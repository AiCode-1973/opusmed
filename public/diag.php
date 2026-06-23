<?php
/**
 * DIAGNÓSTICO — OpusMed
 * ⚠ APAGUE ESTE ARQUIVO APÓS O USO!
 */

// Força exibição de erros ANTES de qualquer output
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Sessão ANTES de qualquer output
session_name('opusmed_sess');
@session_start();

// Proteção via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['key'] ?? '') !== 'opusdiag2026') {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f0f4f8;margin:0}
    .box{background:#fff;padding:40px;border-radius:12px;border:1px solid #e2e8f0;text-align:center;min-width:300px}
    input{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:12px 0;font-size:1rem;box-sizing:border-box}
    button{width:100%;padding:10px;background:#1a6fb5;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer}
    </style></head><body><div class="box">
    <h2 style="margin-top:0">OpusMed Diagnóstico</h2>
    <form method="POST">
        <input type="password" name="key" placeholder="Senha de acesso" autofocus>
        <button type="submit">Acessar</button>
    </form>
    </div></body></html>';
    exit;
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

// ── Teste direto de permissões (admin, perfil_id=1) ───────────
echo '<h2>Teste direto: permissões do Administrador (perfil_id=1)</h2>';
try {
    if (!class_exists('Database')) require_once __DIR__ . '/../config/database.php';
    $db2 = Database::getInstance()->getConnection();

    // Lista módulos com suas permissões para perfil 1
    $stmt = $db2->prepare('
        SELECT m.nome, m.id AS modulo_id,
               p.pode_ver, p.pode_criar, p.pode_editar, p.pode_excluir
        FROM perfil_modulo_permissao p
        INNER JOIN modulos m ON m.id = p.modulo_id
        WHERE p.perfil_id = 1
        ORDER BY m.ordem
    ');
    $stmt->execute();
    $rows = $stmt->fetchAll();
    if (empty($rows)) {
        echo '<span class="err">NENHUMA permissão para perfil_id=1 — verifique o seed SQL</span><br>';
    } else {
        echo '<table border="1" cellpadding="6" style="border-collapse:collapse;font-size:.85rem">';
        echo '<tr style="background:#f8fafc"><th>Módulo</th><th>ver</th><th>criar</th><th>editar</th><th>excluir</th><th>Hex nome</th></tr>';
        foreach ($rows as $r) {
            $hex = bin2hex($r['nome']);
            echo "<tr><td>{$r['nome']}</td><td>{$r['pode_ver']}</td><td>{$r['pode_criar']}</td><td>{$r['pode_editar']}</td><td>{$r['pode_excluir']}</td><td style='font-size:.7rem'>{$hex}</td></tr>";
        }
        echo '</table>';

        // Compara encoding do PHP com o do banco
        echo '<br><b>Comparação de encoding (PHP vs DB):</b><br>';
        $phpUsuarios   = 'Usuários';
        $phpHex        = bin2hex($phpUsuarios);
        $dbNome        = '';
        foreach ($rows as $r) { if ($r['nome'] === $phpUsuarios) { $dbNome = $r['nome']; break; } }
        echo "PHP 'Usuários' hex: <code>$phpHex</code><br>";
        echo "Match no array: " . ($dbNome !== '' ? '<span class="ok">SIM — chave existe</span>' : '<span class="err">NÃO — encoding diferente!</span>') . '<br>';
    }
} catch (Exception $e) {
    echo '<span class="err">ERRO: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
}

// ── Erros PHP recentes ────────────────────────────────────────
echo '<h2>Configuração de erros</h2>';
echo 'display_errors: ' . ini_get('display_errors') . '<br>';
echo 'error_log: ' . (ini_get('error_log') ?: 'padrão do servidor') . '<br>';
echo 'log_errors: ' . ini_get('log_errors') . '<br>';

echo '<br><hr><small>⚠ Apague <b>diag.php</b> após o diagnóstico!</small>';
