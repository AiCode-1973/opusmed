<?php
/**
 * Sidebar reutilizável.
 * Requer que $permissoes e $iniciais estejam definidos (via guard.php).
 */
?>
<aside class="sidebar" id="sidebar">

    <a href="dashboard.php" class="sidebar-logo">
        <div class="logo-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <span class="logo-text">Opus<span>Med</span></span>
    </a>

    <nav class="sidebar-nav">

        <span class="nav-section">Principal</span>

        <a href="dashboard.php" data-label="Dashboard" <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Dashboard</span>
        </a>

        <?php if (!empty($permissoes['Pacientes']['pode_ver'])): ?>
        <a href="pacientes.php" data-label="Pacientes" <?= in_array(basename($_SERVER['PHP_SELF']), ['pacientes.php','paciente_form.php','paciente_excluir.php']) ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Pacientes</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Agendamento']['pode_ver'])): ?>
        <a href="agendamento.php" data-label="Agendamento" <?= basename($_SERVER['PHP_SELF']) === 'agendamento.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span>Agendamento</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Prontuário']['pode_ver'])): ?>
        <a href="prontuario.php" data-label="Prontuário" <?= basename($_SERVER['PHP_SELF']) === 'prontuario.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
            <span>Prontuário</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Internação']['pode_ver'])): ?>
        <a href="internacao.php" data-label="Internação" <?= basename($_SERVER['PHP_SELF']) === 'internacao.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Internação</span>
        </a>
        <?php endif; ?>

        <span class="nav-section">Clínico</span>

        <?php if (!empty($permissoes['Farmácia']['pode_ver'])): ?>
        <a href="farmacia.php" data-label="Farmácia" <?= basename($_SERVER['PHP_SELF']) === 'farmacia.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M18.5 2h-13A2.5 2.5 0 0 0 3 4.5v15A2.5 2.5 0 0 0 5.5 22h13a2.5 2.5 0 0 0 2.5-2.5v-15A2.5 2.5 0 0 0 18.5 2z"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            <span>Farmácia</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Laboratório']['pode_ver'])): ?>
        <a href="laboratorio.php" data-label="Laboratório" <?= basename($_SERVER['PHP_SELF']) === 'laboratorio.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M9 3h6v11l3.4 5.7A1 1 0 0 1 17.5 21h-11a1 1 0 0 1-.9-1.3L9 14V3z"/><line x1="9" y1="9" x2="15" y2="9"/></svg>
            <span>Laboratório</span>
        </a>
        <?php endif; ?>

        <span class="nav-section">Gestão</span>

        <?php if (!empty($permissoes['Financeiro']['pode_ver'])): ?>
        <a href="financeiro.php" data-label="Financeiro" <?= basename($_SERVER['PHP_SELF']) === 'financeiro.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span>Financeiro</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Relatórios']['pode_ver'])): ?>
        <a href="relatorios.php" data-label="Relatórios" <?= basename($_SERVER['PHP_SELF']) === 'relatorios.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Relatórios</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Usuários']['pode_ver'])): ?>
        <a href="usuarios.php" data-label="Usuários" <?= in_array(basename($_SERVER['PHP_SELF']), ['usuarios.php','usuario_form.php','usuario_senha.php']) ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span>Usuários</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Configurações']['pode_ver'])): ?>
        <a href="perfis.php" data-label="Perfis" <?= in_array(basename($_SERVER['PHP_SELF']), ['perfis.php','perfil_form.php']) ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            <span>Perfis</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Convênios']['pode_ver'])): ?>
        <a href="convenios.php" data-label="Convênios" <?= in_array(basename($_SERVER['PHP_SELF']), ['convenios.php','convenio_form.php']) ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span>Convênios</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Setores']['pode_ver'])): ?>
        <a href="setores.php" data-label="Setores" <?= in_array(basename($_SERVER['PHP_SELF']), ['setores.php','setor_form.php','setor_excluir.php']) ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Setores</span>
        </a>
        <?php endif; ?>

        <?php if (!empty($permissoes['Configurações']['pode_ver'])): ?>
        <a href="configuracoes.php" data-label="Configurações" <?= basename($_SERVER['PHP_SELF']) === 'configuracoes.php' ? 'class="active"' : '' ?>>
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span>Configurações</span>
        </a>
        <?php endif; ?>

    </nav>

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
