<?php
require_once __DIR__ . '/config.php';

// Se non loggato, torna al login
if (empty($_SESSION['utente'])) {
    header('Location: ../login.html');
    exit;
}
