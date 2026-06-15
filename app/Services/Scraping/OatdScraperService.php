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

            // We specifically look at OATD's structure
            // Example: .result class contains the item
            $crawler->filter('li.result')->each(function (Crawler $node) use (&$documents, $url) {
                try {
                    $titleNode = $node->filter('.match-title a');
                    $title = $titleNode->count() > 0 ? trim($titleNode->text()) : 'Untitled';
                    $docUrl = $titleNode->count() > 0 ? $titleNode->attr('href') : null;

                    $authorNode = $node->filter('span.author');
                    $author = $authorNode->count() > 0 ? trim($authorNode->text()) : null;

                    $publisherNode = $node->filter('span.publisherDisplay');
                    $university = $publisherNode->count() > 0 ? trim($publisherNode->text()) : null;
                    
                    if ($docUrl) {
                        if (str_starts_with($docUrl, '/')) {
                            $parsedUrl = parse_url($url);
                            $base = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
                            $docUrl = $base . $docUrl;
                        }

                        $documents[] = [
                            'title' => $title,
                            'author' => $author,
                            'university' => $university,
                            'source_url' => $docUrl,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Error parsing a document node in OATD: " . $e->getMessage());
                }
            });

            return $documents;

        } catch (\Exception $e) {
            Log::error('OatdScraperService error on URL ' . $url . ': ' . $e->getMessage());
            return [];
        }
    }
}
