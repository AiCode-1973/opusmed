<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Médicos', 'pode_ver');

require_once __DIR__ . '/../app/models/Medico.php';

$busca  = trim($_GET['busca']  ?? '');
$status = $_GET['status'] ?? '';

$medicoModel = new Medico();
$medicos     = $medicoModel->listarComFiltro($busca, $status);

$msg  = $_GET['msg'] ?? '';
$msgs = [
    'criado'   => 'Médico cadastrado com sucesso.',
    'editado'  => 'Médico atualizado com sucesso.',
    'excluido' => 'Médico excluído com sucesso.',
];

$badgeStatus = [
    'ativo'     => 'badge-green',
    'inativo'   => 'badge-red',
    'ferias'    => 'badge-blue',
    'afastado'  => 'badge-orange',
    'desligado' => 'badge-red',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médicos — OpusMed</title>
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
                    <div class="page-title">Médicos</div>
                    <div class="page-breadcrumb">Cadastro e gestão do corpo clínico</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Médicos', 'pode_criar')): ?>
                <a href="medico_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Novo médico
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
                       placeholder="Buscar por nome, CRM, CPF ou especialidade…"
                       style="flex:1;min-width:240px;padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                <select name="status" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                    <option value="">Todos os status</option>
                    <?php foreach (Medico::$statusList as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-ghost">Filtrar</button>
                <?php if ($busca !== '' || $status !== ''): ?>
                <a href="medicos.php" class="btn btn-ghost">Limpar</a>
                <?php endif; ?>
            </form>

            <!-- Tabela -->
            <div class="card">
                <div class="card-header">
                    <h3>Médicos cadastrados</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($medicos) ?> registro(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>CRM / UF</th>
                                <th>Especialidade</th>
                                <th>Vínculo</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($medicos)): ?>
                            <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:32px">Nenhum médico encontrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($medicos as $m): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm"><?= mb_strtoupper(mb_substr($m['nome'], 0, 2)) ?></div>
                                        <div>
                                            <strong style="font-size:.88rem"><?= htmlspecialchars($m['nome']) ?></strong>
                                            <?php if ($m['email']): ?>
                                            <div style="font-size:.75rem;color:var(--muted)"><?= htmlspecialchars($m['email']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($m['crm']) ?> / <?= htmlspecialchars($m['crm_uf']) ?></td>
                                <td><?= $m['especialidade'] ? htmlspecialchars($m['especialidade']) : '<span style="color:var(--muted)">—</span>' ?></td>
                                <td><?= htmlspecialchars(Medico::$tiposVinculo[$m['tipo_vinculo']] ?? $m['tipo_vinculo']) ?></td>
                                <td><?= $m['setor_nome'] ? htmlspecialchars($m['setor_nome']) : '<span style="color:var(--muted)">—</span>' ?></td>
                                <td>
                                    <span class="badge <?= $badgeStatus[$m['status']] ?? 'badge-blue' ?>">
                                        <?= htmlspecialchars(Medico::$statusList[$m['status']] ?? $m['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Médicos', 'pode_editar')): ?>
                                        <a href="medico_form.php?id=<?= $m['id'] ?>" class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Médicos', 'pode_excluir')): ?>
                                        <button type="button" class="btn-icon btn-icon-red"
                                                title="Excluir"
                                                onclick="confirmarExclusao(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['nome'])) ?>')">
                                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                        </button>
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

<!-- Modal de confirmação de exclusão -->
<div class="modal-backdrop" id="modalExcluir">
    <div class="modal">
        <div class="modal-header">
            <h4>Confirmar exclusão</h4>
            <button class="modal-close" onclick="fecharModal()">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:.9rem">Deseja realmente excluir o médico <strong id="nomeExcluir"></strong>? Esta ação não pode ser desfeita.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="fecharModal()">Cancelar</button>
            <a id="linkExcluir" href="#" class="btn btn-primary" style="background:var(--danger);border-color:var(--danger)">Excluir</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
<script>
function confirmarExclusao(id, nome) {
    document.getElementById('nomeExcluir').textContent = nome;
    document.getElementById('linkExcluir').href = 'medico_excluir.php?id=' + id;
    document.getElementById('modalExcluir').classList.add('open');
}
function fecharModal() {
    document.getElementById('modalExcluir').classList.remove('open');
}
document.getElementById('modalExcluir').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
</body>
</html>
