<?php
define('TMDB_API_KEY',    '71ec29ddd97adba3c6face15562dcf73');
define('TMDB_BASE_URL',   'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');
define('XML_CATALOGO',    __DIR__ . '/../xml/catalogo.xml');
define('XML_UTENTI',      __DIR__ . '/../xml/utenti.xml');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
