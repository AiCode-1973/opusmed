<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Configurações', 'pode_ver');

require_once __DIR__ . '/../app/models/Perfil.php';
require_once __DIR__ . '/../app/models/Modulo.php';
require_once __DIR__ . '/../app/models/PerfilPermissao.php';

$perfilModel    = new Perfil();
$moduloModel    = new Modulo();
$permissaoModel = new PerfilPermissao();

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$perfil   = null;
$permsAtuais = [];
$modulos  = $moduloModel->listarTodos(true);

if ($editando) {
    $perfil = $perfilModel->buscarPorId($id);
    if (!$perfil) {
        header('Location: perfis.php');
        exit;
    }
    $permsAtuais = $permissaoModel->buscarPorPerfil($id); // indexado por nome do módulo
}

$erro  = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome']      ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $ativo     = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '') {
        $erro = 'O nome do perfil é obrigatório.';
    } elseif (!pode('Configurações', $editando ? 'pode_editar' : 'pode_criar')) {
        $erro = 'Você não tem permissão para esta ação.';
    } else {
        try {
            if ($editando) {
                $perfilModel->atualizar($id, [
                    'nome'      => $nome,
                    'descricao' => $descricao,
                    'ativo'     => $ativo,
                ]);
            } else {
                $id = $perfilModel->criar([
                    'nome'      => $nome,
                    'descricao' => $descricao,
                ]);
                $editando = true;
            }

            // Salva permissões enviadas
            foreach ($modulos as $modulo) {
                $mid = (int) $modulo['id'];
                $permissaoModel->salvar($id, $mid, [
                    'pode_ver'     => isset($_POST['perm'][$mid]['pode_ver'])     ? 1 : 0,
                    'pode_criar'   => isset($_POST['perm'][$mid]['pode_criar'])   ? 1 : 0,
                    'pode_editar'  => isset($_POST['perm'][$mid]['pode_editar'])  ? 1 : 0,
                    'pode_excluir' => isset($_POST['perm'][$mid]['pode_excluir']) ? 1 : 0,
                ]);
            }

            header('Location: perfis.php?msg=' . ($editando && $id > 0 ? 'editado' : 'criado'));
            exit;
        } catch (\Exception $e) {
            $erro = 'Erro ao salvar: ' . htmlspecialchars($e->getMessage());
        }
    }

    // Recarrega permissões após POST com erro
    if ($id > 0) {
        $permsAtuais = $permissaoModel->buscarPorPerfil($id);
    }
}

// Helper: verifica permissão atual (POST tem prioridade se houver erro)
function permAtual($permsAtuais, $moduloNome, $tipo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Já processado acima, mas como houve erro relemos do POST
        return false; // será sobreposto pelo checked abaixo
    }
    return !empty($permsAtuais[$moduloNome][$tipo]);
}

$titulo = $editando ? 'Editar Perfil' : 'Novo Perfil';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> — OpusMed</title>
    <link rel="stylesheet" href="assets/css/app.css?v=<?= filemtime(__DIR__.'/assets/css/app.css') ?>">
    <style>
        /* ── Matriz de permissões ── */
        .perm-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        .perm-table th {
            padding: 10px 16px;
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        .perm-table th.center,
        .perm-table td.center { text-align: center; }
        .perm-table td {
            padding: 11px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        .perm-table tr:last-child td { border-bottom: none; }
        .perm-table tr:hover td { background: #f8fafc; }

        /* Checkbox estilizado */
        .perm-check {
            width: 18px; height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        /* Botão "marcar todos" da linha */
        .btn-check-all {
            background: none; border: none; cursor: pointer;
            color: var(--muted); font-size: .75rem; padding: 2px 6px;
            border-radius: 4px; transition: background .15s, color .15s;
        }
        .btn-check-all:hover { background: var(--primary-light); color: var(--primary); }

        /* Seção de permissões */
        .perm-section { margin-top: 28px; }
        .perm-section-title {
            font-size: .95rem; font-weight: 700;
            margin-bottom: 12px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .btn-marcar-todos {
            font-size: .8rem; background: var(--primary-light);
            color: var(--primary); border: none; border-radius: 6px;
            padding: 5px 12px; cursor: pointer; font-weight: 600;
            transition: background .15s;
        }
        .btn-marcar-todos:hover { background: var(--primary); color: #fff; }
    </style>
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
                    <div class="page-title"><?= $titulo ?></div>
                    <div class="page-breadcrumb">
                        <a href="perfis.php" style="color:var(--primary);text-decoration:none">Perfis</a>
                        &rsaquo; <?= $titulo ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="perfis.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Voltar
                </a>
            </div>
        </header>

        <main class="page-content">

            <?php if ($erro !== ''): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>

            <form method="POST" id="perfilForm">

                <!-- ── Dados básicos ── -->
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h3>Dados do Perfil</h3></div>
                    <div class="card-body" style="padding:24px">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="nome">Nome <span class="required">*</span></label>
                                <input type="text" id="nome" name="nome" maxlength="100" required
                                       value="<?= htmlspecialchars($_POST['nome'] ?? $perfil['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:18px">
                                <?php if ($editando): ?>
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;font-weight:600">
                                    <input type="checkbox" name="ativo" value="1"
                                           <?= ($_POST['ativo'] ?? $perfil['ativo'] ?? 1) ? 'checked' : '' ?>
                                           style="width:16px;height:16px;accent-color:var(--primary)">
                                    Perfil ativo
                                </label>
                                <?php endif; ?>
                            </div>
                            <div class="form-group full-width">
                                <label for="descricao">Descrição</label>
                                <input type="text" id="descricao" name="descricao" maxlength="255"
                                       value="<?= htmlspecialchars($_POST['descricao'] ?? $perfil['descricao'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Matriz de permissões ── -->
                <div class="card">
                    <div class="card-header">
                        <h3>Permissões por Módulo</h3>
                        <button type="button" class="btn-marcar-todos" id="btnTodos">Marcar todos</button>
                    </div>
                    <div class="card-body">
                        <table class="perm-table" id="permTable">
                            <thead>
                                <tr>
                                    <th style="width:220px">Módulo</th>
                                    <th class="center">Visualizar</th>
                                    <th class="center">Criar</th>
                                    <th class="center">Editar</th>
                                    <th class="center">Excluir</th>
                                    <th class="center">Todos</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($modulos as $m): ?>
                                <?php
                                $mid   = (int) $m['id'];
                                $mnome = $m['nome'];
                                // Prioridade: POST (se houve erro) > banco
                                $pVer  = isset($_POST['perm'][$mid]['pode_ver'])     ? true : !empty($permsAtuais[$mnome]['pode_ver']);
                                $pCri  = isset($_POST['perm'][$mid]['pode_criar'])   ? true : !empty($permsAtuais[$mnome]['pode_criar']);
                                $pEdi  = isset($_POST['perm'][$mid]['pode_editar'])  ? true : !empty($permsAtuais[$mnome]['pode_editar']);
                                $pExc  = isset($_POST['perm'][$mid]['pode_excluir']) ? true : !empty($permsAtuais[$mnome]['pode_excluir']);
                                ?>
                                <tr data-row="<?= $mid ?>">
                                    <td><strong><?= htmlspecialchars($mnome) ?></strong></td>
                                    <td class="center">
                                        <input type="checkbox" class="perm-check" name="perm[<?= $mid ?>][pode_ver]"     value="1" <?= $pVer ? 'checked' : '' ?>>
                                    </td>
                                    <td class="center">
                                        <input type="checkbox" class="perm-check" name="perm[<?= $mid ?>][pode_criar]"   value="1" <?= $pCri ? 'checked' : '' ?>>
                                    </td>
                                    <td class="center">
                                        <input type="checkbox" class="perm-check" name="perm[<?= $mid ?>][pode_editar]"  value="1" <?= $pEdi ? 'checked' : '' ?>>
                                    </td>
                                    <td class="center">
                                        <input type="checkbox" class="perm-check" name="perm[<?= $mid ?>][pode_excluir]" value="1" <?= $pExc ? 'checked' : '' ?>>
                                    </td>
                                    <td class="center">
                                        <button type="button" class="btn-check-all" data-row="<?= $mid ?>" title="Marcar/desmarcar todos">
                                            ☑
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── Ações ── -->
                <div style="display:flex;gap:12px;margin-top:24px">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Salvar perfil
                    </button>
                    <a href="perfis.php" class="btn btn-ghost">Cancelar</a>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
// Marcar/desmarcar todos de uma linha
document.querySelectorAll('.btn-check-all').forEach(btn => {
    btn.addEventListener('click', function () {
        const row  = document.querySelector('tr[data-row="' + this.dataset.row + '"]');
        const chks = row.querySelectorAll('.perm-check');
        const allChecked = Array.from(chks).every(c => c.checked);
        chks.forEach(c => c.checked = !allChecked);
    });
});

// Marcar/desmarcar TODOS os módulos
let todosChecked = false;
document.getElementById('btnTodos').addEventListener('click', function () {
    todosChecked = !todosChecked;
    document.querySelectorAll('.perm-check').forEach(c => c.checked = todosChecked);
    this.textContent = todosChecked ? 'Desmarcar todos' : 'Marcar todos';
});

// Se marca "Excluir", marca automaticamente Ver, Criar e Editar
// Se marca "Criar" ou "Editar", marca Ver
document.querySelectorAll('.perm-check').forEach(chk => {
    chk.addEventListener('change', function () {
        const row   = this.closest('tr');
        const nome  = this.name;
        const mid   = row.dataset.row;
        const ver   = row.querySelector('[name="perm[' + mid + '][pode_ver]"]');
        const criar = row.querySelector('[name="perm[' + mid + '][pode_criar]"]');
        const edi   = row.querySelector('[name="perm[' + mid + '][pode_editar]"]');
        const exc   = row.querySelector('[name="perm[' + mid + '][pode_excluir]"]');

        if (this.checked) {
            if (nome.includes('pode_excluir')) { ver.checked = criar.checked = edi.checked = true; }
            if (nome.includes('pode_criar'))   { ver.checked = true; }
            if (nome.includes('pode_editar'))  { ver.checked = true; }
        } else {
            if (nome.includes('pode_ver')) { criar.checked = edi.checked = exc.checked = false; }
        }
    });
});
</script>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
