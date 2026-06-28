<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Médicos', 'pode_excluir');

require_once __DIR__ . '/../app/models/Medico.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: medicos.php');
    exit;
}

$medicoModel = new Medico();
$medico = $medicoModel->buscarPorId($id);

if (!$medico) {
    header('Location: medicos.php');
    exit;
}

$medicoModel->excluir($id);
header('Location: medicos.php?msg=excluido');
exit;
