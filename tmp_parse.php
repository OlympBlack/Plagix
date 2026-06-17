<?php
require __DIR__.'/vendor/autoload.php';
$html = file_get_contents('storage/app/oatd_debug.html');
$crawler = new Symfony\Component\DomCrawler\Crawler($html);

$nodes = $crawler->filter('a');
foreach($nodes as $node) {
    if (strpos($node->textContent, 'Next') !== false || strpos($node->textContent, 'Last') !== false || is_numeric(trim($node->textContent))) {
        echo trim($node->textContent) . " => " . $node->getAttribute('href') . "\n";
    }
}
