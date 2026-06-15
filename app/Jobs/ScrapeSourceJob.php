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
            // Force the URL as per requirements, assuming oatd
            if (str_contains($this->source->base_url, 'oatd.org')) {
                $urlToScrape = 'https://oatd.org/oatd/search?q=afrique';
            } else {
                $urlToScrape = $this->source->base_url;
            }
            
            Log::info("Starting job for source: " . $this->source->name . " at URL: " . $urlToScrape);

            $scrapedData = $scraperService->scrape($urlToScrape);

            if (empty($scrapedData)) {
                Log::warning("No documents found for source: " . $this->source->name);
                return;
            }

            $docsInserted = 0;

            foreach ($scrapedData as $data) {
                // hash = sha256(source_url) OR hash = sha256(title + author)
                // Using sha256(source_url) as requested
                $hash = hash('sha256', $data['source_url']);

                // Deduplication
                $exists = CollectedDocument::where('hash', $hash)->exists();

                if (!$exists) {
                    CollectedDocument::create([
                        'title' => $data['title'],
                        'author' => $data['author'],
                        'university' => $data['university'],
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

            Log::info("Scraping completed for source: " . $this->source->name . ". Inserted: " . $docsInserted);

        } catch (\Exception $e) {
            Log::error("ScrapeSourceJob error for source {$this->source->name}: " . $e->getMessage());
        }
    }
}
