<?php
require_once 'php/config.php';

if (empty($_SESSION['utente'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Streaming5BI</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/catalogo.css">
    <script src="js/catalogo.js" defer></script>
</head>
<body>

<header>
    <span id="titolo-sito">🎬 Streaming 5BI</span>
    <div class="header-destra">
        <span id="benvenuto">👤 <?= htmlspecialchars($_SESSION['utente']) ?></span>
        <button id="btn-aggiungi" onclick="togglePannelloAggiungi()">+ Aggiungi film</button>
        <a href="php/logout.php" id="btn-logout">Esci</a>
    </div>
</header>

<div id="pannello-aggiungi" style="display:none;">
    <div id="form-cerca">
        <input type="text" id="input-titolo" placeholder="Cerca un film su TMDb...">
        <button onclick="cercaFilm()">Cerca</button>
        <button onclick="togglePannelloAggiungi()">✕</button>
    </div>
    <div id="risultati-ricerca"></div>
</div>

<main></main>

</body>
</html>
