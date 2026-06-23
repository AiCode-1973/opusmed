<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Usuários', 'pode_ver');

require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Perfil.php';

$usuarioModel = new Usuario();
$perfilModel  = new Perfil();
$perfis       = $perfilModel->listarTodos();

$id        = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editando  = $id > 0;
$usuario   = [];
$erros     = [];

if ($editando) {
    exigirPermissao('Usuários', 'pode_editar');
    $usuario = $usuarioModel->buscarPorId($id);
    if (!$usuario) {
        header('Location: usuarios.php');
        exit;
    }
} else {
    exigirPermissao('Usuários', 'pode_criar');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'perfil_id' => (int) ($_POST['perfil_id'] ?? 0),
        'nome'      => trim($_POST['nome']      ?? ''),
        'email'     => trim($_POST['email']     ?? ''),
        'cpf'       => preg_replace('/\D/', '', $_POST['cpf']      ?? ''),
        'telefone'  => trim($_POST['telefone']  ?? ''),
        'ativo'     => isset($_POST['ativo']) ? 1 : 0,
    ];
    $senha     = $_POST['senha']     ?? '';
    $confirma  = $_POST['confirma']  ?? '';

    // Validações
    if ($dados['nome'] === '')        $erros[] = 'O nome é obrigatório.';
    if ($dados['email'] === '')       $erros[] = 'O e-mail é obrigatório.';
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if ($dados['perfil_id'] === 0)    $erros[] = 'Selecione um perfil.';
    if (!$editando && $senha === '')  $erros[] = 'A senha é obrigatória para novos usuários.';
    if ($senha !== '' && $senha !== $confirma) $erros[] = 'As senhas não coincidem.';
    if ($senha !== '' && strlen($senha) < 6)   $erros[] = 'A senha deve ter no mínimo 6 caracteres.';

    if (empty($erros)) {
        if ($editando) {
            $usuarioModel->atualizar($id, $dados);
            if ($senha !== '') {
                $usuarioModel->alterarSenha($id, $senha);
            }
            header('Location: usuarios.php?msg=editado');
        } else {
            $dados['senha'] = $senha;
            $usuarioModel->criar($dados);
            header('Location: usuarios.php?msg=criado');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $editando ? 'Editar usuário' : 'Novo usuário' ?> — OpusMed</title>
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
                    <div class="page-title"><?= $editando ? 'Editar usuário' : 'Novo usuário' ?></div>
                    <div class="page-breadcrumb">
                        <a href="usuarios.php" style="color:var(--primary)">Usuários</a> /
                        <?= $editando ? htmlspecialchars($usuario['nome']) : 'Novo' ?>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <a href="usuarios.php" class="btn btn-ghost">
                    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Voltar
                </a>
            </div>
        </header>

        <main class="page-content">

            <?php if (!empty($erros)): ?>
            <div class="alert alert-danger" style="margin-bottom:20px">
                <ul style="margin:0;padding-left:18px">
                    <?php foreach ($erros as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="card" style="max-width:680px">
                <div class="card-header">
                    <h3><?= $editando ? 'Editar dados do usuário' : 'Dados do novo usuário' ?></h3>
                </div>
                <div class="card-body" style="padding:28px">

                    <form method="POST" action="">

                        <div class="form-grid-2">

                            <!-- Nome -->
                            <div class="form-group full-width">
                                <label for="nome">Nome completo <span class="required">*</span></label>
                                <input type="text" id="nome" name="nome" maxlength="150"
                                       value="<?= htmlspecialchars($_POST['nome'] ?? $usuario['nome'] ?? '') ?>"
                                       placeholder="Nome completo do usuário" required>
                            </div>

                            <!-- E-mail -->
                            <div class="form-group">
                                <label for="email">E-mail <span class="required">*</span></label>
                                <input type="email" id="email" name="email" maxlength="200"
                                       value="<?= htmlspecialchars($_POST['email'] ?? $usuario['email'] ?? '') ?>"
                                       placeholder="email@dominio.com.br" required>
                            </div>

                            <!-- Perfil -->
                            <div class="form-group">
                                <label for="perfil_id">Perfil de acesso <span class="required">*</span></label>
                                <select id="perfil_id" name="perfil_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($perfis as $p): ?>
                                    <option value="<?= $p['id'] ?>"
                                        <?= ((int)($_POST['perfil_id'] ?? $usuario['perfil_id'] ?? 0)) === (int)$p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- CPF -->
                            <div class="form-group">
                                <label for="cpf">CPF</label>
                                <input type="text" id="cpf" name="cpf" maxlength="14"
                                       value="<?= htmlspecialchars($_POST['cpf'] ?? $usuario['cpf'] ?? '') ?>"
                                       placeholder="000.000.000-00">
                            </div>

                            <!-- Telefone -->
                            <div class="form-group">
                                <label for="telefone">Telefone</label>
                                <input type="text" id="telefone" name="telefone" maxlength="20"
                                       value="<?= htmlspecialchars($_POST['telefone'] ?? $usuario['telefone'] ?? '') ?>"
                                       placeholder="(00) 00000-0000">
                            </div>

                            <!-- Status (só na edição) -->
                            <?php if ($editando): ?>
                            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding-top:24px">
                                <input type="checkbox" id="ativo" name="ativo" value="1"
                                       <?= ((int)($_POST['ativo'] ?? $usuario['ativo'] ?? 1)) ? 'checked' : '' ?>>
                                <label for="ativo" style="margin:0;cursor:pointer">Usuário ativo</label>
                            </div>
                            <?php endif; ?>

                        </div>

                        <hr style="margin:24px 0;border:none;border-top:1px solid var(--border)">
                        <h4 style="font-size:.9rem;font-weight:600;margin-bottom:16px;color:var(--muted)">
                            <?= $editando ? 'Alterar senha (deixe em branco para não alterar)' : 'Senha de acesso' ?>
                        </h4>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="senha">Nova senha <?= !$editando ? '<span class="required">*</span>' : '' ?></label>
                                <div class="pw-wrap">
                                    <input type="password" id="senha" name="senha"
                                           placeholder="Mínimo 6 caracteres"
                                           <?= !$editando ? 'required' : '' ?>>
                                    <button type="button" class="toggle-pw" data-target="senha">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirma">Confirmar senha <?= !$editando ? '<span class="required">*</span>' : '' ?></label>
                                <div class="pw-wrap">
                                    <input type="password" id="confirma" name="confirma"
                                           placeholder="Repita a senha"
                                           <?= !$editando ? 'required' : '' ?>>
                                    <button type="button" class="toggle-pw" data-target="confirma">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:28px">
                            <button type="submit" class="btn btn-primary">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                <?= $editando ? 'Salvar alterações' : 'Criar usuário' ?>
                            </button>
                            <a href="usuarios.php" class="btn btn-ghost">Cancelar</a>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
// Mostrar/ocultar senha
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type  = input.type === 'text' ? 'password' : 'text';
    });
});

// Máscara CPF
document.getElementById('cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g,'').substring(0,11);
    if (v.length > 9) v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
    else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
    else if (v.length > 3) v = v.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
    this.value = v;
});

// Máscara telefone
document.getElementById('telefone').addEventListener('input', function () {
    let v = this.value.replace(/\D/g,'').substring(0,11);
    if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    else if (v.length > 6) v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
    else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
    this.value = v;
});
</script>

<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
