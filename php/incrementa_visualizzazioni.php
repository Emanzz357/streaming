<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['utente'])) {
    echo json_encode(['errore' => 'Non autorizzato.']);
    exit;
}

$film_id = trim($_POST['film_id'] ?? '');
if (!preg_match('/^F[0-9]{2}$/', $film_id)) {
    echo json_encode(['errore' => 'ID non valido.']);
    exit;
}

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;

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
$nuovo           = (int)$nodo->nodeValue + 1;
$nodo->nodeValue = $nuovo;

if (!$dom->save(XML_CATALOGO)) {
    echo json_encode(['errore' => 'Impossibile salvare. Imposta i permessi di catalogo.xml a 664.']);
    exit;
}

echo json_encode(['successo' => true, 'visualizzazioni' => $nuovo]);
