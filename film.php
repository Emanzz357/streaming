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
    <link rel="stylesheet" href="css/film.css">
    <script src="js/film.js" defer></script>
</head>
<body>

<header>
    <a href="index.php" id="btn-torna">← Catalogo</a>
    <span id="titolo-sito">🎬 Streaming 5BI</span>
    <a href="php/logout.php" id="btn-logout">Esci</a>
</header>

<main>
    <img id="poster" src="" alt="Poster">
    <div class="info">
        <h1 id="titolo"></h1>
        <h2 id="regista"></h2>
        <p id="genere-durata"></p>
        <p id="valutazione"></p>
        <p id="budget-incassi"></p>
        <p id="classificazione"></p>
        <div id="attori"></div>
        <p id="trama"></p>
        <p id="visualizzazioni"></p>
        <div id="trailer-container" style="display:none;">
            <h3>Trailer</h3>
            <iframe id="trailer" width="560" height="315"
                    frameborder="0" allowfullscreen
                    allow="autoplay; encrypted-media">
            </iframe>
        </div>
    </div>
</main>

</body>
</html>
