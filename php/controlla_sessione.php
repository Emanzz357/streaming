<?php
require_once __DIR__ . '/config.php';

// Usato solo dalle chiamate AJAX (cerca_film, aggiungi_film)
// Se non loggato risponde JSON invece di fare redirect
// (un redirect HTML romperebbe le chiamate fetch() di JavaScript)
if (empty($_SESSION['utente'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['errore' => 'Non autorizzato. Effettua il login.']);
    exit;
}
