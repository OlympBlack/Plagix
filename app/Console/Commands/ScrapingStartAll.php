<?php

namespace App\Console\Commands;

use App\Models\ScrapingSource;
use App\Jobs\ScrapeSourceJob;
use Illuminate\Console\Command;

class ScrapingStartAll extends Command
{
    protected $signature = 'scraping:start-all';
    protected $description = 'Lancer le scraping pour toutes les sources actives';

    public function handle()
    {
        $sources = ScrapingSource::where('is_active', true)->get();
        $this->info(" Lancement du scraping pour " . $sources->count() . " sources actives...");

        foreach ($sources as $source) {
            ScrapeSourceJob::dispatch($source);
            $this->line("  Job envoyé pour : " . $source->name);
        }

        $this->info("Tous les scrapings ont été mis en file d'attente.");
    }
}
