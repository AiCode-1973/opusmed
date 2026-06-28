<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Médicos', 'pode_ver');

require_once __DIR__ . '/../app/models/Medico.php';
require_once __DIR__ . '/../app/models/Setor.php';

$medicoModel = new Medico();
$setores     = (new Setor())->listarTodos(true);

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$medico   = null;

if ($editando) {
    $medico = $medicoModel->buscarPorId($id);
    if (!$medico) { header('Location: medicos.php'); exit; }
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $crm  = trim($_POST['crm']  ?? '');
    $uf   = $_POST['crm_uf'] ?? '';

    if ($nome === '') {
        $erro = 'O nome completo é obrigatório.';
    } elseif ($crm === '') {
        $erro = 'O CRM é obrigatório.';
    } elseif (!in_array($uf, Medico::$ufs, true)) {
        $erro = 'UF do CRM inválida.';
    } elseif (!in_array($_POST['tipo_vinculo'] ?? '', array_keys(Medico::$tiposVinculo), true)) {
        $erro = 'Tipo de vínculo inválido.';
    } elseif (!in_array($_POST['status'] ?? '', array_keys(Medico::$statusList), true)) {
        $erro = 'Status inválido.';
    } elseif (!pode('Médicos', $editando ? 'pode_editar' : 'pode_criar')) {
        $erro = 'Você não tem permissão para esta ação.';
    }

    if ($erro === '') {
        try {
            $cpf      = preg_replace('/\D/', '', $_POST['cpf']      ?? '') ?: null;
            $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '') ?: null;
            $setorId  = (int) ($_POST['setor_id'] ?? 0);

            $dados = [
                'nome'          => $nome,
                'cpf'           => $cpf,
                'crm'           => $crm,
                'crm_uf'        => $uf,
                'especialidade' => trim($_POST['especialidade'] ?? '') ?: null,
                'rqe'           => trim($_POST['rqe']           ?? '') ?: null,
                'email'         => trim($_POST['email']         ?? '') ?: null,
                'telefone'      => $telefone,
                'tipo_vinculo'  => $_POST['tipo_vinculo'],
                'setor_id'      => $setorId,
                'status'        => $_POST['status'],
                'ativo'         => 1,
            ];

            if ($editando) {
                $medicoModel->atualizar($id, $dados);
                header('Location: medicos.php?msg=editado');
            } else {
                $medicoModel->criar($dados);
                header('Location: medicos.php?msg=criado');
            }
            exit;
        } catch (\Exception $e) {
            $erro = 'Erro ao salvar: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Valores do formulário (POST tem prioridade sobre banco)
$campos = ['nome','cpf','crm','crm_uf','especialidade','rqe','email','telefone','tipo_vinculo','setor_id','status'];
$v = [];
foreach ($campos as $c) {
    $v[$c] = $_POST[$c] ?? $medico[$c] ?? '';
}
// Formata CPF para exibição
if ($v['cpf'] && strlen(preg_replace('/\D/', '', $v['cpf'])) === 11) {
    $raw = preg_replace('/\D/', '', $v['cpf']);
    $v['cpf'] = substr($raw,0,3).'.'.substr($raw,3,3).'.'.substr($raw,6,3).'-'.substr($raw,9,2);
}
// Formata telefone
if ($v['telefone'] && strlen(preg_replace('/\D/', '', $v['telefone'])) >= 10) {
    $raw = preg_replace('/\D/', '', $v['telefone']);
    $v['telefone'] = strlen($raw) === 11
        ? '(' . substr($raw,0,2) . ') ' . substr($raw,2,5) . '-' . substr($raw,7)
        : '(' . substr($raw,0,2) . ') ' . substr($raw,2,4) . '-' . substr($raw,6);
}
// Defaults
if ($v['tipo_vinculo'] === '') $v['tipo_vinculo'] = 'autonomo';
if ($v['status']       === '') $v['status']       = 'ativo';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar médico' : 'Novo médico' ?> — OpusMed</title>
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
                    <div class="page-title"><?= $editando ? 'Editar médico' : 'Novo médico' ?></div>
                    <div class="page-breadcrumb">
                        <a href="medicos.php" style="color:var(--primary);text-decoration:none">Médicos</a>
                        &rsaquo; <?= $editando ? htmlspecialchars($medico['nome']) : 'Novo' ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="medicos.php" class="btn btn-ghost">Cancelar</a>
            </div>
        </header>

        <main class="page-content">

            <?php if ($erro !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" novalidate>

                <div class="card" style="margin-bottom:20px">
                    <div class="card-header"><h3>Dados pessoais e registro</h3></div>
                    <div class="tab-content" style="border-top:none">
                        <div class="form-grid-2">

                            <div class="form-group full-width">
                                <label>Nome completo <span class="required">*</span></label>
                                <input type="text" name="nome" value="<?= htmlspecialchars($v['nome']) ?>" required maxlength="150">
                            </div>

                            <div class="form-group">
                                <label>CPF</label>
                                <input type="text" name="cpf" id="cpf"
                                       value="<?= htmlspecialchars($v['cpf']) ?>"
                                       placeholder="000.000.000-00" maxlength="14">
                            </div>

                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($v['email']) ?>" maxlength="150">
                            </div>

                            <div class="form-group">
                                <label>Telefone</label>
                                <input type="text" name="telefone" id="telefone"
                                       value="<?= htmlspecialchars($v['telefone']) ?>"
                                       placeholder="(00) 00000-0000" maxlength="20">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom:20px">
                    <div class="card-header"><h3>Registro profissional</h3></div>
                    <div class="tab-content" style="border-top:none">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label>CRM <span class="required">*</span></label>
                                <input type="text" name="crm" value="<?= htmlspecialchars($v['crm']) ?>"
                                       placeholder="Ex.: 123456" required maxlength="20">
                            </div>

                            <div class="form-group">
                                <label>UF do CRM <span class="required">*</span></label>
                                <select name="crm_uf" required>
                                    <option value="">Selecione…</option>
                                    <?php foreach (Medico::$ufs as $uf): ?>
                                    <option value="<?= $uf ?>" <?= $v['crm_uf'] === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Especialidade</label>
                                <input type="text" name="especialidade" value="<?= htmlspecialchars($v['especialidade']) ?>" maxlength="100"
                                       placeholder="Ex.: Cardiologia">
                            </div>

                            <div class="form-group">
                                <label>RQE
                                    <span class="form-hint" style="display:inline;margin-left:4px">Registro de Qualificação de Especialista</span>
                                </label>
                                <input type="text" name="rqe" value="<?= htmlspecialchars($v['rqe']) ?>" maxlength="30"
                                       placeholder="Ex.: 12345">
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h3>Vínculo e alocação</h3></div>
                    <div class="tab-content" style="border-top:none">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label>Tipo de vínculo <span class="required">*</span></label>
                                <select name="tipo_vinculo" required>
                                    <?php foreach (Medico::$tiposVinculo as $k => $label): ?>
                                    <option value="<?= $k ?>" <?= $v['tipo_vinculo'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Setor / Unidade</label>
                                <select name="setor_id">
                                    <option value="0">— Nenhum —</option>
                                    <?php foreach ($setores as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (int)$v['setor_id'] === (int)$s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Status <span class="required">*</span></label>
                                <select name="status" required>
                                    <?php foreach (Medico::$statusList as $k => $label): ?>
                                    <option value="<?= $k ?>" <?= $v['status'] === $k ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <?= $editando ? 'Salvar alterações' : 'Cadastrar médico' ?>
                    </button>
                    <a href="medicos.php" class="btn btn-ghost">Cancelar</a>
                </div>

            </form>
        </main>
    </div>
</div>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
<script>
// Máscara CPF
document.getElementById('cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 9)      v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{1,2})$/, '$1.$2.$3-$4');
    else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{1,3})$/,        '$1.$2.$3');
    else if (v.length > 3) v = v.replace(/^(\d{3})(\d{1,3})$/,                '$1.$2');
    this.value = v;
});

// Máscara telefone
document.getElementById('telefone').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').slice(0, 11);
    if (v.length > 10)     v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4})$/, '($1) $2-$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
    this.value = v;
});
</script>
</body>
</html>
