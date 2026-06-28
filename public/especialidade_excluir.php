<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Especialidades', 'pode_excluir');

require_once __DIR__ . '/../app/models/Especialidade.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: especialidades.php');
    exit;
}

$model = new Especialidade();
$esp   = $model->buscarPorId($id);

if (!$esp) {
    header('Location: especialidades.php');
    exit;
}

$model->excluir($id);
header('Location: especialidades.php?msg=excluido');
exit;
