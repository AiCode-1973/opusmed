<?php
/**
 * Guard de sessão — inclua no topo de cada página protegida.
 * Redireciona para o login se o usuário não estiver autenticado.
 */
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../../config/config.php';
    session_name(SESSION_NAME);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    session_set_cookie_params(SESSION_LIFETIME);
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/PerfilPermissao.php';

$permissaoModel = new PerfilPermissao();
$permissoes     = $permissaoModel->buscarPorPerfil((int) $_SESSION['perfil_id']);

// Iniciais para o avatar
$partesNome = array_slice(explode(' ', $_SESSION['usuario_nome']), 0, 2);
$iniciais   = '';
foreach ($partesNome as $parte) {
    $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
}

/**
 * Verifica se o usuário logado tem determinada permissão no módulo.
 */
function pode($modulo, $tipo = 'pode_ver') {
    global $permissoes;
    return !empty($permissoes[$modulo][$tipo]);
}

/**
 * Bloqueia acesso se não tiver a permissão exigida.
 */
function exigirPermissao($modulo, $tipo = 'pode_ver') {
    if (!pode($modulo, $tipo)) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:40px">Acesso negado. Você não tem permissão para esta ação.</p>');
    }
}
