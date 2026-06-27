<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Setores', 'pode_ver');

require_once __DIR__ . '/../app/models/Setor.php';

$busca      = trim($_GET['busca'] ?? '');
$categoriaId = (int) ($_GET['categoria_id'] ?? 0);

$setorModel = new Setor();
$setores    = $setorModel->listarComFiltro($busca, $categoriaId);
$categorias = $setorModel->listarCategorias();

$msg  = $_GET['msg'] ?? '';
$msgs = [
    'criado'     => 'Setor cadastrado com sucesso.',
    'editado'    => 'Setor atualizado com sucesso.',
    'desativado' => 'Setor desativado.',
    'ativado'    => 'Setor reativado.',
    'excluido'   => 'Setor excluído com sucesso.',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setores — OpusMed</title>
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
                    <div class="page-title">Setores</div>
                    <div class="page-breadcrumb">Cadastro de setores do hospital</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Setores', 'pode_criar')): ?>
                <a href="setor_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo setor
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg !== '' && isset($msgs[$msg])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msgs[$msg]) ?></div>
            <?php endif; ?>

            <!-- Filtros -->
            <form method="GET" style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap">
                <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>"
                       placeholder="Buscar por nome ou código..."
                       style="flex:1;min-width:220px;padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                <select name="categoria_id" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $cId => $cNome): ?>
                    <option value="<?= $cId ?>" <?= $categoriaId === $cId ? 'selected' : '' ?>><?= htmlspecialchars($cNome) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Buscar
                </button>
                <?php if ($busca !== '' || $categoriaId > 0): ?>
                <a href="setores.php" class="btn btn-ghost">Limpar</a>
                <?php endif; ?>
            </form>

            <div class="card">
                <div class="card-header">
                    <h3>Setores cadastrados</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($setores) ?> resultado(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Setor</th>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($setores)): ?>
                            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">Nenhum setor encontrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($setores as $s): ?>
                            <tr>
                                <td>
                                    <span style="font-family:monospace;font-size:.82rem;background:#f0f4f8;padding:3px 8px;border-radius:6px;font-weight:700;color:var(--primary)">
                                        <?= htmlspecialchars($s['codigo']) ?>
                                    </span>
                                </td>
                                <td style="font-weight:600"><?= htmlspecialchars($s['nome']) ?></td>
                                <td>
                                    <span class="badge badge-blue"><?= htmlspecialchars($s['categoria_nome'] ?? '—') ?></span>
                                </td>
                                <td style="font-size:.82rem;color:var(--muted)">
                                    <?= $s['descricao'] ? htmlspecialchars(mb_strimwidth($s['descricao'], 0, 60, '…')) : '—' ?>
                                </td>
                                <td>
                                    <span class="badge <?= $s['ativo'] ? 'badge-green' : 'badge-red' ?>">
                                        <?= $s['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Setores', 'pode_editar')): ?>
                                        <a href="setor_form.php?id=<?= $s['id'] ?>" class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Setores', 'pode_excluir')): ?>
                                        <a href="setor_excluir.php?id=<?= $s['id'] ?>"
                                           class="btn-icon <?= $s['ativo'] ? 'btn-icon-red' : 'btn-icon-green' ?>"
                                           title="<?= $s['ativo'] ? 'Desativar' : 'Reativar' ?>"
                                           onclick="return confirm('<?= $s['ativo'] ? 'Desativar' : 'Reativar' ?> o setor \'<?= htmlspecialchars(addslashes($s['nome'])) ?>\'?')">
                                            <?php if ($s['ativo']): ?>
                                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            <?php else: ?>
                                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php endif; ?>
                                        </a>
                                        <a href="setor_delete.php?id=<?= $s['id'] ?>"
                                           class="btn-icon btn-icon-red"
                                           title="Excluir permanentemente"
                                           onclick="return confirm('Excluir PERMANENTEMENTE o setor \'<?= htmlspecialchars(addslashes($s['nome'])) ?>\'? Esta ação não pode ser desfeita.')">
                                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
