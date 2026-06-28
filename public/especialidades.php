<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Especialidades', 'pode_ver');

require_once __DIR__ . '/../app/models/Especialidade.php';

$busca = trim($_GET['busca'] ?? '');
$ativo = $_GET['ativo'] ?? '';

$model         = new Especialidade();
$especialidades = $model->listarComFiltro($busca, $ativo);

$msg  = $_GET['msg'] ?? '';
$msgs = [
    'criado'   => 'Especialidade criada com sucesso.',
    'editado'  => 'Especialidade atualizada com sucesso.',
    'excluido' => 'Especialidade excluída com sucesso.',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Especialidades — OpusMed</title>
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
                    <div class="page-title">Especialidades Médicas</div>
                    <div class="page-breadcrumb">Gerenciamento de especialidades do corpo clínico</div>
                </div>
            </div>
            <div class="topbar-right">
                <?php if (pode('Especialidades', 'pode_criar')): ?>
                <a href="especialidade_form.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nova especialidade
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="page-content">

            <?php if ($msg !== '' && isset($msgs[$msg])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msgs[$msg]) ?></div>
            <?php endif; ?>

            <!-- Filtros -->
            <form method="GET" style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
                <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>"
                       placeholder="Buscar por nome ou código CBO-S…"
                       style="flex:1;min-width:240px;padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                <select name="ativo" style="padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:.88rem;background:#fff;outline:none">
                    <option value="">Todos</option>
                    <option value="1" <?= $ativo === '1' ? 'selected' : '' ?>>Ativos</option>
                    <option value="0" <?= $ativo === '0' ? 'selected' : '' ?>>Inativos</option>
                </select>
                <button type="submit" class="btn btn-ghost">Filtrar</button>
                <?php if ($busca !== '' || $ativo !== ''): ?>
                <a href="especialidades.php" class="btn btn-ghost">Limpar</a>
                <?php endif; ?>
            </form>

            <!-- Tabela -->
            <div class="card">
                <div class="card-header">
                    <h3>Especialidades cadastradas</h3>
                    <span style="font-size:.82rem;color:var(--muted)"><?= count($especialidades) ?> registro(s)</span>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Código CBO-S</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($especialidades)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;color:var(--muted);padding:36px">
                                    Nenhuma especialidade encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($especialidades as $e): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm" style="background:var(--primary-dark);border-radius:8px">
                                            <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round">
                                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                            </svg>
                                        </div>
                                        <strong style="font-size:.88rem"><?= htmlspecialchars($e['nome']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($e['codigo_cbos']): ?>
                                    <span class="badge badge-blue"><?= htmlspecialchars($e['codigo_cbos']) ?></span>
                                    <?php else: ?>
                                    <span style="color:var(--muted)">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width:300px;color:var(--muted);font-size:.82rem">
                                    <?php if ($e['descricao']): ?>
                                    <span title="<?= htmlspecialchars($e['descricao']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($e['descricao'], 0, 80, '…')) ?>
                                    </span>
                                    <?php else: ?>
                                    <span>—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $e['ativo'] ? 'badge-green' : 'badge-red' ?>">
                                        <?= $e['ativo'] ? 'Ativa' : 'Inativa' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if (pode('Especialidades', 'pode_editar')): ?>
                                        <a href="especialidade_form.php?id=<?= $e['id'] ?>"
                                           class="btn-icon btn-icon-blue" title="Editar">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (pode('Especialidades', 'pode_excluir')): ?>
                                        <button type="button" class="btn-icon btn-icon-red" title="Excluir"
                                                onclick="confirmarExclusao(<?= $e['id'] ?>, '<?= htmlspecialchars(addslashes($e['nome'])) ?>')">
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

<!-- Modal de confirmação -->
<div class="modal-backdrop" id="modalExcluir">
    <div class="modal">
        <div class="modal-header">
            <h4>Confirmar exclusão</h4>
            <button class="modal-close" onclick="fecharModal()">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:.9rem">Deseja realmente excluir a especialidade <strong id="nomeExcluir"></strong>?</p>
            <p style="font-size:.82rem;color:var(--muted);margin-top:8px">Esta ação não pode ser desfeita. Médicos vinculados a ela não serão afetados.</p>
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
    document.getElementById('linkExcluir').href = 'especialidade_excluir.php?id=' + id;
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
