<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Categorias de Setores', 'pode_ver');

require_once __DIR__ . '/../app/models/CategoriaSetor.php';

$model    = new CategoriaSetor();
$id       = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editando = $id > 0;

if ($editando) {
    exigirPermissao('Categorias de Setores', 'pode_editar');
    $reg = $model->buscarPorId($id);
    if (!$reg) {
        header('Location: categorias_setor.php');
        exit;
    }
} else {
    exigirPermissao('Categorias de Setores', 'pode_criar');
    $reg = null;
}

$erros = [];
$v     = [
    'nome'      => $reg['nome']      ?? '',
    'descricao' => $reg['descricao'] ?? '',
    'ativo'     => $reg ? (int) $reg['ativo'] : 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['nome']      = trim($_POST['nome']      ?? '');
    $v['descricao'] = trim($_POST['descricao'] ?? '');
    $v['ativo']     = isset($_POST['ativo']) ? 1 : 0;

    if ($v['nome'] === '') {
        $erros[] = 'O nome da categoria é obrigatório.';
    } elseif (mb_strlen($v['nome']) > 100) {
        $erros[] = 'O nome deve ter no máximo 100 caracteres.';
    } elseif ($model->nomeExiste($v['nome'], $id)) {
        $erros[] = 'Já existe uma categoria com este nome.';
    }

    if (empty($erros)) {
        if ($editando) {
            $model->atualizar($id, $v);
            header('Location: categorias_setor.php?msg=editado');
        } else {
            $model->criar($v);
            header('Location: categorias_setor.php?msg=criado');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar categoria' : 'Nova categoria' ?> — OpusMed</title>
    <link rel="stylesheet" href="assets/css/app.css?v=<?= filemtime(__DIR__.'/assets/css/app.css') ?>">
</head>
<body>
<div class="app-wrapper">

    <?php include __DIR__ . '/../app/views/sidebar.php'; ?>

    <div class="main-area">
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar" aria-label="Recolher menu">
                    <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div>
                    <div class="page-title"><?= $editando ? 'Editar categoria' : 'Nova categoria' ?></div>
                    <div class="page-breadcrumb">
                        <a href="setores.php">Setores</a> /
                        <a href="categorias_setor.php">Categorias</a> /
                        <?= $editando ? htmlspecialchars($reg['nome']) : 'Nova categoria' ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="categorias_setor.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </header>

        <main class="page-content">

            <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <?php foreach ($erros as $e): ?>
                <div>• <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="categoria_setor_form.php<?= $editando ? "?id=$id" : '' ?>">
                <div class="card" style="max-width:520px">
                    <div class="card-header">
                        <h3>Dados da categoria</h3>
                    </div>
                    <div class="card-body" style="padding:24px">

                        <!-- Nome -->
                        <div class="form-group">
                            <label class="form-label required">Nome da categoria</label>
                            <input type="text" name="nome" class="form-control"
                                   value="<?= htmlspecialchars($v['nome']) ?>"
                                   placeholder="Ex: UTI, Centro Cirúrgico, Maternidade…"
                                   maxlength="100"
                                   autofocus>
                        </div>

                        <!-- Descrição -->
                        <div class="form-group">
                            <label class="form-label">Descrição <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
                            <textarea name="descricao" class="form-control form-textarea"
                                      rows="3"
                                      placeholder="Descreva o tipo de setor que se enquadra nesta categoria…"><?= htmlspecialchars($v['descricao']) ?></textarea>
                        </div>

                        <?php if ($editando): ?>
                        <!-- Status -->
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                                <input type="checkbox" name="ativo" value="1" <?= $v['ativo'] ? 'checked' : '' ?>
                                       style="width:18px;height:18px;cursor:pointer">
                                <span>Categoria ativa (disponível ao cadastrar setores)</span>
                            </label>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="card-footer" style="display:flex;justify-content:flex-end;gap:12px;padding:18px 24px;border-top:1px solid var(--border)">
                        <a href="categorias_setor.php" class="btn btn-ghost">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <?= $editando ? 'Salvar alterações' : 'Cadastrar categoria' ?>
                        </button>
                    </div>
                </div>
            </form>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
