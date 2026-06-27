<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Setores', 'pode_ver');

require_once __DIR__ . '/../app/models/Setor.php';

$setorModel = new Setor();
$id         = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editando   = $id > 0;

if ($editando) {
    exigirPermissao('Setores', 'pode_editar');
    $reg = $setorModel->buscarPorId($id);
    if (!$reg) {
        header('Location: setores.php');
        exit;
    }
} else {
    exigirPermissao('Setores', 'pode_criar');
    $reg = null;
}

$erros = [];
$v     = [
    'codigo'    => $reg['codigo']    ?? '',
    'nome'      => $reg['nome']      ?? '',
    'categoria' => $reg['categoria'] ?? '',
    'descricao' => $reg['descricao'] ?? '',
    'ativo'     => $reg ? (int) $reg['ativo'] : 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['codigo']    = trim($_POST['codigo']    ?? '');
    $v['nome']      = trim($_POST['nome']      ?? '');
    $v['categoria'] = trim($_POST['categoria'] ?? '');
    $v['descricao'] = trim($_POST['descricao'] ?? '');
    $v['ativo']     = isset($_POST['ativo']) ? 1 : 0;

    if ($v['codigo'] === '') {
        $erros[] = 'O código do setor é obrigatório.';
    } elseif (!preg_match('/^[A-Za-z0-9\-_\.]+$/', $v['codigo'])) {
        $erros[] = 'O código deve conter apenas letras, números, hífen ou underline.';
    } elseif ($setorModel->codigoExiste($v['codigo'], $id)) {
        $erros[] = 'Já existe um setor com este código.';
    }

    if ($v['nome'] === '') {
        $erros[] = 'O nome do setor é obrigatório.';
    }

    if ($v['categoria'] === '' || !isset(Setor::$categorias[$v['categoria']])) {
        $erros[] = 'Selecione uma categoria válida.';
    }

    if (empty($erros)) {
        if ($editando) {
            $setorModel->atualizar($id, $v);
            header('Location: setores.php?msg=editado');
        } else {
            $setorModel->criar($v);
            header('Location: setores.php?msg=criado');
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
    <title><?= $editando ? 'Editar setor' : 'Novo setor' ?> — OpusMed</title>
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
                    <div class="page-title"><?= $editando ? 'Editar setor' : 'Novo setor' ?></div>
                    <div class="page-breadcrumb">
                        <a href="setores.php">Setores</a> /
                        <?= $editando ? htmlspecialchars($reg['nome']) : 'Novo setor' ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="setores.php" class="btn btn-ghost">Cancelar</a>
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

            <form method="POST" action="setor_form.php<?= $editando ? "?id=$id" : '' ?>">
                <div class="card" style="max-width:640px">
                    <div class="card-header">
                        <h3>Dados do setor</h3>
                    </div>
                    <div class="card-body">

                        <div class="form-grid-2">
                            <!-- Código do setor -->
                            <div class="form-group">
                                <label class="form-label required">Código do setor</label>
                                <input type="text" name="codigo" class="form-control"
                                       value="<?= htmlspecialchars($v['codigo']) ?>"
                                       placeholder="Ex: UTI-01, CIRC-01"
                                       maxlength="20"
                                       style="text-transform:uppercase"
                                       oninput="this.value=this.value.toUpperCase()">
                                <small class="form-hint">Único, sem espaços (ex.: UTI-01)</small>
                            </div>

                            <!-- Categoria -->
                            <div class="form-group">
                                <label class="form-label required">Categoria</label>
                                <select name="categoria" class="form-control">
                                    <option value="">Selecione…</option>
                                    <?php foreach (Setor::$categorias as $val => $label): ?>
                                    <option value="<?= htmlspecialchars($val) ?>" <?= $v['categoria'] === $val ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Nome do setor (largura total) -->
                        <div class="form-group">
                            <label class="form-label required">Nome do setor</label>
                            <input type="text" name="nome" class="form-control"
                                   value="<?= htmlspecialchars($v['nome']) ?>"
                                   placeholder="Ex: UTI Adulto, Centro Cirúrgico 1"
                                   maxlength="120">
                        </div>

                        <!-- Descrição -->
                        <div class="form-group">
                            <label class="form-label">Descrição <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
                            <textarea name="descricao" class="form-control form-textarea"
                                      rows="3"
                                      placeholder="Informações adicionais sobre o setor…"><?= htmlspecialchars($v['descricao']) ?></textarea>
                        </div>

                        <?php if ($editando): ?>
                        <!-- Status -->
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                                <input type="checkbox" name="ativo" value="1" <?= $v['ativo'] ? 'checked' : '' ?>
                                       style="width:18px;height:18px;cursor:pointer">
                                <span>Setor ativo (visível no sistema)</span>
                            </label>
                        </div>
                        <?php endif; ?>

                    </div>

                    <div class="card-footer" style="display:flex;justify-content:flex-end;gap:12px;padding:18px 24px;border-top:1px solid var(--border)">
                        <a href="setores.php" class="btn btn-ghost">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <?= $editando ? 'Salvar alterações' : 'Cadastrar setor' ?>
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
