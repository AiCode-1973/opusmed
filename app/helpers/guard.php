<?php
/**
 * Guard de sessão — inclua no topo de cada página protegida.
 * Redireciona para o login se o usuário não estiver autenticado.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/PerfilPermissao.php';

$permissaoModel = $_permissaoModel ?? new PerfilPermissao();
$permissoes     = $permissaoModel->buscarPorPerfil((int) $_SESSION['perfil_id']);

// Iniciais para o avatar
$iniciais = implode('', array_map(
    fn($p) => mb_strtoupper(mb_substr($p, 0, 1)),
    array_slice(explode(' ', $_SESSION['usuario_nome']), 0, 2)
));

/**
 * Verifica se o usuário logado tem determinada permissão no módulo.
 */
function pode(string $modulo, string $tipo = 'pode_ver'): bool {
    global $permissoes;
    return !empty($permissoes[$modulo][$tipo]);
}

/**
 * Bloqueia acesso se não tiver a permissão exigida.
 */
function exigirPermissao(string $modulo, string $tipo = 'pode_ver'): void {
    if (!pode($modulo, $tipo)) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:40px">Acesso negado. Você não tem permissão para esta ação.</p>');
    }
}
