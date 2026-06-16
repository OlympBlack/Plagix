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
                Log::error("Token Scrape.do manquant");
                return [];
            }

            $response = Http::get('https://api.scrape.do/', [
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

                    if (!empty($title) && !empty($author) && !empty($university) && !empty($link)) {
                        $documents[] = [
                            'title' => $title,
                            'author' => $author,
                            'university' => $university,
                            'source_url' => $link,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Parsing error: " . $e->getMessage());
                }
            });

            Log::info("Documents extraits: " . count($documents));

            return $documents;

        } catch (\Exception $e) {
            Log::error("Erreur Scrape.do: " . $e->getMessage());
            return [];
        }
    }
}