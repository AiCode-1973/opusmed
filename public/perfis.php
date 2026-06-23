<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Configurações', 'pode_ver');

require_once __DIR__ . '/../app/models/Perfil.php';

$perfilModel = new Perfil();
$perfis      = $perfilModel->listarTodos(false);

$msg  = $_GET['msg']  ?? '';
$tipo = $_GET['tipo'] ?? '';
$msgs = [
    'criado'   => 'Perfil criado com sucesso.',
    'editado'  => 'Perfil atualizado com sucesso.',
    'excluido' => 'Perfil excluído com sucesso.',
    'erro'     => 'Não é possível excluir: existem usuários vinculados a este perfil.',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfis — OpusMed</title>
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
                    <div class="page-title">Perfis de Acesso</div>
                    <div class="page-breadcrumb">Gerenciar perfis e permissões</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Configurações', 'pode_criar')): ?>
                <a href="perfil_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo perfil
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg !== '' && isset($msgs[$msg])): ?>
            <div class="alert alert-<?= $tipo === 'erro' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($msgs[$msg]) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Perfis cadastrados</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($perfis) ?> perfil(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($perfis)): ?>
                            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">Nenhum perfil cadastrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($perfis as $p): ?>
                            <tr>
                                <td style="color:var(--muted);font-size:.82rem"><?= $p['id'] ?></td>
                                <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
                                <td style="color:var(--muted)"><?= htmlspecialchars($p['descricao'] ?? '—') ?></td>
                                <td>
                                    <?php if ($p['ativo']): ?>
                                        <span class="badge badge-green">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color:var(--muted);font-size:.82rem">
                                    <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Configurações', 'pode_editar')): ?>
                                        <a href="perfil_form.php?id=<?= $p['id'] ?>" class="btn-icon btn-icon-blue" title="Editar perfil e permissões">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Configurações', 'pode_excluir')): ?>
                                        <a href="perfil_excluir.php?id=<?= $p['id'] ?>"
                                           class="btn-icon btn-icon-red" title="Excluir perfil"
                                           onclick="return confirm('Excluir o perfil \'<?= htmlspecialchars(addslashes($p['nome'])) ?>\'? Esta ação não pode ser desfeita.')">
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
