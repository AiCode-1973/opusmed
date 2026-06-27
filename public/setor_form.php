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
    'codigo'      => $reg['codigo']      ?? '',
    'nome'        => $reg['nome']        ?? '',
    'categoria_id'=> $reg['categoria_id'] ?? '',
    'descricao'   => $reg['descricao']   ?? '',
    'ativo'       => $reg ? (int) $reg['ativo'] : 1,
];

// Carrega categorias ativas do banco
$categorias = $setorModel->listarCategorias();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['codigo']      = trim($_POST['codigo']    ?? '');
    $v['nome']        = trim($_POST['nome']      ?? '');
    $v['categoria_id']= (int) ($_POST['categoria_id'] ?? 0);
    $v['descricao']   = trim($_POST['descricao'] ?? '');
    $v['ativo']       = isset($_POST['ativo']) ? 1 : 0;

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

    if ($v['categoria_id'] <= 0 || !isset($categorias[$v['categoria_id']])) {
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
                    <div class="card-body" style="padding:24px">

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
                                <div class="input-addon">
                                    <select id="selectCategoria" name="categoria_id" class="form-control">
                                        <option value="">Selecione…</option>
                                        <?php foreach ($categorias as $cId => $cNome): ?>
                                        <option value="<?= $cId ?>" <?= (int) $v['categoria_id'] === $cId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cNome) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (pode('Categorias de Setores', 'pode_criar')): ?>
                                    <button type="button" class="btn-addon" id="btnNovaCategoria" title="Nova categoria">
                                        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    </button>
                                    <?php endif; ?>
                                </div>
                                <span class="form-hint"><a href="categorias_setor.php" target="_blank" style="color:var(--primary)">Gerenciar categorias</a></span>
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

<!-- ====================================================
     Modal — Nova Categoria
     ==================================================== -->
<div class="modal-backdrop" id="modalCategoria" role="dialog" aria-modal="true" aria-labelledby="modalCatTitulo">
    <div class="modal">
        <div class="modal-header">
            <h4 id="modalCatTitulo">Nova categoria de setor</h4>
            <button type="button" class="modal-close" id="btnFecharModal" aria-label="Fechar">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-error" id="modalErro"></div>

            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label required" for="modalNome">Nome da categoria</label>
                <input type="text" id="modalNome" class="form-control"
                       placeholder="Ex: UTI, Maternidade, Ambulatório…"
                       maxlength="100" autocomplete="off">
            </div>
            <div class="form-group" style="margin-bottom:0">
                <label class="form-label" for="modalDescricao">Descrição <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
                <textarea id="modalDescricao" class="form-control form-textarea" rows="2"
                          placeholder="Descrição da categoria…"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" id="btnCancelarModal">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnSalvarCategoria">
                <svg viewBox="0 0 24 24" style="width:15px;height:15px"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Salvar categoria
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const backdrop   = document.getElementById('modalCategoria');
    const inputNome  = document.getElementById('modalNome');
    const inputDesc  = document.getElementById('modalDescricao');
    const erroDiv    = document.getElementById('modalErro');
    const btnSalvar  = document.getElementById('btnSalvarCategoria');
    const selectCat  = document.getElementById('selectCategoria');

    function abrirModal() {
        inputNome.value  = '';
        inputDesc.value  = '';
        erroDiv.style.display = 'none';
        erroDiv.textContent   = '';
        backdrop.classList.add('open');
        setTimeout(() => inputNome.focus(), 150);
    }

    function fecharModal() {
        backdrop.classList.remove('open');
    }

    document.getElementById('btnNovaCategoria')  && document.getElementById('btnNovaCategoria').addEventListener('click', abrirModal);
    document.getElementById('btnFecharModal').addEventListener('click', fecharModal);
    document.getElementById('btnCancelarModal').addEventListener('click', fecharModal);

    // Fecha ao clicar fora
    backdrop.addEventListener('click', function (e) {
        if (e.target === backdrop) fecharModal();
    });

    // Fecha com Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && backdrop.classList.contains('open')) fecharModal();
    });

    // Enter no campo nome confirma
    inputNome.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); btnSalvar.click(); }
    });

    btnSalvar.addEventListener('click', function () {
        const nome = inputNome.value.trim();
        if (!nome) {
            erroDiv.textContent   = 'O nome da categoria é obrigatório.';
            erroDiv.style.display = 'block';
            inputNome.focus();
            return;
        }

        btnSalvar.disabled = true;
        btnSalvar.textContent = 'Salvando…';

        const body = new FormData();
        body.append('nome',      nome);
        body.append('descricao', inputDesc.value.trim());

        fetch('categoria_setor_ajax.php', { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    erroDiv.textContent   = data.msg || 'Erro ao salvar.';
                    erroDiv.style.display = 'block';
                    return;
                }
                // Adiciona a nova opção no select e seleciona
                const opt = new Option(data.nome, data.id, true, true);
                selectCat.add(opt);
                selectCat.value = data.id;
                fecharModal();
            })
            .catch(() => {
                erroDiv.textContent   = 'Erro de comunicação. Tente novamente.';
                erroDiv.style.display = 'block';
            })
            .finally(() => {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Salvar categoria';
            });
    });
}());
</script>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
