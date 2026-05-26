<?php
// aggiorna_poster.php — Aggiorna i poster di tutti i film via API TMDb
// Aprilo UNA VOLTA nel browser dopo aver caricato il sito: tuosito.altervista.org/php/aggiorna_poster.php
// Usa i tmdb_id già presenti nel catalogo.xml per recuperare i path corretti da TMDb
require_once 'config.php';

// Funzione cURL (funziona su Altervista, file_get_contents verso URL esterni no)
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
$dom->formatOutput       = true;
$dom->load(XML_CATALOGO);

$xpath   = new DOMXPath($dom);
$films   = $xpath->query('//film');
$results = [];

foreach ($films as $film) {
    $id_nodo     = $film->getAttribute('id');
    $titolo_nodo = $xpath->query('titolo', $film)->item(0)->nodeValue;
    $tmdb_nodo   = $xpath->query('tmdb_id', $film)->item(0);
    $poster_nodo = $xpath->query('poster', $film)->item(0);

    // Se non c'è tmdb_id, salta
    if (!$tmdb_nodo || empty($tmdb_nodo->nodeValue)) {
        $results[] = "⚠️ {$id_nodo} ({$titolo_nodo}): nessun tmdb_id, saltato";
        continue;
    }

    $tmdb_id = (int)$tmdb_nodo->nodeValue;

    // Chiama TMDb per ottenere il poster_path aggiornato
    $url      = TMDB_BASE_URL . "/movie/{$tmdb_id}?api_key=" . TMDB_API_KEY . "&language=it-IT";
    $risposta = chiamaUrl($url);
    $dati     = json_decode($risposta, true);

    if (empty($dati['poster_path'])) {
        $results[] = "❌ {$id_nodo} ({$titolo_nodo}): poster non trovato su TMDb";
        continue;
    }

    // Costruisce l'URL completo del poster
    $nuovo_poster = TMDB_IMAGE_BASE . $dati['poster_path'];

    // Aggiorna il nodo <poster> nel DOM
    if ($poster_nodo) {
        $poster_nodo->nodeValue = $nuovo_poster;
    } else {
        // Se il nodo <poster> non esiste, lo crea prima di <tmdb_id>
        $nuovo_nodo = $dom->createElement('poster');
        $nuovo_nodo->appendChild($dom->createTextNode($nuovo_poster));
        $film->insertBefore($nuovo_nodo, $tmdb_nodo);
    }

    $results[] = "✅ {$id_nodo} ({$titolo_nodo}): poster aggiornato → {$dati['poster_path']}";
}

// Salva il file XML aggiornato
if ($dom->save(XML_CATALOGO)) {
    $results[] = "<br><strong>✅ catalogo.xml salvato con successo.</strong>";
} else {
    $results[] = "<br><strong>❌ Errore nel salvataggio. Controlla i permessi di catalogo.xml (664).</strong>";
}

// Output HTML leggibile
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Aggiornamento Poster</title></head><body>";
echo "<h2>Aggiornamento poster TMDb</h2><pre>";
echo implode("\n", $results);
echo "</pre></body></html>";
?>
