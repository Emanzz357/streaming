<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['utente'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(401);
    echo json_encode(['errore' => 'Sessione scaduta. Ricarica la pagina e accedi di nuovo.']);
    exit;
}
