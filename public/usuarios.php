<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Usuários', 'pode_ver');

require_once __DIR__ . '/../app/models/Usuario.php';

$usuarioModel = new Usuario();
$usuarios     = $usuarioModel->listarTodos(false); // lista ativos e inativos

$msg   = $_GET['msg']   ?? '';
$tipo  = $_GET['tipo']  ?? '';
$msgs  = [
    'criado'    => 'Usuário criado com sucesso.',
    'editado'   => 'Usuário atualizado com sucesso.',
    'desativado'=> 'Usuário desativado com sucesso.',
    'ativado'   => 'Usuário ativado com sucesso.',
    'senha'     => 'Senha alterada com sucesso.',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários — OpusMed</title>
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
                    <div class="page-title">Usuários</div>
                    <div class="page-breadcrumb">Gestão de usuários do sistema</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Usuários', 'pode_criar')): ?>
                <a href="usuario_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo usuário
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg && isset($msgs[$msg])): ?>
            <div class="alert alert-<?= $tipo === 'erro' ? 'danger' : 'success' ?>">
                <?= htmlspecialchars($msgs[$msg]) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Todos os usuários</h3>
                    <span class="badge badge-blue"><?= count($usuarios) ?> registros</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>CPF</th>
                                <th>Perfil</th>
                                <th>Último acesso</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm"><?= mb_strtoupper(mb_substr($u['nome'], 0, 1)) ?></div>
                                        <?= htmlspecialchars($u['nome']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= $u['cpf'] ? htmlspecialchars(substr_replace(substr_replace($u['cpf'], '.', 3, 0), '.', 7, 0)) : '—' ?></td>
                                <td><?= htmlspecialchars($u['perfil_nome']) ?></td>
                                <td><?= $u['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : '—' ?></td>
                                <td>
                                    <?php if ($u['ativo']): ?>
                                        <span class="badge badge-green">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Usuários', 'pode_editar')): ?>
                                        <a href="usuario_form.php?id=<?= $u['id'] ?>" class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <a href="usuario_senha.php?id=<?= $u['id'] ?>" class="btn-icon btn-icon-orange" title="Alterar senha">
                                            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Usuários', 'pode_excluir') && $u['id'] != $_SESSION['usuario_id']): ?>
                                        <a href="usuario_excluir.php?id=<?= $u['id'] ?>&acao=<?= $u['ativo'] ? 'desativar' : 'ativar' ?>"
                                           class="btn-icon <?= $u['ativo'] ? 'btn-icon-red' : 'btn-icon-green' ?>"
                                           title="<?= $u['ativo'] ? 'Desativar' : 'Ativar' ?>"
                                           onclick="return confirm('Confirmar esta ação?')">
                                            <?php if ($u['ativo']): ?>
                                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                            <?php else: ?>
                                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
                                            <?php endif; ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($usuarios)): ?>
                            <tr><td colspan="7" style="text-align:center;padding:32px;color:#718096">Nenhum usuário cadastrado.</td></tr>
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
