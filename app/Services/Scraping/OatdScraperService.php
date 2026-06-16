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
            // Ne pas déclencher d'exception sur les erreurs HTTP (ex: 403, 500)
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
                'Connection' => 'keep-alive',
                'Referer' => 'https://oatd.org/',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'same-origin',
                'Sec-Fetch-User' => '?1',
                'Upgrade-Insecure-Requests' => '1',
                // Mimic typical browser cache headers
                'Cache-Control' => 'max-age=0',
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
            Log::info("Début appel ZenRows pour le scrape de : " . $url);
            
            $zenrowsKey = config('services.zenrows.key');
            if (empty($zenrowsKey)) {
                Log::error("Clé API ZenRows manquante dans config/services.php.");
                return [];
            }

            $zenrowsUrl = 'https://api.zenrows.com/v1/?' . http_build_query([
                'apikey' => $zenrowsKey,
                'url' => $url,
                'js_render' => 'true',
            ]);

            $response = $this->client->get($zenrowsUrl);
            $statusCode = $response->getStatusCode();
            $html = (string) $response->getBody();

            Log::info("Code HTTP reçu de ZenRows : " . $statusCode);
            Log::info("Taille du HTML reçu : " . strlen($html) . " octets");

            // Sauvegarde de l'HTML réel pour pouvoir l'inspecter localement (Cloudflare vs Vraie page)
            file_put_contents(storage_path('app/oatd_debug.html'), $html);
            Log::info("Code source enregistré dans storage/app/oatd_debug.html pour vérification.");

            if ($statusCode === 403 || str_contains($html, 'Just a moment...') || str_contains($html, 'Cloudflare')) {
                Log::warning("⚠️ OATD bloque la connexion avec une sécurité Cloudflare anti-bot. (Code $statusCode)");
                // Même si le code est 403, essayons de voir s'il y a quelque chose
            }

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
                    Log::error("Erreur parsing DomCrawler (OATD) extract : " . $e->getMessage());
                }
            });

            Log::info("Le DomCrawler a analysé la page HTML et détecté : " . count($documents) . " résultats valides.");
            return $documents;

        } catch (\Exception $e) {
            Log::error("Exception critique sur OatdScraperService : " . $e->getMessage());
            return [];
        }
    }
}
