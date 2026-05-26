<?php
require_once 'config.php';

function chiamaUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->load(XML_CATALOGO);
$xpath   = new DOMXPath($dom);
$films   = $xpath->query('//film');
$results = [];

foreach ($films as $film) {
    $id      = $film->getAttribute('id');
    $titolo  = $xpath->query('titolo', $film)->item(0)->nodeValue;
    $tmdbN   = $xpath->query('tmdb_id', $film)->item(0);
    $posterN = $xpath->query('poster',  $film)->item(0);

    if (!$tmdbN || empty($tmdbN->nodeValue)) { $results[] = "⚠️ {$id}: nessun tmdb_id"; continue; }

    $dati = json_decode(chiamaUrl(TMDB_BASE_URL . "/movie/{$tmdbN->nodeValue}?api_key=" . TMDB_API_KEY . "&language=it-IT"), true);

    if (empty($dati['poster_path'])) { $results[] = "❌ {$id} ({$titolo}): poster non trovato"; continue; }

    $nuovo = TMDB_IMAGE_BASE . $dati['poster_path'];
    if ($posterN) { $posterN->nodeValue = $nuovo; }
    $results[] = "✅ {$id} ({$titolo}): {$dati['poster_path']}";
}

$dom->save(XML_CATALOGO);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Aggiorna Poster</title></head><body>";
echo "<h2>Aggiornamento poster</h2><pre>" . implode("\n", $results) . "</pre>";
echo "<p><strong>✅ Fatto. Puoi chiudere questa pagina.</strong></p></body></html>";
