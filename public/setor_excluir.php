<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Setores', 'pode_excluir');

require_once __DIR__ . '/../app/models/Setor.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: setores.php');
    exit;
}

$setorModel = new Setor();
$setor      = $setorModel->buscarPorId($id);
if (!$setor) {
    header('Location: setores.php');
    exit;
}

$setorModel->toggleAtivo($id);
$msg = $setor['ativo'] ? 'desativado' : 'ativado';

header("Location: setores.php?msg=$msg");
exit;
