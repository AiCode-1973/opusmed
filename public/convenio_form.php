<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Convênios', 'pode_ver');

require_once __DIR__ . '/../app/models/Convenio.php';

$convenioModel = new Convenio();

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$convenio = null;

if ($editando) {
    $convenio = $convenioModel->buscarPorId($id);
    if (!$convenio) {
        header('Location: convenios.php');
        exit;
    }
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome         = trim($_POST['nome']         ?? '');
    $tipo         = $_POST['tipo']              ?? 'plano_saude';
    $codigo_ans   = trim($_POST['codigo_ans']   ?? '');
    $cnpj         = preg_replace('/\D/', '', $_POST['cnpj'] ?? '');
    $telefone     = trim($_POST['telefone']     ?? '');
    $email        = trim($_POST['email']        ?? '');
    $site         = trim($_POST['site']         ?? '');
    $endereco     = trim($_POST['endereco']     ?? '');
    $carencia     = (int) ($_POST['carencia_dias'] ?? 0);
    $observacoes  = trim($_POST['observacoes']  ?? '');
    $ativo        = isset($_POST['ativo']) ? 1 : 0;

    if ($nome === '') {
        $erro = 'O nome do convênio é obrigatório.';
    } elseif (!in_array($tipo, array_keys(Convenio::$tipos), true)) {
        $erro = 'Tipo inválido.';
    } elseif (!pode('Convênios', $editando ? 'pode_editar' : 'pode_criar')) {
        $erro = 'Você não tem permissão para esta ação.';
    } else {
        try {
            $dados = [
                'nome'          => $nome,
                'tipo'          => $tipo,
                'codigo_ans'    => $codigo_ans   ?: null,
                'cnpj'          => $cnpj         ?: null,
                'telefone'      => $telefone     ?: null,
                'email'         => $email        ?: null,
                'site'          => $site         ?: null,
                'endereco'      => $endereco     ?: null,
                'carencia_dias' => $carencia,
                'observacoes'   => $observacoes  ?: null,
                'ativo'         => $ativo,
            ];

            if ($editando) {
                $convenioModel->atualizar($id, $dados);
                header('Location: convenios.php?msg=editado');
            } else {
                $convenioModel->criar($dados);
                header('Location: convenios.php?msg=criado');
            }
            exit;
        } catch (\Exception $e) {
            $erro = 'Erro ao salvar: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Valores para o formulário (POST tem prioridade sobre banco)
$v = [];
$campos = ['nome','tipo','codigo_ans','cnpj','telefone','email','site','endereco','carencia_dias','observacoes','ativo'];
foreach ($campos as $c) {
    $v[$c] = $_POST[$c] ?? $convenio[$c] ?? '';
}
// Formata CNPJ para exibição
if ($v['cnpj'] && strlen(preg_replace('/\D/','',$v['cnpj'])) === 14) {
    $raw = preg_replace('/\D/','',$v['cnpj']);
    $v['cnpj'] = preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $raw);
}
$ativo = isset($_POST['ativo']) ? (bool)$_POST['ativo'] : (bool)($convenio['ativo'] ?? true);

$titulo = $editando ? 'Editar Convênio' : 'Novo Convênio';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> — OpusMed</title>
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
                    <div class="page-title"><?= $titulo ?></div>
                    <div class="page-breadcrumb">
                        <a href="convenios.php" style="color:var(--primary);text-decoration:none">Convênios</a>
                        &rsaquo; <?= $titulo ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="convenios.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Voltar
                </a>
            </div>
        </header>

        <main class="page-content">

            <?php if ($erro !== ''): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
            <?php endif; ?>

            <form method="POST" id="convenioForm">

                <!-- ── Identificação ── -->
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h3>Identificação</h3></div>
                    <div class="card-body" style="padding:24px">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="nome">Nome <span class="required">*</span></label>
                                <input type="text" id="nome" name="nome" maxlength="150" required
                                       value="<?= htmlspecialchars($v['nome']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="tipo">Tipo <span class="required">*</span></label>
                                <select id="tipo" name="tipo" required>
                                    <?php foreach (Convenio::$tipos as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($v['tipo'] === $key) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="codigo_ans">Código ANS</label>
                                <input type="text" id="codigo_ans" name="codigo_ans" maxlength="20"
                                       placeholder="000000"
                                       value="<?= htmlspecialchars($v['codigo_ans']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="cnpj">CNPJ</label>
                                <input type="text" id="cnpj" name="cnpj" maxlength="18"
                                       placeholder="00.000.000/0000-00"
                                       value="<?= htmlspecialchars($v['cnpj']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="carencia_dias">Carência (dias)</label>
                                <input type="number" id="carencia_dias" name="carencia_dias"
                                       min="0" max="9999" placeholder="0"
                                       value="<?= (int) $v['carencia_dias'] ?>">
                            </div>

                            <?php if ($editando): ?>
                            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:18px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;font-weight:600">
                                    <input type="checkbox" name="ativo" value="1"
                                           <?= $ativo ? 'checked' : '' ?>
                                           style="width:16px;height:16px;accent-color:var(--primary)">
                                    Convênio ativo
                                </label>
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- ── Contato ── -->
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h3>Contato</h3></div>
                    <div class="card-body" style="padding:24px">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" maxlength="20"
                                       placeholder="(00) 0000-0000"
                                       value="<?= htmlspecialchars($v['telefone']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" maxlength="200"
                                       placeholder="contato@convenio.com.br"
                                       value="<?= htmlspecialchars($v['email']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="site">Site</label>
                                <input type="text" id="site" name="site" maxlength="255"
                                       placeholder="https://www.convenio.com.br"
                                       value="<?= htmlspecialchars($v['site']) ?>">
                            </div>

                            <div class="form-group">
                                <label for="endereco">Endereço</label>
                                <input type="text" id="endereco" name="endereco" maxlength="255"
                                       placeholder="Rua, número, bairro, cidade — UF"
                                       value="<?= htmlspecialchars($v['endereco']) ?>">
                            </div>

                        </div>
                    </div>
                </div>

                <!-- ── Observações ── -->
                <div class="card" style="margin-bottom:24px">
                    <div class="card-header"><h3>Observações</h3></div>
                    <div class="card-body" style="padding:24px">
                        <div class="form-group" style="margin-bottom:0">
                            <textarea id="observacoes" name="observacoes" rows="4"
                                      placeholder="Informações adicionais sobre o convênio..."
                                      style="width:100%;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:.92rem;color:var(--text);background:#f8fafc;outline:none;resize:vertical;font-family:inherit;transition:border-color .2s,box-shadow .2s,background .2s"
                                      onfocus="this.style.borderColor='var(--primary)';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(26,111,181,.1)'"
                                      onblur="this.style.borderColor='';this.style.background='';this.style.boxShadow=''"><?= htmlspecialchars($v['observacoes']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ── Ações ── -->
                <div style="display:flex;gap:12px">
                    <button type="submit" class="btn btn-primary">
                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Salvar convênio
                    </button>
                    <a href="convenios.php" class="btn btn-ghost">Cancelar</a>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
// Máscara CNPJ: 00.000.000/0000-00
document.getElementById('cnpj').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 14);
    if (v.length > 12)      v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, '$1.$2.$3/$4-$5');
    else if (v.length > 8)  v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4})/, '$1.$2.$3/$4');
    else if (v.length > 5)  v = v.replace(/^(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
    else if (v.length > 2)  v = v.replace(/^(\d{2})(\d{0,3})/, '$1.$2');
    this.value = v;
});

// Máscara telefone: (00) 00000-0000
document.getElementById('telefone').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 11);
    if (v.length > 10)     v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4})/, '($1) $2-$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
    this.value = v;
});
</script>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
