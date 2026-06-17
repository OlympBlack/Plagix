<?php

namespace App\Jobs;

use App\Models\ScrapingSource;
use App\Models\CollectedDocument;
use App\Services\Scraping\OatdScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ScrapeSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $source;

    /**
     * Create a new job instance.
     */
    public function __construct(ScrapingSource $source)
    {
        $this->source = $source;
    }

    /**
     * Execute the job.
     */
    public function handle(OatdScraperService $scraperService): void
    {
        try {
            $this->source->refresh();
            
            if (!in_array($this->source->scraping_status, ['running', 'paused', 'error'])) {
                $this->source->update([
                    'scraping_status' => 'running',
                    'scraping_progress' => 0,
                    'current_page' => 0,
                    'total_pages' => 0
                ]);
            } else if (in_array($this->source->scraping_status, ['paused', 'error'])) {
                $this->source->update(['scraping_status' => 'running']);
            }

            Log::info("========================================");
            Log::info("Début du scraping pour : " . $this->source->name);
            Log::info("========================================");

            $startTime = microtime(true);
            $totalDocsInserted = 0;
            $totalDuplicates = 0;
            $pagesTraversed = 0;

            while (true) {
                $this->source->refresh();
                if ($this->source->scraping_status === 'paused') {
                    Log::info("[PAUSE] Scraping mis en pause à la page {$this->source->current_page}.");
                    return;
                }

                if ($this->source->total_pages > 0 && $this->source->current_page >= $this->source->total_pages) {
                    $this->source->update([
                        'scraping_status' => 'idle',
                        'scraping_progress' => 100,
                        'current_page' => 0, // Reset pour un futur run depuis 0
                        'total_pages' => 0
                    ]);

                    $timeTook = round(microtime(true) - $startTime, 2);
                    Log::info("========================================");
                    Log::info("Fin du scraping");
                    Log::info("Nombre total de pages parcourues : " . $pagesTraversed);
                    Log::info("Nombre total de documents enregistrés : " . $totalDocsInserted);
                    Log::info("Nombre total de doublons ignorés : " . $totalDuplicates);
                    Log::info("Temps total d'exécution : {$timeTook} secondes");
                    Log::info("========================================");
                    return;
                }

                $nextPage = $this->source->current_page + 1;
                $displayTotalPages = $this->source->total_pages > 0 ? $this->source->total_pages : "?";
                
                Log::info("----------------------------------------");
                Log::info("Page {$nextPage}/{$displayTotalPages} en cours");

                $startParam = ($nextPage - 1) * 30 + 1;
                $urlToScrape = $this->source->base_url . '/oatd/search?q=afrique';
                if ($nextPage > 1) {
                    $urlToScrape .= '&start=' . $startParam;
                }
                
                $result = $scraperService->scrape($urlToScrape);

                if (empty($result) || empty($result['documents'])) {
                    Log::warning("Avertissement: Impossible d'extraire la page {$nextPage} ou page vide.");
                    // En cas d'erreur de timeout on veut break pour ne pas boucler indéfiniment
                    $this->source->update(['scraping_status' => 'error']);
                    return;
                }

                $documents = $result['documents'];
                $totalPages = $result['total_pages'];

                // Mise à jour du total de pages dès la première fois
                if ($this->source->total_pages == 0) {
                    $this->source->update(['total_pages' => $totalPages]);
                    $displayTotalPages = $totalPages;
                }

                Log::info(count($documents) . " thèses trouvées sur la page {$nextPage}.");

                $docsInserted = 0;
                $duplicates = 0;

                foreach ($documents as $data) {
                    if (empty($data['source_url'])) continue;

                    Log::info("Ouverture de la thèse : " . Str::limit($data['title'], 50));
                    Log::info("Extraction réussie...");

                    $hash = hash('sha256', $data['source_url']);
                    $exists = CollectedDocument::where('hash', $hash)->exists();

                    if (!$exists) {
                        CollectedDocument::create([
                            'title' => $data['title'],
                            'author' => $data['author'],
                            'university' => $data['university'],
                            'publication_year' => $data['publication_year'] ?? null,
                            'description' => $data['description'] ?? null,
                            'source_url' => $data['source_url'],
                            'hash' => $hash,
                            'scraping_source_id' => $this->source->id,
                        ]);
                        Log::info("Document enregistré avec succès (Hash: {$hash})");
                        $docsInserted++;
                        $totalDocsInserted++;
                    } else {
                        Log::info("Doublon ignoré (Hash existant: {$hash})");
                        $duplicates++;
                        $totalDuplicates++;
                    }
                }

                $currentTotalPages = $this->source->total_pages > 0 ? $this->source->total_pages : $totalPages;
                $progress = (int) round(($nextPage / $currentTotalPages) * 100);

                $this->source->update([
                    'last_run_at' => now(),
                    'documents_collected' => $this->source->documents_collected + $docsInserted,
                    'current_page' => $nextPage,
                    'scraping_progress' => $progress,
                ]);
                
                $pagesTraversed++;

                sleep(2);
            }

        } catch (\Exception $e) {
            $this->source->update(['scraping_status' => 'error']);
            Log::error("Erreur fatale ScrapeSourceJob : " . $e->getMessage());
        }
    }
}
