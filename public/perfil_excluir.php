<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Configurações', 'pode_excluir');

require_once __DIR__ . '/../app/models/Perfil.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: perfis.php');
    exit;
}

$perfilModel = new Perfil();
$perfil = $perfilModel->buscarPorId($id);

if (!$perfil) {
    header('Location: perfis.php');
    exit;
}

try {
    $perfilModel->excluir($id);
    header('Location: perfis.php?msg=excluido');
} catch (\RuntimeException $e) {
    header('Location: perfis.php?msg=erro&tipo=erro');
}
exit;
