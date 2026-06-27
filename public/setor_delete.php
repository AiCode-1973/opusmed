<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Setores', 'pode_excluir');

require_once __DIR__ . '/../app/models/Setor.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: setores.php');
    exit;
}

$model = new Setor();
$setor = $model->buscarPorId($id);
if (!$setor) {
    header('Location: setores.php');
    exit;
}

$model->excluir($id);

header('Location: setores.php?msg=excluido');
exit;
