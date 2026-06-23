<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Convênios', 'pode_ver');

require_once __DIR__ . '/../app/models/Convenio.php';

$convenioModel = new Convenio();
$convenios     = $convenioModel->listarTodos(false);

$msg  = $_GET['msg']  ?? '';
$tipo = $_GET['tipo'] ?? '';
$msgs = [
    'criado'   => 'Convênio criado com sucesso.',
    'editado'  => 'Convênio atualizado com sucesso.',
    'excluido' => 'Convênio excluído com sucesso.',
];

$badgeTipo = [
    'plano_saude' => 'badge-blue',
    'sus'         => 'badge-green',
    'particular'  => 'badge-orange',
    'outros'      => 'badge-orange',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convênios — OpusMed</title>
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
                    <div class="page-title">Convênios</div>
                    <div class="page-breadcrumb">Planos de saúde e convênios cadastrados</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Convênios', 'pode_criar')): ?>
                <a href="convenio_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo convênio
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg !== '' && isset($msgs[$msg])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msgs[$msg]) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Convênios cadastrados</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($convenios) ?> registro(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Cód. ANS</th>
                                <th>CNPJ</th>
                                <th>Telefone</th>
                                <th>Carência</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($convenios)): ?>
                            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">Nenhum convênio cadastrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($convenios as $c): ?>
                            <?php
                                $cnpjFmt = $c['cnpj'] ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $c['cnpj']) : '—';
                                $telFmt  = $c['telefone'] ?? '—';
                                $tipoLabel = Convenio::$tipos[$c['tipo']] ?? $c['tipo'];
                                $tipoBadge = $badgeTipo[$c['tipo']] ?? 'badge-orange';
                            ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm" style="background:var(--primary-dark)">
                                            <?= mb_strtoupper(mb_substr($c['nome'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($c['nome']) ?></strong>
                                            <?php if ($c['email']): ?>
                                            <div style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($c['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge <?= $tipoBadge ?>"><?= $tipoLabel ?></span></td>
                                <td style="color:var(--muted)"><?= htmlspecialchars($c['codigo_ans'] ?? '—') ?></td>
                                <td style="color:var(--muted);font-size:.82rem"><?= $cnpjFmt ?></td>
                                <td style="color:var(--muted)"><?= htmlspecialchars($telFmt) ?></td>
                                <td style="color:var(--muted)">
                                    <?= $c['carencia_dias'] > 0 ? $c['carencia_dias'] . ' dias' : '—' ?>
                                </td>
                                <td>
                                    <?php if ($c['ativo']): ?>
                                        <span class="badge badge-green">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Convênios', 'pode_editar')): ?>
                                        <a href="convenio_form.php?id=<?= $c['id'] ?>" class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Convênios', 'pode_excluir')): ?>
                                        <a href="convenio_excluir.php?id=<?= $c['id'] ?>"
                                           class="btn-icon btn-icon-red" title="Excluir"
                                           onclick="return confirm('Excluir o convênio \'<?= htmlspecialchars(addslashes($c['nome'])) ?>\'?')">
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
