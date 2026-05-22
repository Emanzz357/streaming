<?php
require_once 'config.php';

// Se già loggato, vai al catalogo
if (!empty($_SESSION['utente'])) {
    header('Location: ../index.html');
    exit;
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['mail']     ?? '');
    $password = trim($_POST['password'] ?? '');

    $xml = simplexml_load_file(XML_UTENTI);

    // Cicla tutti gli utenti e controlla email + hash password
    foreach ($xml->utente as $utente) {
        if ((string)$utente->email === $email &&
            (string)$utente->password_hash === hash('sha256', $password)) {

            // Credenziali corrette: salva in sessione e vai al catalogo
            $_SESSION['utente'] = (string)$utente->nome;
            header('Location: ../index.html');
            exit;
        }
    }

    $errore = 'Email o password errati.';
}

// Torna al login con il messaggio di errore
header('Location: ../login.html?errore=' . urlencode($errore));
exit;
