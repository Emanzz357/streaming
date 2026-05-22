<?php
require_once 'controlla_sessione.php';
header('Content-Type: application/json; charset=utf-8');

$titolo = trim($_POST['titolo'] ?? '');
if (empty($titolo)) {
    echo json_encode(['errore' => 'Inserisci un titolo.']);
    exit;
}

// Funzione che chiama un URL con cURL invece di file_get_contents
// cURL funziona su Altervista, file_get_contents verso URL esterni no
function chiamaUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // restituisce la risposta come stringa
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);            // timeout 10 secondi
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // necessario su alcuni hosting
    $risposta = curl_exec($ch);
    curl_close($ch);
    return $risposta;
}

$url      = TMDB_BASE_URL . '/search/movie?api_key=' . TMDB_API_KEY . '&query=' . urlencode($titolo) . '&language=it-IT';
$risposta = chiamaUrl($url);
$dati     = json_decode($risposta, true);

if (empty($dati['results'])) {
    echo json_encode(['errore' => 'Nessun film trovato.']);
    exit;
}

$risultati = [];
foreach (array_slice($dati['results'], 0, 5) as $f) {
    $risultati[] = [
        'tmdb_id' => $f['id'],
        'titolo'  => $f['title'],
        'anno'    => substr($f['release_date'] ?? '0000', 0, 4),
        'poster'  => !empty($f['poster_path']) ? TMDB_IMAGE_BASE . $f['poster_path'] : null,
    ];
}

echo json_encode(['risultati' => $risultati]);
