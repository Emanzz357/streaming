<?php
require_once 'config.php';

// Se già loggato, vai al catalogo
if (!empty($_SESSION['utente'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['mail']     ?? '');
    $password = trim($_POST['password'] ?? '');
    $xml      = simplexml_load_file(XML_UTENTI);

    foreach ($xml->utente as $utente) {
        if ((string)$utente->email === $email &&
            (string)$utente->password_hash === hash('sha256', $password)) {
            $_SESSION['utente'] = (string)$utente->nome;
            header('Location: ../index.php');
            exit;
        }
    }
    header('Location: ../login.html?errore=' . urlencode('Email o password errati.'));
    exit;
}

header('Location: ../login.html');
exit;
