<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Categorias de Setores', 'pode_ver');

require_once __DIR__ . '/../app/models/CategoriaSetor.php';

$model     = new CategoriaSetor();
$categorias = $model->listarTodos();

$msg  = $_GET['msg'] ?? '';
$msgs = [
    'criado'     => 'Categoria cadastrada com sucesso.',
    'editado'    => 'Categoria atualizada com sucesso.',
    'desativado' => 'Categoria desativada.',
    'ativado'    => 'Categoria reativada.',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias de Setores — OpusMed</title>
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
                    <div class="page-title">Categorias de Setores</div>
                    <div class="page-breadcrumb">
                        <a href="setores.php">Setores</a> / Categorias
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="setores.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Voltar
                </a>
                <?php if (pode('Categorias de Setores', 'pode_criar')): ?>
                <a href="categoria_setor_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nova categoria
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg !== '' && isset($msgs[$msg])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msgs[$msg]) ?></div>
            <?php endif; ?>

            <div class="card" style="max-width:720px">
                <div class="card-header">
                    <h3>Categorias cadastradas</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($categorias) ?> categoria(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Setores vinculados</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($categorias)): ?>
                            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">Nenhuma categoria encontrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($categorias as $c): ?>
                            <?php $total = $model->contarSetores((int) $c['id']); ?>
                            <tr>
                                <td style="font-weight:600"><?= htmlspecialchars($c['nome']) ?></td>
                                <td style="font-size:.82rem;color:var(--muted)">
                                    <?= $c['descricao'] ? htmlspecialchars(mb_strimwidth($c['descricao'], 0, 70, '…')) : '—' ?>
                                </td>
                                <td>
                                    <?php if ($total > 0): ?>
                                    <a href="setores.php?categoria_id=<?= $c['id'] ?>" style="font-size:.82rem;color:var(--primary);font-weight:600">
                                        <?= $total ?> setor<?= $total > 1 ? 'es' : '' ?>
                                    </a>
                                    <?php else: ?>
                                    <span style="font-size:.82rem;color:var(--muted)">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $c['ativo'] ? 'badge-green' : 'badge-red' ?>">
                                        <?= $c['ativo'] ? 'Ativa' : 'Inativa' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Categorias de Setores', 'pode_editar')): ?>
                                        <a href="categoria_setor_form.php?id=<?= $c['id'] ?>" class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Categorias de Setores', 'pode_excluir')): ?>
                                        <a href="categoria_setor_excluir.php?id=<?= $c['id'] ?>"
                                           class="btn-icon <?= $c['ativo'] ? 'btn-icon-red' : 'btn-icon-green' ?>"
                                           title="<?= $c['ativo'] ? 'Desativar' : 'Reativar' ?>"
                                           onclick="return confirm('<?= $c['ativo'] ? 'Desativar' : 'Reativar' ?> a categoria \'<?= htmlspecialchars(addslashes($c['nome'])) ?>\'?')">
                                            <?php if ($c['ativo']): ?>
                                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            <?php else: ?>
                                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php endif; ?>
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
