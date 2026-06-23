<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Convênios', 'pode_excluir');

require_once __DIR__ . '/../app/models/Convenio.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: convenios.php');
    exit;
}

$convenioModel = new Convenio();
$convenio = $convenioModel->buscarPorId($id);

if (!$convenio) {
    header('Location: convenios.php');
    exit;
}

$convenioModel->excluir($id);
header('Location: convenios.php?msg=excluido');
exit;
