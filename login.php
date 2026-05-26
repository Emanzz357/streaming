<?php
require_once 'php/config.php';

// Già loggato → vai al catalogo
if (!empty($_SESSION['utente'])) {
    header('Location: index.php');
    exit;
}

// Legge l'eventuale messaggio di errore dall'URL
$errore = htmlspecialchars($_GET['errore'] ?? '');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Streaming5BI — Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <form action="php/login.php" method="post">
        <h1>Streaming 5BI</h1>

        <?php if ($errore): ?>
            <p class="errore-login"><?= $errore ?></p>
        <?php endif; ?>

        <input type="text"     name="mail"     placeholder="e-mail">
        <input type="password" name="password" placeholder="Password">
        <input type="submit"   value="Accedi">
    </form>
</body>
</html>
