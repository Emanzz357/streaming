<?php
require_once 'config.php';

// Già loggato → vai al catalogo
if (!empty($_SESSION['utente'])) {
    header('Location: ../index.php');
    exit;
}

$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['mail']     ?? '');
    $password = trim($_POST['password'] ?? '');

    // Campi vuoti
    if (empty($email) || empty($password)) {
        $errore = 'Inserisci email e password.';
    } else {
        $xml = @simplexml_load_file(XML_UTENTI);

        if (!$xml) {
            $errore = 'Errore interno del server.';
        } else {
            $trovato = false;
            foreach ($xml->utente as $utente) {
                if ((string)$utente->email === $email &&
                    (string)$utente->password_hash === hash('sha256', $password)) {
                    $_SESSION['utente'] = (string)$utente->nome;
                    $trovato = true;
                    header('Location: ../index.php');
                    exit;
                }
            }
            if (!$trovato) {
                $errore = 'Email o password non corretti.';
            }
        }
    }
}

// Torna alla pagina login con l'errore nell'URL
header('Location: ../login.php?errore=' . urlencode($errore));
exit;
