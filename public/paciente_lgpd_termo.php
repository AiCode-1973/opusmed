<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Pacientes', 'pode_ver');

require_once __DIR__ . '/../app/models/Paciente.php';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header('Location: pacientes.php'); exit; }

$pacienteModel = new Paciente();
$p = $pacienteModel->buscarPorId($id);
if (!$p) { header('Location: pacientes.php'); exit; }

// Formata CPF
$cpfFmt = '';
if ($p['cpf'] && strlen($p['cpf']) === 11) {
    $cpfFmt = preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $p['cpf']);
}

// Formata nascimento e idade
$nascFmt = '';
$idade   = '';
if ($p['data_nascimento']) {
    $nascFmt = date('d/m/Y', strtotime($p['data_nascimento']));
    $anos    = (new DateTime())->diff(new DateTime($p['data_nascimento']))->y;
    $idade   = $anos . ' anos';
}

// RG
$rgFmt = trim(($p['rg'] ?? '') . ($p['rg_orgao'] ? ' / ' . $p['rg_orgao'] : ''));

// Data do termo
$dataHoje = date('d') . ' de ' . strftime_pt(date('n')) . ' de ' . date('Y');

function strftime_pt(int $mes): string {
    return ['janeiro','fevereiro','março','abril','maio','junho',
            'julho','agosto','setembro','outubro','novembro','dezembro'][$mes - 1];
}

// Responsável (se menor ou incapaz)
$temResponsavel = !empty($p['resp_nome']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo LGPD — <?= htmlspecialchars($p['nome']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #111;
            background: #f0f4f8;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            padding: 12mm 14mm 10mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
        }

        /* Cabeçalho */
        .header {
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid #1a6fb5;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .header-logo {
            width: 36px; height: 36px;
            background: #1a6fb5;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .header-logo svg { width: 22px; height: 22px; stroke: #fff; fill: none; stroke-width: 2; }
        .header-info { flex: 1; }
        .header-info h1 { font-size: 13pt; color: #1a6fb5; letter-spacing: -.3px; line-height: 1; }
        .header-info h1 span { color: #135494; }
        .header-info p  { font-size: 7.5pt; color: #555; margin-top: 1px; }
        .header-meta { text-align: right; font-size: 7.5pt; color: #555; line-height: 1.5; }

        /* Título do documento */
        .doc-title {
            text-align: center;
            margin: 8px 0 2px;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 7.5pt;
            color: #555;
            margin-bottom: 10px;
        }

        /* Seção */
        .section { margin-bottom: 8px; }
        .section-title {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #1a6fb5;
            border-bottom: 1px solid #c7d8f0;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }

        /* Dados em grade */
        .dados-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3px 16px;
        }
        .dados-grid .full { grid-column: 1 / -1; }
        .dado-item { font-size: 9pt; line-height: 1.3; }
        .dado-item label { font-size: 7pt; color: #666; display: block; }
        .dado-item span  { font-weight: bold; }

        /* Texto jurídico */
        .texto-juridico {
            font-size: 8.5pt;
            line-height: 1.45;
            text-align: justify;
        }
        .texto-juridico p { margin-bottom: 4px; }

        /* Consentimentos */
        .consent-list { list-style: none; margin: 4px 0; }
        .consent-list li {
            display: flex;
            align-items: flex-start;
            gap: 7px;
            padding: 4px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 8.5pt;
            line-height: 1.3;
        }
        .consent-list li:last-child { border-bottom: none; }
        .check-box {
            width: 12px; height: 12px;
            border: 1.5px solid #333;
            border-radius: 2px;
            flex-shrink: 0;
            margin-top: 1px;
            display: flex; align-items: center; justify-content: center;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1;
        }
        .check-box.checked { background: #1a6fb5; border-color: #1a6fb5; color: #fff; }

        /* Assinaturas */
        .assinaturas {
            margin-top: 16px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        .assinatura-item { text-align: center; }
        .assinatura-linha {
            border-bottom: 1px solid #333;
            margin-bottom: 4px;
            height: 32px;
        }
        .assinatura-item p { font-size: 7.5pt; color: #333; line-height: 1.4; }

        /* Rodapé */
        .footer {
            margin-top: 10px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            font-size: 7pt;
            color: #999;
            text-align: center;
        }

        /* Prontuário badge */
        .pront-badge {
            font-size: 7.5pt;
            background: #e8f1fb;
            color: #1a6fb5;
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: bold;
        }

        /* Botões de controle (não imprimem) */
        .no-print {
            position: fixed;
            bottom: 24px;
            right: 24px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        .no-print button, .no-print a {
            padding: 10px 20px;
            border-radius: 9px;
            font-size: .88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-imprimir { background: #1a6fb5; color: #fff; }
        .btn-imprimir:hover { background: #135494; }
        .btn-voltar   { background: #e2e8f0; color: #2d3748; }

        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .page {
                width: 100%;
                margin: 0;
                padding: 10mm 12mm 8mm;
                box-shadow: none;
                min-height: auto;
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="page">

    <!-- Cabeçalho -->
    <div class="header">
        <div class="header-logo">
            <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <div class="header-info">
            <h1>Opus<span>Med</span></h1>
            <p>Sistema de Gestão em Saúde</p>
        </div>
        <div class="header-meta">
            Emitido em: <?= date('d/m/Y H:i') ?><br>
            <?php if ($p['prontuario']): ?>
            <span class="pront-badge"><?= htmlspecialchars($p['prontuario']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Título -->
    <div class="doc-title">Termo de Consentimento e Autorização</div>
    <div class="doc-subtitle">Lei Geral de Proteção de Dados Pessoais — Lei nº 13.709/2018 (LGPD)</div>

    <!-- Identificação do titular -->
    <div class="section">
        <div class="section-title">1. Identificação do Titular dos Dados</div>
        <div class="dados-grid">
            <div class="dado-item full">
                <label>Nome completo</label>
                <span><?= htmlspecialchars($p['nome']) ?></span>
                <?php if ($p['nome_social']): ?>
                &nbsp;&nbsp;<small style="color:#555;font-weight:normal">(Nome social: <?= htmlspecialchars($p['nome_social']) ?>)</small>
                <?php endif; ?>
            </div>
            <?php if ($cpfFmt): ?>
            <div class="dado-item">
                <label>CPF</label>
                <span><?= $cpfFmt ?></span>
            </div>
            <?php endif; ?>
            <?php if ($rgFmt): ?>
            <div class="dado-item">
                <label>RG</label>
                <span><?= htmlspecialchars($rgFmt) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($nascFmt): ?>
            <div class="dado-item">
                <label>Data de nascimento</label>
                <span><?= $nascFmt ?> (<?= $idade ?>)</span>
            </div>
            <?php endif; ?>
            <?php if (!empty($p['cns'])): ?>
            <div class="dado-item">
                <label>Cartão Nacional de Saúde (CNS)</label>
                <span><?= htmlspecialchars($p['cns']) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($p['telefone'])): ?>
            <div class="dado-item">
                <label>Telefone / Contato</label>
                <span><?= htmlspecialchars($p['telefone']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($temResponsavel): ?>
    <!-- Responsável legal -->
    <div class="section">
        <div class="section-title">2. Responsável Legal (quando aplicável)</div>
        <div class="dados-grid">
            <div class="dado-item">
                <label>Nome</label>
                <span><?= htmlspecialchars($p['resp_nome']) ?></span>
            </div>
            <div class="dado-item">
                <label>Parentesco / Vínculo</label>
                <span><?= htmlspecialchars($p['resp_parentesco'] ?? '—') ?></span>
            </div>
            <?php if ($p['resp_cpf']): ?>
            <div class="dado-item">
                <label>CPF do responsável</label>
                <?php $rcpf = strlen($p['resp_cpf']) === 11 ? preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $p['resp_cpf']) : $p['resp_cpf']; ?>
                <span><?= $rcpf ?></span>
            </div>
            <?php endif; ?>
            <?php if ($p['resp_telefone']): ?>
            <div class="dado-item">
                <label>Telefone</label>
                <span><?= htmlspecialchars($p['resp_telefone']) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Declaração de consentimento -->
    <div class="section">
        <div class="section-title"><?= $temResponsavel ? '3' : '2' ?>. Declaração de Consentimento</div>
        <div class="texto-juridico">
            <p>
                Eu, <strong><?= htmlspecialchars($p['nome']) ?></strong><?= $temResponsavel ? ', representado(a) por <strong>' . htmlspecialchars($p['resp_nome']) . '</strong>' : '' ?>,
                titular dos dados pessoais, declaro ter sido informado(a), de forma clara e inequívoca, sobre o tratamento dos meus dados pessoais e sensíveis pela
                <strong>OpusMed — Sistema de Gestão em Saúde</strong>, nos termos da <strong>Lei nº 13.709/2018 (LGPD)</strong>.
                Estou ciente de que os dados serão utilizados para fins assistenciais, incluindo identificação, agendamento, prontuário e comunicação com equipes de saúde.
                Fico ciente de que posso, a qualquer momento, solicitar acesso, correção, portabilidade, eliminação ou revogação deste consentimento, conforme art. 18 da LGPD.
            </p>
        </div>
    </div>

    <!-- Opções de consentimento -->
    <div class="section">
        <div class="section-title"><?= $temResponsavel ? '4' : '3' ?>. Opções de Consentimento</div>
        <ul class="consent-list">
            <li>
                <div class="check-box <?= $p['lgpd_consentimento'] ? 'checked' : '' ?>"><?= $p['lgpd_consentimento'] ? '✓' : '' ?></div>
                <div><strong>Tratamento de dados pessoais e de saúde para fins assistenciais</strong></div>
            </li>
            <li>
                <div class="check-box <?= $p['lgpd_whatsapp'] ? 'checked' : '' ?>"><?= $p['lgpd_whatsapp'] ? '✓' : '' ?></div>
                <div><strong>Comunicação via WhatsApp</strong> — lembretes, orientações e comunicados institucionais.</div>
            </li>
            <li>
                <div class="check-box <?= $p['lgpd_sms'] ? 'checked' : '' ?>"><?= $p['lgpd_sms'] ? '✓' : '' ?></div>
                <div><strong>Comunicação via SMS</strong> — informações sobre atendimentos e alertas de saúde.</div>
            </li>
            <li>
                <div class="check-box <?= $p['lgpd_email_consent'] ? 'checked' : '' ?>"><?= $p['lgpd_email_consent'] ? '✓' : '' ?></div>
                <div><strong>Comunicação via e-mail</strong> — comunicações, resultados de exames e informativos.</div>
            </li>
        </ul>

        <?php if (!empty($p['lgpd_finalidade'])): ?>
        <p style="font-size:10pt;margin-top:4px"><strong>Finalidade específica informada:</strong> <?= htmlspecialchars($p['lgpd_finalidade']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Assinatura -->
    <div class="section">
        <div class="section-title"><?= $temResponsavel ? '5' : '4' ?>. Assinatura</div>
        <p class="texto-juridico" style="margin-bottom:14px">
            Por ser verdade, firmo o presente Termo de livre e espontânea vontade, em <strong><?= $dataHoje ?></strong>.
        </p>

        <div class="assinaturas">
            <div class="assinatura-item">
                <div class="assinatura-linha"></div>
                <p><?= htmlspecialchars($temResponsavel ? $p['resp_nome'] : $p['nome']) ?></p>
                <p><?= $temResponsavel ? 'Responsável legal — ' . htmlspecialchars($p['resp_parentesco'] ?? '') : 'Titular dos dados' ?></p>
            </div>
            <div class="assinatura-item">
                <div class="assinatura-linha">
                    <?php if (!empty($p['lgpd_responsavel_aceite'])): ?>
                    <div style="padding-top:14px;font-size:9.5pt;text-align:center;color:#1a6fb5">
                        <?= htmlspecialchars($p['lgpd_responsavel_aceite']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <p>Profissional / Responsável pelo cadastro</p>
                <p style="color:#888">OpusMed — Sistema de Gestão em Saúde</p>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        OpusMed — Sistema de Gestão em Saúde &nbsp;|&nbsp;
        Documento gerado em <?= date('d/m/Y \à\s H:i') ?> &nbsp;|&nbsp;
        <?php if ($p['prontuario']): ?>Prontuário: <?= htmlspecialchars($p['prontuario']) ?> &nbsp;|&nbsp; <?php endif; ?>
        Lei nº 13.709/2018 (LGPD)
    </div>

</div>

<!-- Botões flutuantes (não imprimem) -->
<div class="no-print">
    <a href="paciente_form.php?id=<?= $id ?>" class="btn-voltar">&#8592; Voltar</a>
    <button class="btn-imprimir" onclick="window.print()">
        &#128438; Imprimir / Salvar PDF
    </button>
</div>

</body>
</html>
