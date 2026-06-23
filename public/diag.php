<?php
/**
 * DIAGNÓSTICO — OpusMed
 * ⚠ APAGUE ESTE ARQUIVO APÓS O USO!
 */

// Força exibição de erros
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Proteção simples
if (($_GET['key'] ?? '') !== 'opusdiag2026') {
    http_response_code(403);
    die('Acesso negado. Use ?key=opusdiag2026');
}

echo '<style>body{font-family:monospace;padding:20px;background:#f0f4f8}
h2{color:#1a6fb5;margin-top:24px}
.ok{color:#166534;background:#d1fae5;padding:3px 8px;border-radius:4px}
.err{color:#991b1b;background:#fee2e2;padding:3px 8px;border-radius:4px}
pre{background:#1e293b;color:#e2e8f0;padding:14px;border-radius:8px;overflow:auto}
</style>';
echo '<h1>OpusMed — Diagnóstico</h1>';

// ── PHP ───────────────────────────────────────────────────────
echo '<h2>PHP</h2>';
echo 'Versão: <strong>' . PHP_VERSION . '</strong><br>';
echo 'SAPI: ' . php_sapi_name() . '<br>';
echo 'OS: ' . PHP_OS . '<br>';
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'session'];
foreach ($extensions as $ext) {
    $ok = extension_loaded($ext);
    echo "Extensão <b>$ext</b>: " . ($ok ? '<span class="ok">OK</span>' : '<span class="err">FALTANDO</span>') . '<br>';
}

// ── Caminhos ─────────────────────────────────────────────────
echo '<h2>Caminhos</h2>';
echo '__DIR__: ' . __DIR__ . '<br>';
echo 'DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'n/d') . '<br>';
echo 'PHP_SELF: ' . ($_SERVER['PHP_SELF'] ?? 'n/d') . '<br>';
echo 'SERVER_NAME: ' . ($_SERVER['SERVER_NAME'] ?? 'n/d') . '<br>';

$configPath = __DIR__ . '/../config/config.php';
$guardPath  = __DIR__ . '/../app/helpers/guard.php';
echo 'config.php existe: ' . (file_exists($configPath) ? '<span class="ok">SIM</span>' : '<span class="err">NÃO - ' . $configPath . '</span>') . '<br>';
echo 'guard.php existe: '  . (file_exists($guardPath)  ? '<span class="ok">SIM</span>' : '<span class="err">NÃO - ' . $guardPath  . '</span>') . '<br>';

// ── Banco de dados ────────────────────────────────────────────
echo '<h2>Banco de dados</h2>';
try {
    require_once $configPath;
    echo 'DB_HOST: <b>' . DB_HOST . '</b><br>';
    echo 'DB_NAME: <b>' . DB_NAME . '</b><br>';

    require_once __DIR__ . '/../config/database.php';
    $db = Database::getInstance()->getConnection();
    echo 'Conexão PDO: <span class="ok">OK</span><br>';

    // Conta tabelas
    $tabelas = ['modulos', 'perfis', 'perfil_modulo_permissao', 'usuarios'];
    foreach ($tabelas as $t) {
        $n = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "Registros em <b>$t</b>: $n<br>";
    }
} catch (Exception $e) {
    echo '<span class="err">ERRO: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
}

// ── Session ───────────────────────────────────────────────────
echo '<h2>Sessão</h2>';
if (session_status() === PHP_SESSION_NONE) {
    session_name('opusmed_sess');
    session_start();
}
echo 'session_status: ' . session_status() . '<br>';
echo 'session_id: ' . session_id() . '<br>';
echo 'usuario_id na sessão: ' . (isset($_SESSION['usuario_id']) ? '<span class="ok">' . $_SESSION['usuario_id'] . '</span>' : '<span class="err">não logado</span>') . '<br>';
if (!empty($_SESSION)) {
    echo '<pre>' . htmlspecialchars(print_r($_SESSION, true)) . '</pre>';
}

// ── Permissões (se logado) ────────────────────────────────────
if (!empty($_SESSION['usuario_id']) && !empty($_SESSION['perfil_id'])) {
    echo '<h2>Permissões do perfil ' . (int)$_SESSION['perfil_id'] . '</h2>';
    try {
        require_once __DIR__ . '/../app/models/PerfilPermissao.php';
        $pm = new PerfilPermissao();
        $perms = $pm->buscarPorPerfil((int)$_SESSION['perfil_id']);
        if (empty($perms)) {
            echo '<span class="err">NENHUMA permissão retornada — tabela perfil_modulo_permissao vazia ou perfil_id inválido</span><br>';
        } else {
            echo '<pre>' . htmlspecialchars(print_r($perms, true)) . '</pre>';
        }
    } catch (Exception $e) {
        echo '<span class="err">ERRO: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
    }
}

// ── Erros PHP recentes ────────────────────────────────────────
echo '<h2>Configuração de erros</h2>';
echo 'display_errors: ' . ini_get('display_errors') . '<br>';
echo 'error_log: ' . (ini_get('error_log') ?: 'padrão do servidor') . '<br>';
echo 'log_errors: ' . ini_get('log_errors') . '<br>';

echo '<br><hr><small>⚠ Apague <b>diag.php</b> após o diagnóstico!</small>';
