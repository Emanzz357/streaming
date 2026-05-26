<?php
require_once 'controlla_sessione.php';
header('Content-Type: application/json; charset=utf-8');

$titolo = trim($_POST['titolo'] ?? '');
if (empty($titolo)) {
    echo json_encode(['errore' => 'Inserisci un titolo.']);
    exit;
}

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

$url   = TMDB_BASE_URL . '/search/movie?api_key=' . TMDB_API_KEY . '&query=' . urlencode($titolo) . '&language=it-IT';
$dati  = json_decode(chiamaUrl($url), true);

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
