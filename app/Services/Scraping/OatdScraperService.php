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
            Log::info("Début scraping : {$url}");

            // =====================================================
            // 1. STRATÉGIE : OATD EN DIRECT (PAS DE SCRAPE.DO)
            // =====================================================
            $html = $this->fetchDirect($url);

            // fallback si échec
            if (!$html || strlen($html) < 500) {
                Log::warning("Direct failed, fallback Scrape.do");

                $html = $this->fetchViaScrapeDo($url);
            }

            if (!$html || strlen($html) < 500) {
                Log::error("Impossible de récupérer le contenu");
                return [];
            }

            file_put_contents(storage_path('app/oatd_debug.html'), $html);

            $crawler = new Crawler($html);
            $documents = [];

            $crawler->filter('div.result')->each(function ($node) use (&$documents) {
                try {
                    $titleNode = $node->filter('cite.etdTitle');
                    if (!$titleNode->count()) return;

                    $title = trim($titleNode->text());

                    $authorNode = $titleNode->closest('p')->filter('span')->first();
                    $author = $authorNode->count() ? trim($authorNode->text()) : null;

                    $uniNode = $node->filter('span[itemprop="publisher"]')->first();
                    $university = $uniNode->count() ? trim($uniNode->text()) : null;

                    $linkNode = $node->filter('p.links a')->first();
                    $link = $linkNode->count() ? trim($linkNode->attr('href')) : null;

                    // Year
                    $publicationYear = null;
                    $degreeNode = $node->filter('p.degree');
                    if ($degreeNode->count()) {
                        if (preg_match('/(\d{4})/', $degreeNode->text(), $m)) {
                            $publicationYear = $m[1];
                        }
                    }

                    // Description
                    $description = null;
                    $abstractNode = $node->filter('div.abstract');

                    if ($abstractNode->count()) {
                        $description = trim($abstractNode->text());
                    } else {
                        $teaserNode = $node->filter('div.teaser');
                        if ($teaserNode->count()) {
                            $description = trim($teaserNode->text());
                        }
                    }

                    if ($title && $author && $university && $link) {
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

            Log::info("Documents extraits: " . count($documents));

            return $documents;

        } catch (\Exception $e) {
            Log::error("Scraping fatal error: " . $e->getMessage());
            return [];
        }
    }

    // =====================================================
    // DIRECT SCRAPE (LOCAL / PRODUCTION SAFE)
    // =====================================================
    private function fetchDirect(string $url): ?string
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::error("Direct HTTP error: " . $response->status());
                return null;
            }

            return $response->body();

        } catch (\Exception $e) {
            Log::error("Direct scrape failed: " . $e->getMessage());
            return null;
        }
    }

    // =====================================================
    // SCRAPE.DO FALLBACK
    // =====================================================
    private function fetchViaScrapeDo(string $url): ?string
    {
        try {
            $token = config('services.scrapedo.token');

            if (!$token) {
                Log::error("Scrape.do token missing");
                return null;
            }

            Log::info("Fallback Scrape.do : {$url}");

            $response = Http::timeout(120)
                ->withHeaders([
                    'Accept' => 'text/html',
                    'User-Agent' => 'Mozilla/5.0',
                ])
                ->get('https://api.scrape.do/', [
                    'token' => $token,
                    'url' => $url
                ]);

            if (!$response->successful()) {
                Log::error("Scrape.do HTTP error: " . $response->status());
                return null;
            }

            return $response->body();

        } catch (\Exception $e) {
            Log::error("Scrape.do exception: " . $e->getMessage());
            return null;
        }
    }
}