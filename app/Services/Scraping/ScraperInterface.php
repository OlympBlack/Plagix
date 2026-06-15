<?php

namespace App\Services\Scraping;

interface ScraperInterface
{
    /**
     * Scrape a given URL and return an array of collected documents.
     *
     * @param string $url
     * @return array
     */
    public function scrape(string $url): array;
}
