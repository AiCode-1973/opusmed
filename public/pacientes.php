<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Pacientes', 'pode_ver');

require_once __DIR__ . '/../app/models/Paciente.php';

$busca  = trim($_GET['busca']  ?? '');
$status = $_GET['status'] ?? '';

$pacienteModel = new Paciente();
$pacientes = $pacienteModel->listarComFiltro($busca, $status);

$msg  = $_GET['msg']  ?? '';
$msgs = [
    'criado'      => 'Paciente cadastrado com sucesso.',
    'editado'     => 'Paciente atualizado com sucesso.',
    'desativado'  => 'Paciente desativado.',
    'ativado'     => 'Paciente reativado.',
];

$badgeStatus = [
    'ativo'       => 'badge-green',
    'inativo'     => 'badge-red',
    'obito'       => 'badge-red',
    'transferido' => 'badge-orange',
];

function calcIdade($nascimento) {
    if (!$nascimento) return '—';
    $d = new DateTime($nascimento);
    $hoje = new DateTime();
    $anos = $hoje->diff($d)->y;
    return $anos . ' anos';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacientes — OpusMed</title>
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
                    <div class="page-title">Pacientes</div>
                    <div class="page-breadcrumb">Cadastro e gestão de pacientes</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Pacientes', 'pode_criar')): ?>
                <a href="paciente_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo paciente
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
                       placeholder="Buscar por nome, CPF, prontuário ou CNS..."
                       style="flex:1;min-width:240px;padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                <select name="status" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                    <option value="">Todos os status</option>
                    <?php foreach (Paciente::$statusList as $k => $l): ?>
                    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Buscar
                </button>
                <?php if ($busca !== '' || $status !== ''): ?>
                <a href="pacientes.php" class="btn btn-ghost">Limpar</a>
                <?php endif; ?>
            </form>

            <div class="card">
                <div class="card-header">
                    <h3>Pacientes cadastrados</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($pacientes) ?> resultado(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Prontuário</th>
                                <th>Paciente</th>
                                <th>CPF</th>
                                <th>Nascimento / Idade</th>
                                <th>Telefone</th>
                                <th>Convênio</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($pacientes)): ?>
                            <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px">Nenhum paciente encontrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pacientes as $p): ?>
                            <?php
                                $cpfFmt = $p['cpf'] ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $p['cpf']) : '—';
                                $nascFmt = $p['data_nascimento'] ? date('d/m/Y', strtotime($p['data_nascimento'])) : '—';
                                $iniciais = mb_strtoupper(mb_substr($p['nome'], 0, 1));
                                $partes = explode(' ', $p['nome']);
                                if (count($partes) > 1) $iniciais .= mb_strtoupper(mb_substr(end($partes), 0, 1));
                                $bage = $badgeStatus[$p['status']] ?? 'badge-orange';
                                $statusLabel = Paciente::$statusList[$p['status']] ?? $p['status'];
                            ?>
                            <tr>
                                <td style="color:var(--muted);font-size:.8rem;font-weight:600"><?= htmlspecialchars($p['prontuario'] ?? '—') ?></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm"><?= $iniciais ?></div>
                                        <div>
                                            <strong><?= htmlspecialchars($p['nome']) ?></strong>
                                            <?php if ($p['nome_social']): ?>
                                            <div style="font-size:.75rem;color:var(--muted)">Social: <?= htmlspecialchars($p['nome_social']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:.82rem;color:var(--muted)"><?= $cpfFmt ?></td>
                                <td style="font-size:.82rem">
                                    <?= $nascFmt ?>
                                    <?php if ($p['data_nascimento']): ?>
                                    <div style="color:var(--muted);font-size:.75rem"><?= calcIdade($p['data_nascimento']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($p['telefone'] ?? '—') ?></td>
                                <td style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($p['convenio_nome'] ?? '—') ?></td>
                                <td><span class="badge <?= $bage ?>"><?= $statusLabel ?></span></td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Pacientes', 'pode_editar')): ?>
                                        <a href="paciente_form.php?id=<?= $p['id'] ?>" class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Pacientes', 'pode_excluir')): ?>
                                        <a href="paciente_excluir.php?id=<?= $p['id'] ?>"
                                           class="btn-icon <?= $p['ativo'] ? 'btn-icon-red' : 'btn-icon-green' ?>"
                                           title="<?= $p['ativo'] ? 'Desativar' : 'Reativar' ?>"
                                           onclick="return confirm('<?= $p['ativo'] ? 'Desativar' : 'Reativar' ?> o paciente \'<?= htmlspecialchars(addslashes($p['nome'])) ?>\'?')">
                                            <?php if ($p['ativo']): ?>
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
