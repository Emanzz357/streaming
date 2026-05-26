<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// Controllo sessione inline (risponde JSON, non HTML)
if (empty($_SESSION['utente'])) {
    http_response_code(401);
    echo json_encode(['errore' => 'Non autorizzato.']);
    exit;
}

$film_id = trim($_POST['film_id'] ?? '');

// Validazione formato ID: deve essere F + 2 cifre
if (!preg_match('/^F[0-9]{2}$/', $film_id)) {
    echo json_encode(['errore' => 'ID film non valido.']);
    exit;
}

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput       = true;

if (!$dom->load(XML_CATALOGO)) {
    echo json_encode(['errore' => 'Impossibile leggere il catalogo.']);
    exit;
}

$xpath = new DOMXPath($dom);
$nodi  = $xpath->query("//film[@id='{$film_id}']/visualizzazioni_totali");

if ($nodi->length === 0) {
    echo json_encode(['errore' => 'Film non trovato.']);
    exit;
}

$nodo            = $nodi->item(0);
$nuovo_valore    = (int)$nodo->nodeValue + 1;
$nodo->nodeValue = $nuovo_valore;

if ($dom->save(XML_CATALOGO) === false) {
    echo json_encode(['errore' => 'Impossibile salvare. Controlla i permessi di catalogo.xml (deve essere 664).']);
    exit;
}

echo json_encode(['successo' => true, 'visualizzazioni' => $nuovo_valore]);
