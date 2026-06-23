<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo '<h3>Teste de includes</h3>';
echo '<b>PHP:</b> ' . PHP_VERSION . '<br>';
echo '<b>PHP >= 8.0:</b> ' . (version_compare(PHP_VERSION, '8.0.0', '>=') ? '✅ Sim' : '❌ NÃO — os models exigem PHP 8.0+') . '<br><br>';

// 1. database.php
if (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
    echo '✅ database.php carregado<br>';
} else {
    echo '❌ database.php NÃO encontrado em: ' . realpath(__DIR__ . '/../config') . '<br>';
}

// 2. guard.php
if (file_exists(__DIR__ . '/../app/helpers/guard.php')) {
    echo '✅ guard.php existe<br>';
} else {
    echo '❌ guard.php NÃO encontrado<br>';
}

// 3. sidebar.php
if (file_exists(__DIR__ . '/../app/views/sidebar.php')) {
    echo '✅ sidebar.php existe<br>';
} else {
    echo '❌ sidebar.php NÃO encontrado<br>';
}

// 4. Conexão PDO
try {
    $db = Database::getInstance()->getConnection();
    $db->query('SELECT 1');
    echo '✅ Conexão com banco OK<br>';
} catch (Exception $e) {
    echo '❌ Erro banco: ' . $e->getMessage() . '<br>';
}

// 5. Sessão
session_start();
echo '<br><b>Sessão:</b> ';
echo empty($_SESSION['usuario_id']) 
    ? '❌ Sem sessão ativa (faça login primeiro)' 
    : '✅ Logado como: ' . htmlspecialchars($_SESSION['usuario_nome']);

echo '<br><br><a href="login.php">Ir para o Login</a> | <a href="usuarios.php">Ir para Usuários</a>';
