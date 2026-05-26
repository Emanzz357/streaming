<?php
require_once 'config.php';
session_destroy();
// Redirect esplicito con header HTTP - il browser esegue il redirect, non scarica il file
header('Location: ../login.html');
exit;
