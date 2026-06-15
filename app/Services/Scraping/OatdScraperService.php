<?php

namespace App\Services\Scraping;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class OatdScraperService implements ScraperInterface
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30.0,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
            ],
        ]);
    }

    /**
     * Scrape the OATD page and return results
     *
     * @param string $url
     * @return array
     */
    public function scrape(string $url): array
    {
        try {
            $response = $this->client->get($url);
            $html = (string) $response->getBody();

            $crawler = new Crawler($html);
            $documents = [];

            // On s'assure de trouver tous les blocs correspondants aux documents
            $crawler->filter('.result, article, .record, li.result')->each(function (Crawler $node) use (&$documents, $url) {
                try {
                    // Extraction du titre
                    $titleNode = $node->filter('.match-title, .title, h3, h2')->first();
                    if ($titleNode->count() === 0) return; // Un résultat sans titre est invalide
                    
                    $title = trim($titleNode->text());

                    // Extraction de l'URL du document
                    $docUrl = null;
                    if ($titleNode->nodeName() === 'a') {
                        $docUrl = $titleNode->attr('href');
                    } else if ($titleNode->filter('a')->count() > 0) {
                        $docUrl = $titleNode->filter('a')->first()->attr('href');
                    }

                    // Extraction de l'auteur
                    $authorNode = $node->filter('.author, span.author, .authors');
                    $author = $authorNode->count() > 0 ? trim($authorNode->text()) : null;

                    // Extraction de l'université
                    $publisherNode = $node->filter('.publisherDisplay, .publisher, .university, .institution');
                    $university = $publisherNode->count() > 0 ? trim($publisherNode->text()) : null;
                    
                    if ($docUrl) {
                        // S'assurer que le lien est absolu
                        if (!str_starts_with($docUrl, 'http')) {
                            if (str_starts_with($docUrl, '/')) {
                                $parsedUrl = parse_url($url);
                                $docUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $docUrl;
                            } else {
                                $docUrl = 'https://oatd.org/' . ltrim($docUrl, '/');
                            }
                        }

                        $documents[] = [
                            'title' => $title,
                            'author' => $author,
                            'university' => $university,
                            'source_url' => $docUrl,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur parsing DomCrawler (OATD) : " . $e->getMessage());
                }
            });

            return $documents;

        } catch (\Exception $e) {
            Log::error('OatdScraperService error on URL ' . $url . ': ' . $e->getMessage());
            return [];
        }
    }
}
