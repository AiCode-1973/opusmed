<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Pacientes', 'pode_excluir');

require_once __DIR__ . '/../app/models/Paciente.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header('Location: pacientes.php'); exit; }

$model   = new Paciente();
$paciente = $model->buscarPorId($id);
if (!$paciente) { header('Location: pacientes.php'); exit; }

$model->toggleAtivo($id);

$msg = $paciente['ativo'] ? 'desativado' : 'ativado';
header('Location: pacientes.php?msg=' . $msg);
exit;
