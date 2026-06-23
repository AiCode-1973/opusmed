<?php
// ── Guard de sessão ──────────────────────────────────────────
session_start();
if (empty($_SESSION['usuario_id'])) {
    header('Location: /opusmed/public/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/PerfilPermissao.php';

$permissaoModel = new PerfilPermissao();
$permissoes     = $permissaoModel->buscarPorPerfil((int) $_SESSION['perfil_id']);

// Iniciais para o avatar
$iniciais = implode('', array_map(
    fn($p) => mb_strtoupper(mb_substr($p, 0, 1)),
    array_slice(explode(' ', $_SESSION['usuario_nome']), 0, 2)
));

// Estatísticas básicas
$db = Database::getInstance()->getConnection();
$totalUsuarios = $db->query('SELECT COUNT(*) FROM usuarios WHERE ativo=1')->fetchColumn();
$totalPerfis   = $db->query('SELECT COUNT(*) FROM perfis  WHERE ativo=1')->fetchColumn();
$totalModulos  = $db->query('SELECT COUNT(*) FROM modulos WHERE ativo=1')->fetchColumn();

$ultimosUsuarios = $db->query('
    SELECT u.nome, u.email, u.ultimo_acesso, p.nome AS perfil_nome, u.ativo
    FROM usuarios u
    INNER JOIN perfis p ON p.id = u.perfil_id
    ORDER BY u.created_at DESC
    LIMIT 8
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — OpusMed</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>

<div class="app-wrapper">

    <!-- ══════════════════════════════════════
         SIDEBAR
    ══════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">

        <a href="dashboard.php" class="sidebar-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
            <span class="logo-text">Opus<span>Med</span></span>
        </a>

        <nav class="sidebar-nav">

            <span class="nav-section">Principal</span>

            <a href="dashboard.php" class="active">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>

            <?php if (!empty($permissoes['Pacientes']['pode_ver'])): ?>
            <a href="pacientes.php">
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Pacientes
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Agendamento']['pode_ver'])): ?>
            <a href="agendamento.php">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Agendamento
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Prontuário']['pode_ver'])): ?>
            <a href="prontuario.php">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
                Prontuário
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Internação']['pode_ver'])): ?>
            <a href="internacao.php">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Internação
            </a>
            <?php endif; ?>

            <span class="nav-section">Clínico</span>

            <?php if (!empty($permissoes['Farmácia']['pode_ver'])): ?>
            <a href="farmacia.php">
                <svg viewBox="0 0 24 24"><path d="M18.5 2h-13A2.5 2.5 0 0 0 3 4.5v15A2.5 2.5 0 0 0 5.5 22h13a2.5 2.5 0 0 0 2.5-2.5v-15A2.5 2.5 0 0 0 18.5 2z"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Farmácia
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Laboratório']['pode_ver'])): ?>
            <a href="laboratorio.php">
                <svg viewBox="0 0 24 24"><path d="M9 3h6v11l3.4 5.7A1 1 0 0 1 17.5 21h-11a1 1 0 0 1-.9-1.3L9 14V3z"/><line x1="9" y1="9" x2="15" y2="9"/></svg>
                Laboratório
            </a>
            <?php endif; ?>

            <span class="nav-section">Gestão</span>

            <?php if (!empty($permissoes['Financeiro']['pode_ver'])): ?>
            <a href="financeiro.php">
                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Financeiro
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Relatórios']['pode_ver'])): ?>
            <a href="relatorios.php">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6"  y1="20" x2="6"  y2="14"/></svg>
                Relatórios
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Usuários']['pode_ver'])): ?>
            <a href="usuarios.php">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Usuários
            </a>
            <?php endif; ?>

            <?php if (!empty($permissoes['Configurações']['pode_ver'])): ?>
            <a href="configuracoes.php">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Configurações
            </a>
            <?php endif; ?>

        </nav>

        <!-- Usuário logado -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar"><?= htmlspecialchars($iniciais) ?></div>
                <div class="user-info">
                    <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
                    <span><?= htmlspecialchars($_SESSION['perfil_nome']) ?></span>
                </div>
                <a href="logout.php" class="btn-logout" title="Sair">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
        </div>

    </aside>

    <!-- ══════════════════════════════════════
         ÁREA PRINCIPAL
    ══════════════════════════════════════ -->
    <div class="main-area">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Dashboard</div>
                    <div class="page-breadcrumb">Visão geral do sistema</div>
                </div>
            </div>
            <div class="topbar-right">
                <span class="topbar-date" id="relogio"></span>
            </div>
        </header>

        <!-- Conteúdo -->
        <main class="page-content">

            <!-- Cards de estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">👥</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= (int) $totalUsuarios ?></div>
                        <div class="stat-label">Usuários ativos</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">🏷️</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= (int) $totalPerfis ?></div>
                        <div class="stat-label">Perfis de acesso</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">🧩</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= (int) $totalModulos ?></div>
                        <div class="stat-label">Módulos ativos</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">📅</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= date('d/m') ?></div>
                        <div class="stat-label">Data de hoje</div>
                    </div>
                </div>
            </div>

            <!-- Tabela: últimos usuários cadastrados -->
            <div class="card">
                <div class="card-header">
                    <h3>Usuários cadastrados</h3>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Último acesso</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($ultimosUsuarios as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['nome']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['perfil_nome']) ?></td>
                                <td><?= $u['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acesso'])) : '—' ?></td>
                                <td>
                                    <?php if ($u['ativo']): ?>
                                        <span class="badge badge-green">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Inativo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div><!-- /main-area -->

</div><!-- /app-wrapper -->

<script>
// Relógio no topbar
function atualizarRelogio() {
    const agora = new Date();
    document.getElementById('relogio').textContent =
        agora.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' })
        + ' ' + agora.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}
atualizarRelogio();
setInterval(atualizarRelogio, 30000);
</script>

</body>
</html>
