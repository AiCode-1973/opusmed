<?php
/**
 * Configuração central do OpusMed
 * Detecta automaticamente ambiente local (XAMPP) ou servidor remoto
 */

// ── Detecta ambiente ──────────────────────────────────────────
$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'])
        || (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1');

// ── Banco de dados ────────────────────────────────────────────
if ($isLocal) {
    // XAMPP local: conecta via IP remoto
    define('DB_HOST',    '69.49.241.25');
    define('DB_PORT',    3306);
} else {
    // Servidor remoto: conecta via localhost (mais rápido e seguro)
    define('DB_HOST',    'localhost');
    define('DB_PORT',    3306);
}

define('DB_USER',    'apassa73_opusmed');
define('DB_PASS',    'Dema@1973');
define('DB_NAME',    'apassa73_opusmed');
define('DB_CHARSET', 'utf8mb4');

// ── Sessão ───────────────────────────────────────────────────
define('SESSION_NAME',     'opusmed_sess');
define('SESSION_LIFETIME', 7200); // 2 horas

// ── Erros: exibe em local, esconde em produção ────────────────
if ($isLocal) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
