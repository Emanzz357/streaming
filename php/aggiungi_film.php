<?php
require_once 'controlla_sessione.php';
header('Content-Type: application/json; charset=utf-8');

$tmdb_id = intval($_POST['tmdb_id'] ?? 0);
if ($tmdb_id <= 0) {
    echo json_encode(['errore' => 'ID non valido.']);
    exit;
}

// cURL per chiamate esterne — funziona su Altervista
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

// Chiama un endpoint TMDb e restituisce array PHP
function tmdb($endpoint) {
    $url = TMDB_BASE_URL . $endpoint . '?api_key=' . TMDB_API_KEY . '&language=it-IT';
    $r   = chiamaUrl($url);
    return $r ? json_decode($r, true) : null;
}

$d = tmdb("/movie/{$tmdb_id}");
$c = tmdb("/movie/{$tmdb_id}/credits");
$v = tmdb("/movie/{$tmdb_id}/videos");

if (!$d) {
    echo json_encode(['errore' => 'Film non trovato su TMDb.']);
    exit;
}

$xml = simplexml_load_file(XML_CATALOGO);
if (!empty($xml->xpath("//film[tmdb_id='{$tmdb_id}']"))) {
    echo json_encode(['errore' => 'Film già presente nel catalogo.']);
    exit;
}

$titolo      = $d['title'] ?? $d['original_title'];
$anno        = (int)substr($d['release_date'] ?? '0', 0, 4);
$genere      = $d['genres'][0]['name']  ?? 'N/D';
$durata      = (int)($d['runtime']      ?? 0);
$trama       = $d['overview']           ?? 'N/D';
$budget      = (int)($d['budget']       ?? 0);
$incassi     = (int)($d['revenue']      ?? 0);
$poster      = !empty($d['poster_path']) ? TMDB_IMAGE_BASE . $d['poster_path'] : '';
$valutazione = round((float)($d['vote_average'] ?? 0) / 2, 1);

$regista = 'N/D';
foreach (($c['crew'] ?? []) as $membro) {
    if ($membro['job'] === 'Director') { $regista = $membro['name']; break; }
}

$attori  = array_slice(array_column($c['cast'] ?? [], 'name'), 0, 3);

$trailer = '';
foreach (($v['results'] ?? []) as $vid) {
    if ($vid['type'] === 'Trailer' && $vid['site'] === 'YouTube') {
        $trailer = 'https://www.youtube.com/watch?v=' . $vid['key'];
        break;
    }
}

$max = 0;
foreach ($xml->film as $f) {
    $n = (int)substr((string)$f['id'], 1);
    if ($n > $max) $max = $n;
}
$nuovo_id = 'F' . sprintf('%02d', $max + 1);

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput       = true;
$dom->load(XML_CATALOGO);

function el($dom, $tag, $val) {
    $e = $dom->createElement($tag);
    $e->appendChild($dom->createTextNode((string)$val));
    return $e;
}

$film = $dom->createElement('film');
$film->setAttribute('id', $nuovo_id);
$film->appendChild(el($dom, 'titolo',      $titolo));
$film->appendChild(el($dom, 'anno',        $anno));
$film->appendChild(el($dom, 'genere',      $genere));
$film->appendChild(el($dom, 'durata',      $durata));
$film->appendChild(el($dom, 'valutazione', $valutazione));
$film->appendChild(el($dom, 'trama',       $trama));
$film->appendChild(el($dom, 'regista',     $regista));

$elAttori = $dom->createElement('attori');
foreach ($attori as $i => $nome) {
    $a = $dom->createElement('attore');
    $a->setAttribute('ruolo', $i === 0 ? 'protagonista' : 'secondario');
    $a->appendChild($dom->createTextNode($nome));
    $elAttori->appendChild($a);
}
$film->appendChild($elAttori);

$film->appendChild(el($dom, 'budget',                $budget));
$film->appendChild(el($dom, 'incassi',               $incassi));
$film->appendChild(el($dom, 'classificazione',       'NR'));
$film->appendChild(el($dom, 'trailer',               $trailer));
$film->appendChild(el($dom, 'poster',                $poster));
$film->appendChild(el($dom, 'tmdb_id',               $tmdb_id));
$film->appendChild(el($dom, 'visualizzazioni_totali','0'));

$dom->documentElement->appendChild($film);
$dom->save(XML_CATALOGO);

echo json_encode(['successo' => true, 'titolo' => $titolo, 'id' => $nuovo_id]);
