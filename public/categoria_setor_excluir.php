<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Categorias de Setores', 'pode_excluir');

require_once __DIR__ . '/../app/models/CategoriaSetor.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: categorias_setor.php');
    exit;
}

$model    = new CategoriaSetor();
$categoria = $model->buscarPorId($id);
if (!$categoria) {
    header('Location: categorias_setor.php');
    exit;
}

$model->toggleAtivo($id);
$msg = $categoria['ativo'] ? 'desativado' : 'ativado';

header("Location: categorias_setor.php?msg=$msg");
exit;
