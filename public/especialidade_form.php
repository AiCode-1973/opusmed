<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Especialidades', 'pode_ver');

require_once __DIR__ . '/../app/models/Especialidade.php';

$model    = new Especialidade();
$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$esp      = null;

if ($editando) {
    $esp = $model->buscarPorId($id);
    if (!$esp) { header('Location: especialidades.php'); exit; }
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');

    if ($nome === '') {
        $erro = 'O nome da especialidade é obrigatório.';
    } elseif (!pode('Especialidades', $editando ? 'pode_editar' : 'pode_criar')) {
        $erro = 'Você não tem permissão para esta ação.';
    }

    if ($erro === '') {
        try {
            $dados = [
                'nome'        => $nome,
                'codigo_cbos' => trim($_POST['codigo_cbos'] ?? ''),
                'descricao'   => trim($_POST['descricao']   ?? ''),
                'ativo'       => isset($_POST['ativo']) ? 1 : 0,
            ];

            if ($editando) {
                $model->atualizar($id, $dados);
                header('Location: especialidades.php?msg=editado');
            } else {
                $model->criar($dados);
                header('Location: especialidades.php?msg=criado');
            }
            exit;
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Duplicate') || str_contains($msg, '1062')) {
                $erro = 'Já existe uma especialidade com este nome.';
            } else {
                $erro = 'Erro ao salvar: ' . htmlspecialchars($msg);
            }
        }
    }
}

// Valores do formulário (POST tem prioridade sobre banco)
$campos = ['nome', 'codigo_cbos', 'descricao', 'ativo'];
$v = [];
foreach ($campos as $c) {
    $v[$c] = $_POST[$c] ?? $esp[$c] ?? '';
}
if (!$editando && !isset($_POST['ativo'])) {
    $v['ativo'] = 1; // padrão ativo para novo cadastro
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar especialidade' : 'Nova especialidade' ?> — OpusMed</title>
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
                    <div class="page-title"><?= $editando ? 'Editar especialidade' : 'Nova especialidade' ?></div>
                    <div class="page-breadcrumb">
                        <a href="especialidades.php" style="color:var(--primary);text-decoration:none">Especialidades</a>
                        &rsaquo; <?= $editando ? htmlspecialchars($esp['nome']) : 'Nova' ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="especialidades.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Voltar
                </a>
            </div>
        </header>

        <main class="page-content">

            <?php if ($erro !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="card" style="max-width:680px">
                <div class="card-header">
                    <h3><?= $editando ? 'Editar especialidade' : 'Nova especialidade' ?></h3>
                </div>
                <div class="tab-content" style="border-top:none">
                    <form method="POST" novalidate>

                        <div class="form-group">
                            <label>Nome da especialidade <span class="required">*</span></label>
                            <input type="text" name="nome"
                                   value="<?= htmlspecialchars($v['nome']) ?>"
                                   placeholder="Ex.: Cardiologia, Neurologia…"
                                   required maxlength="120" autofocus>
                        </div>

                        <div class="form-group">
                            <label>Código CBO-S
                                <span class="form-hint" style="display:inline;margin-left:4px">
                                    Classificação Brasileira de Ocupações em Saúde
                                </span>
                            </label>
                            <input type="text" name="codigo_cbos"
                                   value="<?= htmlspecialchars($v['codigo_cbos']) ?>"
                                   placeholder="Ex.: 2251-10"
                                   maxlength="10"
                                   style="max-width:200px">
                        </div>

                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea name="descricao" class="form-textarea" rows="4"
                                      placeholder="Descrição opcional da especialidade…"><?= htmlspecialchars($v['descricao']) ?></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom:24px">
                            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:600;font-size:.83rem">
                                <input type="checkbox" name="ativo" value="1"
                                       <?= $v['ativo'] ? 'checked' : '' ?>
                                       style="width:17px;height:17px;accent-color:var(--primary);cursor:pointer">
                                Especialidade ativa
                            </label>
                            <span class="form-hint">Somente especialidades ativas aparecem nos cadastros de médicos.</span>
                        </div>

                        <div style="display:flex;gap:10px">
                            <button type="submit" class="btn btn-primary">
                                <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                <?= $editando ? 'Salvar alterações' : 'Cadastrar' ?>
                            </button>
                            <a href="especialidades.php" class="btn btn-ghost">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
