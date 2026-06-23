<?php
require_once __DIR__ . '/../app/helpers/guard.php';
exigirPermissao('Usuários', 'pode_editar');

require_once __DIR__ . '/../app/models/Usuario.php';

$usuarioModel = new Usuario();
$id           = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$usuario      = $usuarioModel->buscarPorId($id);

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha    = $_POST['senha']    ?? '';
    $confirma = $_POST['confirma'] ?? '';
    $senhaAtual = $_POST['senha_atual'] ?? '';

    // Valida senha atual se o usuário estiver alterando a própria senha
    if ($id === (int) $_SESSION['usuario_id']) {
        $usuarioCompleto = $usuarioModel->buscarPorEmail($usuario['email']);
        if (!password_verify($senhaAtual, $usuarioCompleto['senha'])) {
            $erros[] = 'Senha atual incorreta.';
        }
    }

    if ($senha === '')              $erros[] = 'A nova senha é obrigatória.';
    if (strlen($senha) < 6)        $erros[] = 'A senha deve ter no mínimo 6 caracteres.';
    if ($senha !== $confirma)      $erros[] = 'As senhas não coincidem.';

    if (empty($erros)) {
        $usuarioModel->alterarSenha($id, $senha);
        header('Location: usuarios.php?msg=senha');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar senha — OpusMed</title>
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
                    <div class="page-title">Alterar senha</div>
                    <div class="page-breadcrumb">
                        <a href="usuarios.php" style="color:var(--primary)">Usuários</a> / <?= htmlspecialchars($usuario['nome']) ?>
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

            <div class="card" style="max-width:480px">
                <div class="card-header">
                    <h3>Nova senha para: <strong><?= htmlspecialchars($usuario['nome']) ?></strong></h3>
                </div>
                <div class="card-body" style="padding:28px">
                    <form method="POST" action="">

                        <?php if ($id === (int) $_SESSION['usuario_id']): ?>
                        <div class="form-group">
                            <label for="senha_atual">Senha atual <span class="required">*</span></label>
                            <div class="pw-wrap">
                                <input type="password" id="senha_atual" name="senha_atual" required>
                                <button type="button" class="toggle-pw" data-target="senha_atual">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <hr style="margin:20px 0;border:none;border-top:1px solid var(--border)">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="senha">Nova senha <span class="required">*</span></label>
                            <div class="pw-wrap">
                                <input type="password" id="senha" name="senha" placeholder="Mínimo 6 caracteres" required>
                                <button type="button" class="toggle-pw" data-target="senha">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirma">Confirmar nova senha <span class="required">*</span></label>
                            <div class="pw-wrap">
                                <input type="password" id="confirma" name="confirma" placeholder="Repita a senha" required>
                                <button type="button" class="toggle-pw" data-target="confirma">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:28px">
                            <button type="submit" class="btn btn-primary">
                                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Salvar nova senha
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
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type  = input.type === 'text' ? 'password' : 'text';
    });
});
</script>
<?php include __DIR__ . '/../app/views/toggle_script.php'; ?>
</body>
</html>
