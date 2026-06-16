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
            $urlToScrape = 'https://oatd.org/oatd/search?q=afrique';
            
            Log::info("Début du scraping pour : " . $this->source->name . " sur " . $urlToScrape);

            $scrapedData = $scraperService->scrape($urlToScrape);
            
            Log::info("Nombre de documents trouvés : " . count($scrapedData));

            if (empty($scrapedData)) {
                return;
            }

            $docsInserted = 0;

            foreach ($scrapedData as $data) {
                if (empty($data['source_url'])) continue;

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
                    $docsInserted++;
                }
            }

            $this->source->update([
                'last_run_at' => now(),
                'documents_collected' => $this->source->documents_collected + $docsInserted,
            ]);

            Log::info("Nombre de documents enregistrés : " . $docsInserted);

        } catch (\Exception $e) {
            Log::error("Erreur ScrapeSourceJob : " . $e->getMessage());
        }
    }
}
