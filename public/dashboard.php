<?php
// ── Guard de sessão ──────────────────────────────────────────
require_once __DIR__ . '/../app/helpers/guard.php';

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
    <link rel="stylesheet" href="assets/css/app.css?v=<?= filemtime(__DIR__.'/assets/css/app.css') ?>">
</head>
<body>

<div class="app-wrapper">

    <?php include __DIR__ . '/../app/views/sidebar.php'; ?>

    <!-- ══════════════════════════════════════
         ÁREA PRINCIPAL
    ══════════════════════════════════════ -->
    <div class="main-area">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar" aria-label="Recolher menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6"  x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
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
<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>

</body>
</html>
