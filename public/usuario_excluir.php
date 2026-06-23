<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Usuários', 'pode_excluir');

require_once __DIR__ . '/../app/models/Usuario.php';

$id   = isset($_GET['id'])   ? (int) $_GET['id']   : 0;
$acao = $_GET['acao'] ?? 'desativar';

// Protege: não pode desativar a si mesmo
if ($id === (int) $_SESSION['usuario_id']) {
    header('Location: usuarios.php');
    exit;
}

$usuarioModel = new Usuario();
$usuario      = $usuarioModel->buscarPorId($id);

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

if ($acao === 'desativar') {
    $usuarioModel->atualizar($id, ['ativo' => 0]);
    header('Location: usuarios.php?msg=desativado');
} else {
    $usuarioModel->atualizar($id, ['ativo' => 1]);
    header('Location: usuarios.php?msg=ativado');
}
exit;
