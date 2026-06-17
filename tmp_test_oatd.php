<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = config('services.scrapedo.token');
$url = 'https://oatd.org/oatd/search?q=afrique';
$response = Illuminate\Support\Facades\Http::get('https://api.scrape.do/', [
    'token' => $token,
    'url' => $url,
    'render' => 'true'
]);

$html = $response->body();
preg_match_all('/<a[^>]+href="([^"]+)"[^>]*>([0-9]+)<\/a>/i', $html, $matches_double);
// print_r($matches_double);

preg_match('/Showing (?:records )?[0-9]+-[0-9]+ of ([0-9,]+)/i', $html, $stats);
if (empty($stats)) preg_match('/of ([0-9,]+) results/i', $html, $stats);
if (empty($stats)) preg_match('/([0-9,]+) total results/i', $html, $stats);
if (empty($stats)) preg_match('/Displaying[^0-9]+[0-9]+-[0-9]+ of ([0-9,]+)/i', $html, $stats);

print_r($stats);
file_put_contents('oatd_test_out.html', clone $html);
echo "Written to oatd_test_out.html\n";
