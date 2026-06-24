<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Pacientes', 'pode_ver');

require_once __DIR__ . '/../app/models/Paciente.php';
require_once __DIR__ . '/../app/models/Convenio.php';

$pacienteModel = new Paciente();
$convenios     = (new Convenio())->listarTodos(true);

$id       = (int) ($_GET['id'] ?? 0);
$editando = $id > 0;
$paciente = null;

if ($editando) {
    $paciente = $pacienteModel->buscarPorId($id);
    if (!$paciente) { header('Location: pacientes.php'); exit; }
}

$erros    = [];
$tabAtiva = 'tab1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    if ($nome === '') {
        $erros[] = 'O nome do paciente é obrigatório.';
        $tabAtiva = 'tab1';
    }
    if (!pode('Pacientes', $editando ? 'pode_editar' : 'pode_criar')) {
        $erros[] = 'Você não tem permissão para esta ação.';
    }

    if (empty($erros)) {
        // Sanitiza campos
        $cpf       = preg_replace('/\D/', '', $_POST['cpf']       ?? '') ?: null;
        $cns       = preg_replace('/\D/', '', $_POST['cns']       ?? '') ?: null;
        $resp_cpf  = preg_replace('/\D/', '', $_POST['resp_cpf']  ?? '') ?: null;
        $cep       = preg_replace('/\D/', '', $_POST['cep']       ?? '') ?: null;
        $tel       = preg_replace('/\D/', '', $_POST['telefone']  ?? '') ?: null;
        $tel2      = preg_replace('/\D/', '', $_POST['telefone2'] ?? '') ?: null;
        $wpp       = preg_replace('/\D/', '', $_POST['whatsapp']  ?? '') ?: null;
        $resp_tel  = preg_replace('/\D/', '', $_POST['resp_telefone'] ?? '') ?: null;

        // LGPD: auto-data de aceite ao marcar consentimento
        $lgpdConsent = isset($_POST['lgpd_consentimento']) ? 1 : 0;
        $lgpdData    = null;
        if ($lgpdConsent) {
            $lgpdData = !empty($_POST['lgpd_data_aceite'])
                ? $_POST['lgpd_data_aceite']
                : date('Y-m-d H:i:s');
        }

        $dados = [
            // Tab 1
            'prontuario'               => trim($_POST['prontuario'] ?? '') ?: null,
            'nome'                     => $nome,
            'nome_social'              => trim($_POST['nome_social']    ?? '') ?: null,
            'data_nascimento'          => $_POST['data_nascimento']    ?: null,
            'sexo_biologico'           => $_POST['sexo_biologico']     ?: null,
            'genero'                   => trim($_POST['genero']         ?? '') ?: null,
            'cpf'                      => $cpf,
            'rg'                       => trim($_POST['rg']             ?? '') ?: null,
            'rg_orgao'                 => trim($_POST['rg_orgao']       ?? '') ?: null,
            'cns'                      => $cns,
            'nome_mae'                 => trim($_POST['nome_mae']       ?? '') ?: null,
            'nome_pai'                 => trim($_POST['nome_pai']       ?? '') ?: null,
            'estado_civil'             => $_POST['estado_civil']       ?: null,
            'nacionalidade'            => trim($_POST['nacionalidade']  ?? '') ?: null,
            'naturalidade'             => trim($_POST['naturalidade']   ?? '') ?: null,
            'foto'                     => trim($_POST['foto']           ?? '') ?: null,
            // Tab 2
            'telefone'                 => $tel,
            'telefone2'                => $tel2,
            'whatsapp'                 => $wpp,
            'email'                    => trim($_POST['email']          ?? '') ?: null,
            'preferencia_contato'      => $_POST['preferencia_contato'] ?? 'telefone',
            'aceite_mensagens'         => isset($_POST['aceite_mensagens']) ? 1 : 0,
            // Tab 3
            'cep'                      => $cep,
            'logradouro'               => trim($_POST['logradouro']     ?? '') ?: null,
            'numero'                   => trim($_POST['numero']         ?? '') ?: null,
            'complemento'              => trim($_POST['complemento']    ?? '') ?: null,
            'bairro'                   => trim($_POST['bairro']         ?? '') ?: null,
            'cidade'                   => trim($_POST['cidade']         ?? '') ?: null,
            'estado_uf'                => trim($_POST['estado_uf']      ?? '') ?: null,
            'referencia'               => trim($_POST['referencia']     ?? '') ?: null,
            // Tab 4
            'resp_nome'                => trim($_POST['resp_nome']          ?? '') ?: null,
            'resp_parentesco'          => trim($_POST['resp_parentesco']    ?? '') ?: null,
            'resp_cpf'                 => $resp_cpf,
            'resp_telefone'            => $resp_tel,
            'resp_email'               => trim($_POST['resp_email']         ?? '') ?: null,
            'resp_observacao'          => trim($_POST['resp_observacao']    ?? '') ?: null,
            // Tab 5
            'alergias'                 => trim($_POST['alergias']                ?? '') ?: null,
            'doencas_preexistentes'    => trim($_POST['doencas_preexistentes']   ?? '') ?: null,
            'medicamentos_continuos'   => trim($_POST['medicamentos_continuos']  ?? '') ?: null,
            'tipo_sanguineo'           => $_POST['tipo_sanguineo']               ?: null,
            'condicoes_especiais'      => trim($_POST['condicoes_especiais']      ?? '') ?: null,
            'deficiencia'              => trim($_POST['deficiencia']             ?? '') ?: null,
            'gestante'                 => isset($_POST['gestante']) ? 1 : 0,
            'restricao_alimentar'      => trim($_POST['restricao_alimentar']     ?? '') ?: null,
            // Tab 6
            'convenio_id'              => (int)($_POST['convenio_id'] ?? 0) ?: null,
            'convenio_carteirinha'     => trim($_POST['convenio_carteirinha']     ?? '') ?: null,
            'convenio_validade'        => $_POST['convenio_validade']             ?: null,
            'convenio_titular'         => trim($_POST['convenio_titular']         ?? '') ?: null,
            'convenio_matricula'       => trim($_POST['convenio_matricula']       ?? '') ?: null,
            'convenio_plano'           => trim($_POST['convenio_plano']           ?? '') ?: null,
            'convenio_cod_beneficiario'=> trim($_POST['convenio_cod_beneficiario']?? '') ?: null,
            // Tab 7
            'status'                   => $_POST['status']          ?? 'ativo',
            'unidade'                  => trim($_POST['unidade']     ?? '') ?: null,
            'origem_cadastro'          => $_POST['origem_cadastro'] ?? 'recepcao',
            'observacoes'              => trim($_POST['observacoes'] ?? '') ?: null,
            'cadastrado_por'           => $editando ? ($paciente['cadastrado_por'] ?? null) : (int)$_SESSION['usuario_id'],
            // Tab 8
            'lgpd_consentimento'       => $lgpdConsent,
            'lgpd_whatsapp'            => isset($_POST['lgpd_whatsapp'])      ? 1 : 0,
            'lgpd_sms'                 => isset($_POST['lgpd_sms'])           ? 1 : 0,
            'lgpd_email_consent'       => isset($_POST['lgpd_email_consent']) ? 1 : 0,
            'lgpd_data_aceite'         => $lgpdData,
            'lgpd_responsavel_aceite'  => trim($_POST['lgpd_responsavel_aceite'] ?? '') ?: null,
            'lgpd_finalidade'          => trim($_POST['lgpd_finalidade']         ?? '') ?: null,
        ];

        try {
            if ($editando) {
                $pacienteModel->atualizar($id, $dados);
                header('Location: pacientes.php?msg=editado');
            } else {
                $pacienteModel->criar($dados);
                header('Location: pacientes.php?msg=criado');
            }
            exit;
        } catch (\Exception $e) {
            $erros[] = 'Erro ao salvar: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Helper: valor para exibição (POST > banco > default)
function fv(string $f, $default = '') {
    global $paciente;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return $_POST[$f] ?? $default;
    return $paciente[$f] ?? $default;
}
function fc(string $f): bool {
    global $paciente;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return isset($_POST[$f]);
    return !empty($paciente[$f]);
}
// CPF formatado para exibição
function fvcpf(string $f): string {
    global $paciente;
    $raw = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') $raw = preg_replace('/\D/', '', $_POST[$f] ?? '');
    else $raw = $paciente[$f] ?? '';
    if (strlen($raw) === 11) return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $raw);
    return $raw;
}
function fvtel(string $f): string {
    global $paciente;
    $raw = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') $raw = preg_replace('/\D/', '', $_POST[$f] ?? '');
    else $raw = preg_replace('/\D/', '', $paciente[$f] ?? '');
    if (strlen($raw) === 11) return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $raw);
    if (strlen($raw) === 10) return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $raw);
    return $raw;
}
function fvcep(string $f): string {
    global $paciente;
    $raw = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') $raw = preg_replace('/\D/', '', $_POST[$f] ?? '');
    else $raw = preg_replace('/\D/', '', $paciente[$f] ?? '');
    if (strlen($raw) === 8) return preg_replace('/^(\d{5})(\d{3})$/', '$1-$2', $raw);
    return $raw;
}

$prontuarioSugerido = $editando
    ? htmlspecialchars($paciente['prontuario'] ?? '')
    : $pacienteModel->proximoProntuario();

$titulo = $editando ? 'Editar Paciente' : 'Novo Paciente';
$ufs    = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
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
                        <a href="pacientes.php" style="color:var(--primary);text-decoration:none">Pacientes</a>
                        &rsaquo; <?= $titulo ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="pacientes.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Voltar
                </a>
                <button type="submit" form="pacienteForm" class="btn btn-primary">
                    <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Salvar paciente
                </button>
            </div>
        </header>

        <main class="page-content">

            <?php if (!empty($erros)): ?>
            <div class="alert alert-danger"><?= implode('<br>', $erros) ?></div>
            <?php endif; ?>

            <form method="POST" id="pacienteForm">

                <!-- ── Navegação por abas ── -->
                <div class="tabs-nav" id="tabsNav">
                    <?php
                    $tabs = [
                        'tab1' => '1 Identificação',
                        'tab2' => '2 Contato',
                        'tab3' => '3 Endereço',
                        'tab4' => '4 Responsável',
                        'tab5' => '5 Assistencial',
                        'tab6' => '6 Convênio',
                        'tab7' => '7 Administrativo',
                        'tab8' => '8 LGPD',
                    ];
                    foreach ($tabs as $tid => $tlabel):
                        list($num, $label) = explode(' ', $tlabel, 2);
                    ?>
                    <button type="button" class="tab-btn <?= $tid === $tabAtiva ? 'active' : '' ?>" data-tab="<?= $tid ?>">
                        <span class="tab-num"><?= $num ?></span>
                        <span class="tab-label"><?= $label ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- ════════════════════════════════════
                     TAB 1 — IDENTIFICAÇÃO
                ════════════════════════════════════ -->
                <div id="tab1" class="tab-pane <?= $tabAtiva === 'tab1' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="prontuario">Prontuário</label>
                                <input type="text" id="prontuario" name="prontuario" maxlength="30"
                                       placeholder="Auto-gerado se vazio"
                                       value="<?= htmlspecialchars(fv('prontuario', $prontuarioSugerido)) ?>">
                            </div>
                            <div class="form-group">
                                <label for="nome">Nome completo <span class="required">*</span></label>
                                <input type="text" id="nome" name="nome" maxlength="200" required
                                       value="<?= htmlspecialchars(fv('nome')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="nome_social">Nome social</label>
                                <input type="text" id="nome_social" name="nome_social" maxlength="200"
                                       value="<?= htmlspecialchars(fv('nome_social')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="data_nascimento">Data de nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento"
                                       value="<?= htmlspecialchars(fv('data_nascimento')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="sexo_biologico">Sexo biológico</label>
                                <select id="sexo_biologico" name="sexo_biologico">
                                    <option value="">Não informado</option>
                                    <?php foreach (Paciente::$sexos as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= fv('sexo_biologico') === $k ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="genero">Gênero</label>
                                <input type="text" id="genero" name="genero" maxlength="60"
                                       placeholder="Ex: Não-binário, Transgênero..."
                                       value="<?= htmlspecialchars(fv('genero')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="cpf">CPF</label>
                                <input type="text" id="cpf" name="cpf" maxlength="14"
                                       placeholder="000.000.000-00"
                                       value="<?= htmlspecialchars(fvcpf('cpf')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="cns">Cartão SUS / CNS</label>
                                <input type="text" id="cns" name="cns" maxlength="18"
                                       placeholder="000 0000 0000 0000"
                                       value="<?= htmlspecialchars(fv('cns')) ?>">
                            </div>
                            <div class="form-group" style="display:flex;gap:10px">
                                <div style="flex:2">
                                    <label for="rg">RG</label>
                                    <input type="text" id="rg" name="rg" maxlength="20"
                                           value="<?= htmlspecialchars(fv('rg')) ?>">
                                </div>
                                <div style="flex:1">
                                    <label for="rg_orgao">Órgão emissor</label>
                                    <input type="text" id="rg_orgao" name="rg_orgao" maxlength="20"
                                           placeholder="SSP/SP"
                                           value="<?= htmlspecialchars(fv('rg_orgao')) ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="estado_civil">Estado civil</label>
                                <select id="estado_civil" name="estado_civil">
                                    <option value="">Não informado</option>
                                    <?php foreach (Paciente::$estadosCivis as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= fv('estado_civil') === $k ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nome_mae">Nome da mãe</label>
                                <input type="text" id="nome_mae" name="nome_mae" maxlength="200"
                                       value="<?= htmlspecialchars(fv('nome_mae')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="nome_pai">Nome do pai</label>
                                <input type="text" id="nome_pai" name="nome_pai" maxlength="200"
                                       value="<?= htmlspecialchars(fv('nome_pai')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="nacionalidade">Nacionalidade</label>
                                <input type="text" id="nacionalidade" name="nacionalidade" maxlength="80"
                                       value="<?= htmlspecialchars(fv('nacionalidade', 'Brasileira')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="naturalidade">Naturalidade</label>
                                <input type="text" id="naturalidade" name="naturalidade" maxlength="100"
                                       placeholder="Cidade — UF"
                                       value="<?= htmlspecialchars(fv('naturalidade')) ?>">
                            </div>

                        </div>
                        <?php include __DIR__ . '/../app/views/_tab_footer.php'; $tabFooter = ['prev'=>null,'next'=>'tab2']; ?>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 2 — CONTATO
                ════════════════════════════════════ -->
                <div id="tab2" class="tab-pane <?= $tabAtiva === 'tab2' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="telefone">Telefone principal</label>
                                <input type="text" id="telefone" name="telefone" maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       value="<?= htmlspecialchars(fvtel('telefone')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="telefone2">Telefone secundário</label>
                                <input type="text" id="telefone2" name="telefone2" maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       value="<?= htmlspecialchars(fvtel('telefone2')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp</label>
                                <input type="text" id="whatsapp" name="whatsapp" maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       value="<?= htmlspecialchars(fvtel('whatsapp')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" maxlength="200"
                                       value="<?= htmlspecialchars(fv('email')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="preferencia_contato">Preferência de contato</label>
                                <select id="preferencia_contato" name="preferencia_contato">
                                    <?php foreach (Paciente::$preferenciasContato as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= fv('preferencia_contato', 'telefone') === $k ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:18px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;font-weight:600">
                                    <input type="checkbox" name="aceite_mensagens" value="1" <?= fc('aceite_mensagens') ? 'checked' : '' ?>
                                           style="width:16px;height:16px;accent-color:var(--primary)">
                                    Aceita receber mensagens
                                </label>
                            </div>

                        </div>
                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab1">&#8592; Anterior</button>
                            <button type="button" class="btn btn-primary btn-tab-nav" data-target="tab3">Próximo &#8594;</button>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 3 — ENDEREÇO
                ════════════════════════════════════ -->
                <div id="tab3" class="tab-pane <?= $tabAtiva === 'tab3' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="cep">CEP</label>
                                <div style="display:flex;gap:8px">
                                    <input type="text" id="cep" name="cep" maxlength="9"
                                           placeholder="00000-000" style="flex:1"
                                           value="<?= htmlspecialchars(fvcep('cep')) ?>">
                                    <button type="button" id="btnBuscarCep" class="btn btn-ghost" style="flex-shrink:0">
                                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="logradouro">Logradouro</label>
                                <input type="text" id="logradouro" name="logradouro" maxlength="200"
                                       value="<?= htmlspecialchars(fv('logradouro')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="numero">Número</label>
                                <input type="text" id="numero" name="numero" maxlength="20"
                                       value="<?= htmlspecialchars(fv('numero')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="complemento">Complemento</label>
                                <input type="text" id="complemento" name="complemento" maxlength="100"
                                       value="<?= htmlspecialchars(fv('complemento')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="bairro">Bairro</label>
                                <input type="text" id="bairro" name="bairro" maxlength="100"
                                       value="<?= htmlspecialchars(fv('bairro')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="cidade">Cidade</label>
                                <input type="text" id="cidade" name="cidade" maxlength="100"
                                       value="<?= htmlspecialchars(fv('cidade')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="estado_uf">Estado</label>
                                <select id="estado_uf" name="estado_uf">
                                    <option value="">Selecione</option>
                                    <?php foreach ($ufs as $uf): ?>
                                    <option value="<?= $uf ?>" <?= fv('estado_uf') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="referencia">Ponto de referência</label>
                                <input type="text" id="referencia" name="referencia" maxlength="200"
                                       value="<?= htmlspecialchars(fv('referencia')) ?>">
                            </div>

                        </div>
                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab2">&#8592; Anterior</button>
                            <button type="button" class="btn btn-primary btn-tab-nav" data-target="tab4">Próximo &#8594;</button>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 4 — RESPONSÁVEL LEGAL
                ════════════════════════════════════ -->
                <div id="tab4" class="tab-pane <?= $tabAtiva === 'tab4' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="resp_nome">Nome do responsável</label>
                                <input type="text" id="resp_nome" name="resp_nome" maxlength="200"
                                       value="<?= htmlspecialchars(fv('resp_nome')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="resp_parentesco">Parentesco</label>
                                <input type="text" id="resp_parentesco" name="resp_parentesco" maxlength="60"
                                       placeholder="Ex: Mãe, Pai, Cônjuge..."
                                       value="<?= htmlspecialchars(fv('resp_parentesco')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="resp_cpf">CPF do responsável</label>
                                <input type="text" id="resp_cpf" name="resp_cpf" maxlength="14"
                                       placeholder="000.000.000-00"
                                       value="<?= htmlspecialchars(fvcpf('resp_cpf')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="resp_telefone">Telefone do responsável</label>
                                <input type="text" id="resp_telefone" name="resp_telefone" maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       value="<?= htmlspecialchars(fvtel('resp_telefone')) ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="resp_email">E-mail do responsável</label>
                                <input type="email" id="resp_email" name="resp_email" maxlength="200"
                                       value="<?= htmlspecialchars(fv('resp_email')) ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="resp_observacao">Observação de autorização</label>
                                <textarea id="resp_observacao" name="resp_observacao" rows="3"
                                          class="form-textarea"><?= htmlspecialchars(fv('resp_observacao')) ?></textarea>
                            </div>

                        </div>
                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab3">&#8592; Anterior</button>
                            <button type="button" class="btn btn-primary btn-tab-nav" data-target="tab5">Próximo &#8594;</button>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 5 — DADOS ASSISTENCIAIS
                ════════════════════════════════════ -->
                <div id="tab5" class="tab-pane <?= $tabAtiva === 'tab5' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group full-width">
                                <label for="alergias">Alergias</label>
                                <textarea id="alergias" name="alergias" rows="3" class="form-textarea"
                                          placeholder="Descreva alergias conhecidas..."><?= htmlspecialchars(fv('alergias')) ?></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="doencas_preexistentes">Doenças preexistentes</label>
                                <textarea id="doencas_preexistentes" name="doencas_preexistentes" rows="3" class="form-textarea"
                                          placeholder="Diabetes, hipertensão, etc."><?= htmlspecialchars(fv('doencas_preexistentes')) ?></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="medicamentos_continuos">Uso contínuo de medicamentos</label>
                                <textarea id="medicamentos_continuos" name="medicamentos_continuos" rows="3" class="form-textarea"
                                          placeholder="Liste os medicamentos de uso contínuo..."><?= htmlspecialchars(fv('medicamentos_continuos')) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="tipo_sanguineo">Tipo sanguíneo</label>
                                <select id="tipo_sanguineo" name="tipo_sanguineo">
                                    <option value="">Não informado</option>
                                    <?php foreach (Paciente::$tiposSanguineos as $ts): ?>
                                    <option value="<?= $ts ?>" <?= fv('tipo_sanguineo') === $ts ? 'selected' : '' ?>><?= $ts ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="deficiencia">Deficiência / necessidade de acessibilidade</label>
                                <input type="text" id="deficiencia" name="deficiencia" maxlength="200"
                                       placeholder="Ex: Cadeirante, deficiência visual..."
                                       value="<?= htmlspecialchars(fv('deficiencia')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="restricao_alimentar">Restrição alimentar</label>
                                <input type="text" id="restricao_alimentar" name="restricao_alimentar" maxlength="200"
                                       placeholder="Ex: Vegetariano, intolerância à lactose..."
                                       value="<?= htmlspecialchars(fv('restricao_alimentar')) ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="condicoes_especiais">Condições especiais</label>
                                <textarea id="condicoes_especiais" name="condicoes_especiais" rows="3" class="form-textarea"
                                          placeholder="Outras condições relevantes..."><?= htmlspecialchars(fv('condicoes_especiais')) ?></textarea>
                            </div>
                            <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:18px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.88rem;font-weight:600">
                                    <input type="checkbox" name="gestante" value="1" <?= fc('gestante') ? 'checked' : '' ?>
                                           id="checkGestante"
                                           style="width:16px;height:16px;accent-color:var(--primary)">
                                    Gestante
                                </label>
                            </div>

                        </div>
                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab4">&#8592; Anterior</button>
                            <button type="button" class="btn btn-primary btn-tab-nav" data-target="tab6">Próximo &#8594;</button>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 6 — CONVÊNIO
                ════════════════════════════════════ -->
                <div id="tab6" class="tab-pane <?= $tabAtiva === 'tab6' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group full-width">
                                <label for="convenio_id">Convênio</label>
                                <select id="convenio_id" name="convenio_id">
                                    <option value="">Nenhum / Particular</option>
                                    <?php foreach ($convenios as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (int)fv('convenio_id', 0) === $c['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="convenio_carteirinha">Número da carteirinha</label>
                                <input type="text" id="convenio_carteirinha" name="convenio_carteirinha" maxlength="60"
                                       value="<?= htmlspecialchars(fv('convenio_carteirinha')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="convenio_validade">Validade da carteirinha</label>
                                <input type="date" id="convenio_validade" name="convenio_validade"
                                       value="<?= htmlspecialchars(fv('convenio_validade')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="convenio_titular">Titular do plano</label>
                                <input type="text" id="convenio_titular" name="convenio_titular" maxlength="200"
                                       value="<?= htmlspecialchars(fv('convenio_titular')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="convenio_matricula">Matrícula</label>
                                <input type="text" id="convenio_matricula" name="convenio_matricula" maxlength="60"
                                       value="<?= htmlspecialchars(fv('convenio_matricula')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="convenio_plano">Plano / categoria</label>
                                <input type="text" id="convenio_plano" name="convenio_plano" maxlength="100"
                                       placeholder="Ex: Enfermaria, Apartamento..."
                                       value="<?= htmlspecialchars(fv('convenio_plano')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="convenio_cod_beneficiario">Cód. do beneficiário</label>
                                <input type="text" id="convenio_cod_beneficiario" name="convenio_cod_beneficiario" maxlength="60"
                                       value="<?= htmlspecialchars(fv('convenio_cod_beneficiario')) ?>">
                            </div>

                        </div>
                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab5">&#8592; Anterior</button>
                            <button type="button" class="btn btn-primary btn-tab-nav" data-target="tab7">Próximo &#8594;</button>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 7 — ADMINISTRATIVO
                ════════════════════════════════════ -->
                <div id="tab7" class="tab-pane <?= $tabAtiva === 'tab7' ? 'active' : '' ?>">
                    <div class="tab-content">
                        <div class="form-grid-2">

                            <div class="form-group">
                                <label for="status">Status do cadastro</label>
                                <select id="status" name="status">
                                    <?php foreach (Paciente::$statusList as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= fv('status', 'ativo') === $k ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="origem_cadastro">Origem do cadastro</label>
                                <select id="origem_cadastro" name="origem_cadastro">
                                    <?php foreach (Paciente::$origenscadastro as $k => $l): ?>
                                    <option value="<?= $k ?>" <?= fv('origem_cadastro', 'recepcao') === $k ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="unidade">Unidade / filial</label>
                                <input type="text" id="unidade" name="unidade" maxlength="100"
                                       value="<?= htmlspecialchars(fv('unidade')) ?>">
                            </div>
                            <?php if ($editando): ?>
                            <div class="form-group">
                                <label>Data de cadastro</label>
                                <input type="text" disabled
                                       value="<?= $paciente['created_at'] ? date('d/m/Y H:i', strtotime($paciente['created_at'])) : '—' ?>"
                                       style="background:#f0f4f8;color:var(--muted)">
                            </div>
                            <?php endif; ?>
                            <?php if ($editando && !empty($paciente['cadastrado_por_nome'])): ?>
                            <div class="form-group">
                                <label>Cadastrado por</label>
                                <input type="text" disabled
                                       value="<?= htmlspecialchars($paciente['cadastrado_por_nome']) ?>"
                                       style="background:#f0f4f8;color:var(--muted)">
                            </div>
                            <?php endif; ?>
                            <div class="form-group full-width">
                                <label for="observacoes">Observações gerais</label>
                                <textarea id="observacoes" name="observacoes" rows="4" class="form-textarea"><?= htmlspecialchars(fv('observacoes')) ?></textarea>
                            </div>

                        </div>
                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab6">&#8592; Anterior</button>
                            <button type="button" class="btn btn-primary btn-tab-nav" data-target="tab8">Próximo &#8594;</button>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════
                     TAB 8 — LGPD / CONSENTIMENTO
                ════════════════════════════════════ -->
                <div id="tab8" class="tab-pane <?= $tabAtiva === 'tab8' ? 'active' : '' ?>">
                    <div class="tab-content">

                        <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:14px 18px;margin-bottom:24px;font-size:.88rem;color:#1e40af;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
                            <div>
                                <strong>Lei Geral de Proteção de Dados (LGPD)</strong> — As informações coletadas são utilizadas exclusivamente para a assistência à saúde do paciente, em conformidade com a Lei nº 13.709/2018.
                            </div>
                            <?php if ($editando): ?>
                            <a href="paciente_lgpd_termo.php?id=<?= $id ?>" target="_blank"
                               style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#1a6fb5;color:#fff;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;white-space:nowrap;flex-shrink:0">
                                <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                Imprimir Termo LGPD
                            </a>
                            <?php endif; ?>
                        </div>

                        <div class="lgpd-checks">
                            <label class="lgpd-item">
                                <input type="checkbox" name="lgpd_consentimento" id="lgpdConsent" value="1" <?= fc('lgpd_consentimento') ? 'checked' : '' ?>>
                                <div>
                                    <strong>Consentimento para tratamento de dados</strong>
                                    <span>Autorizo o tratamento dos meus dados pessoais e de saúde para fins assistenciais.</span>
                                </div>
                            </label>
                            <label class="lgpd-item">
                                <input type="checkbox" name="lgpd_whatsapp" value="1" <?= fc('lgpd_whatsapp') ? 'checked' : '' ?>>
                                <div>
                                    <strong>Contato via WhatsApp</strong>
                                    <span>Autorizo o envio de lembretes e comunicações via WhatsApp.</span>
                                </div>
                            </label>
                            <label class="lgpd-item">
                                <input type="checkbox" name="lgpd_sms" value="1" <?= fc('lgpd_sms') ? 'checked' : '' ?>>
                                <div>
                                    <strong>Contato via SMS</strong>
                                    <span>Autorizo o envio de mensagens SMS.</span>
                                </div>
                            </label>
                            <label class="lgpd-item">
                                <input type="checkbox" name="lgpd_email_consent" value="1" <?= fc('lgpd_email_consent') ? 'checked' : '' ?>>
                                <div>
                                    <strong>Contato via e-mail</strong>
                                    <span>Autorizo o envio de comunicações por e-mail.</span>
                                </div>
                            </label>
                        </div>

                        <div class="form-grid-2" style="margin-top:20px">
                            <div class="form-group">
                                <label for="lgpd_data_aceite">Data e hora do aceite</label>
                                <input type="datetime-local" id="lgpd_data_aceite" name="lgpd_data_aceite"
                                       value="<?= htmlspecialchars(str_replace(' ', 'T', fv('lgpd_data_aceite'))) ?>">
                            </div>
                            <div class="form-group">
                                <label for="lgpd_responsavel_aceite">Responsável pelo aceite</label>
                                <input type="text" id="lgpd_responsavel_aceite" name="lgpd_responsavel_aceite" maxlength="200"
                                       value="<?= htmlspecialchars(fv('lgpd_responsavel_aceite')) ?>">
                            </div>
                            <div class="form-group full-width">
                                <label for="lgpd_finalidade">Finalidade do uso dos dados</label>
                                <textarea id="lgpd_finalidade" name="lgpd_finalidade" rows="3" class="form-textarea"
                                          placeholder="Ex: Identificação do paciente, agendamento de consultas, envio de resultados..."><?= htmlspecialchars(fv('lgpd_finalidade')) ?></textarea>
                            </div>
                        </div>

                        <div class="tab-footer">
                            <button type="button" class="btn btn-ghost btn-tab-nav" data-target="tab7">&#8592; Anterior</button>
                            <button type="submit" class="btn btn-primary">
                                <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Salvar paciente
                            </button>
                        </div>

                    </div>
                </div>

            </form>

        </main>
    </div>
</div>

<script>
// ── Tab navigation ───────────────────────────────────────────
function activateTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.tab === tabId);
    });
    document.querySelectorAll('.tab-pane').forEach(p => {
        p.classList.toggle('active', p.id === tabId);
    });
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => activateTab(btn.dataset.tab));
});

document.querySelectorAll('.btn-tab-nav').forEach(btn => {
    btn.addEventListener('click', () => activateTab(btn.dataset.target));
});

// Restore tab on POST error
activateTab('<?= $tabAtiva ?>');

// ── Máscara CPF ──────────────────────────────────────────────
function maskCpf(el) {
    el.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 11);
        if (v.length > 9)      v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
        else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
        else if (v.length > 3) v = v.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
        this.value = v;
    });
}
['cpf','resp_cpf'].forEach(id => { const el = document.getElementById(id); if (el) maskCpf(el); });

// ── Máscara telefone ─────────────────────────────────────────
function maskTel(el) {
    el.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 11);
        if (v.length > 10)     v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4})/, '($1) $2-$3');
        else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        this.value = v;
    });
}
['telefone','telefone2','whatsapp','resp_telefone'].forEach(id => {
    const el = document.getElementById(id); if (el) maskTel(el);
});

// ── Máscara CEP + busca automática ──────────────────────────
const cepIconSvg = '<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';

function buscarCep(cep) {
    const btn = document.getElementById('btnBuscarCep');
    btn.innerHTML = '<span style="font-size:.8rem">...</span>';
    btn.disabled = true;
    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(r => r.json())
        .then(d => {
            if (d.erro) { alert('CEP não encontrado.'); return; }
            document.getElementById('logradouro').value = d.logradouro || '';
            document.getElementById('bairro').value     = d.bairro    || '';
            document.getElementById('cidade').value     = d.localidade || '';
            const ufSel = document.getElementById('estado_uf');
            for (let i = 0; i < ufSel.options.length; i++) {
                if (ufSel.options[i].value === d.uf) { ufSel.selectedIndex = i; break; }
            }
            document.getElementById('numero').focus();
        })
        .catch(() => alert('Erro ao buscar CEP. Verifique sua conexão.'))
        .finally(() => {
            btn.innerHTML = cepIconSvg;
            btn.disabled = false;
        });
}

document.getElementById('cep').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 8);
    if (v.length > 5) v = v.replace(/^(\d{5})(\d{0,3})/, '$1-$2');
    this.value = v;
    if (v.replace(/\D/g, '').length === 8) buscarCep(v.replace(/\D/g, ''));
});

document.getElementById('btnBuscarCep').addEventListener('click', function () {
    const cep = document.getElementById('cep').value.replace(/\D/g, '');
    if (cep.length !== 8) { alert('CEP inválido. Digite 8 dígitos.'); return; }
    buscarCep(cep);
});

// ── LGPD: auto-preenche data/hora ao marcar consentimento ────
document.getElementById('lgpdConsent').addEventListener('change', function () {
    const dtField = document.getElementById('lgpd_data_aceite');
    if (this.checked && !dtField.value) {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        dtField.value = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate())
                      + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
    }
});

// ── Gestante: só exibe se sexo biológico = F ─────────────────
function toggleGestante() {
    const sexo = document.getElementById('sexo_biologico').value;
    const wrap = document.getElementById('checkGestante').closest('.form-group');
    wrap.style.display = (sexo === 'F' || sexo === '') ? '' : 'none';
}
document.getElementById('sexo_biologico').addEventListener('change', toggleGestante);
toggleGestante();
</script>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
