<?php
require_once 'config.php';  // ← solo config, senza controllo sessione
header('Content-Type: application/json; charset=utf-8');

$film_id = trim($_POST['film_id'] ?? '');

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput       = true;
$dom->load(XML_CATALOGO);

$xpath = new DOMXPath($dom);
$nodi  = $xpath->query("//film[@id='{$film_id}']/visualizzazioni_totali");

if ($nodi->length === 0) {
    echo json_encode(['errore' => 'Film non trovato.']);
    exit;
}

$nodo            = $nodi->item(0);
$nodo->nodeValue = (int)$nodo->nodeValue + 1;
$dom->save(XML_CATALOGO);

echo json_encode(['successo' => true, 'visualizzazioni' => (int)$nodo->nodeValue]);