<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Categorias de Setores', 'pode_criar');

require_once __DIR__ . '/../app/models/CategoriaSetor.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'Método inválido.']);
    exit;
}

$nome      = trim($_POST['nome']      ?? '');
$descricao = trim($_POST['descricao'] ?? '');

if ($nome === '') {
    echo json_encode(['ok' => false, 'msg' => 'O nome da categoria é obrigatório.']);
    exit;
}
if (mb_strlen($nome) > 100) {
    echo json_encode(['ok' => false, 'msg' => 'O nome deve ter no máximo 100 caracteres.']);
    exit;
}

$model = new CategoriaSetor();

if ($model->nomeExiste($nome)) {
    echo json_encode(['ok' => false, 'msg' => 'Já existe uma categoria com este nome.']);
    exit;
}

$id = $model->criar(['nome' => $nome, 'descricao' => $descricao, 'ativo' => 1]);

echo json_encode(['ok' => true, 'id' => $id, 'nome' => $nome]);
