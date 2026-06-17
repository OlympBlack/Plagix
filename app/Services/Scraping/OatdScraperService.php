<?php

namespace App\Services\Scraping;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OatdScraperService implements ScraperInterface
{
    public function scrape(string $url): array
    {
        try {
            Log::info("Début scraping via Scrape.do : {$url}");

            $token = config('services.scrapedo.token');

            if (!$token) {
                Log::error("Token  manquant");
                return [];
            }

            $response = Http::timeout(120)
                ->retry(3, 5000)
                ->get('https://api.scrape.do/', [
                    'token' => $token,
                    'url' => $url,
                    'render' => 'true'
                ]);

            $status = $response->status();
            $html = $response->body();

            Log::info("Scrape.do status: {$status}");
            Log::info("HTML size: " . strlen($html));

            file_put_contents(storage_path('app/oatd_debug.html'), $html);

            if ($status !== 200) {
                Log::error("Scrape.do error HTTP: {$status}");
                return [];
            }

            if (
                str_contains($html, 'Just a moment') ||
                str_contains($html, 'Cloudflare') ||
                strlen($html) < 500
            ) {
                Log::warning("Page bloquée ou invalide");
                return [];
            }

            $crawler = new Crawler($html);
            $documents = [];

            $crawler->filter('div.result')->each(function ($node) use (&$documents) {
                try {
                    $titleNode = $node->filter('cite.etdTitle');
                    if (!$titleNode->count()) {
                        return;
                    }
                    $title = trim($titleNode->text());

                    $authorNode = $titleNode->closest('p')->filter('span')->first();
                    $author = $authorNode->count() ? trim($authorNode->text()) : null;

                    $uniNode = $node->filter('span[itemprop="publisher"]')->first();
                    $university = $uniNode->count() ? trim($uniNode->text()) : null;

                    $linkNode = $node->filter('p.links a')->first();
                    $link = $linkNode->count() ? trim($linkNode->attr('href')) : null;

                    // --- Nouveaux champs ---

                    // Date de publication : extraire l'année depuis "Degree: 2019, Université..."
                    $publicationYear = null;
                    $degreeNode = $node->filter('p.degree');
                    if ($degreeNode->count()) {
                        $degreeText = $degreeNode->text();
                        if (preg_match('/Degree:\s*(\d{4})/', $degreeText, $matches)) {
                            $publicationYear = $matches[1];
                        }
                    }

                    // Description complète : priorité au div.abstract (texte complet derrière "more")
                    $description = null;
                    $abstractNode = $node->filter('div.abstract');
                    if ($abstractNode->count()) {
                        $description = trim($abstractNode->text());
                        // Supprimer le symbole ▼ en début de texte
                        $description = preg_replace('/^[\x{25BC}\x{25B6}]\s*/u', '', $description);
                    } else {
                        // Fallback : le teaser (aperçu tronqué)
                        $teaserNode = $node->filter('div.teaser');
                        if ($teaserNode->count()) {
                            $description = trim($teaserNode->text());
                            $description = preg_replace('/^[\x{25BC}\x{25B6}]\s*/u', '', $description);
                            // Supprimer le "(more)" en fin de texte
                            $description = preg_replace('/\s*\(more\)\s*$/', '', $description);
                        }
                    }

                    if (!empty($title) && !empty($author) && !empty($university) && !empty($link)) {
                        $documents[] = [
                            'title' => $title,
                            'author' => $author,
                            'university' => $university,
                            'source_url' => $link,
                            'publication_year' => $publicationYear,
                            'description' => $description,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Parsing error: " . $e->getMessage());
                }
            });

            $totalPages = 1;
            $crawler->filter('a')->each(function ($node) use (&$totalPages) {
                $text = trim($node->text());
                if (is_numeric($text)) {
                    $val = (int)$text;
                    if ($val > $totalPages) {
                        $totalPages = $val;
                    }
                }
            });

            Log::info("Documents extraits: " . count($documents) . " - Pages totales: {$totalPages}");

            return [
                'documents' => $documents,
                'total_pages' => $totalPages
            ];

        } catch (\Exception $e) {
            Log::error("Erreur Scrape.do: " . $e->getMessage());
            return [];
        }
    }
}