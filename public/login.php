<?php
session_start();

// Redireciona se já estiver logado
if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../app/models/Usuario.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email',  FILTER_SANITIZE_EMAIL) ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->autenticar($email, $senha);

        if ($usuario) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']    = $usuario['id'];
            $_SESSION['usuario_nome']  = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['perfil_id']     = $usuario['perfil_id'];
            $_SESSION['perfil_nome']   = $usuario['perfil_nome'];

            header('Location: dashboard.php');
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpusMed — Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="login-wrapper">

    <!-- ── Lado esquerdo: identidade do sistema ── -->
    <div class="login-brand">
        <div class="brand-logo">
            <!-- Ícone: cruz médica / monitor hospitalar -->
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>

        <h1 class="brand-title">Opus<span>Med</span></h1>
        <p class="brand-tagline">Sistema Hospitalar Integrado</p>

        <ul class="brand-features">
            <li>
                <span class="feature-icon">🏥</span>
                Gestão completa de pacientes
            </li>
            <li>
                <span class="feature-icon">📅</span>
                Agendamento e prontuário eletrônico
            </li>
            <li>
                <span class="feature-icon">💊</span>
                Controle de farmácia e laboratório
            </li>
            <li>
                <span class="feature-icon">📊</span>
                Relatórios e faturamento integrados
            </li>
        </ul>
    </div>

    <!-- ── Lado direito: formulário ── -->
    <div class="login-panel">
        <div class="login-box">

            <h2>Bem-vindo de volta</h2>
            <p class="subtitle">Informe suas credenciais para acessar o sistema.</p>

            <?php if ($erro !== ''): ?>
            <div class="alert-error" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?= htmlspecialchars($erro) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate id="loginForm">

                <!-- E-mail -->
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="seu@email.com.br"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="email"
                            required
                        >
                    </div>
                </div>

                <!-- Senha -->
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input
                            type="password"
                            id="senha"
                            name="senha"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" aria-label="Mostrar senha" data-target="senha">
                            <svg id="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Opções -->
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="lembrar" id="lembrar">
                        Lembrar-me
                    </label>
                    <a href="recuperar-senha.php" class="forgot-link">Esqueci a senha</a>
                </div>

                <!-- Botão -->
                <button type="submit" class="btn-login" id="btnLogin">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Entrar no sistema
                </button>

            </form>

            <div class="login-footer">
                &copy; <?= date('Y') ?> OpusMed &mdash; Todos os direitos reservados
            </div>

        </div>
    </div>

</div>

<script>
// Toggle mostrar/ocultar senha
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        btn.setAttribute('aria-label', isText ? 'Mostrar senha' : 'Ocultar senha');
    });
});

// Desabilita botão ao submeter (evita duplo clique)
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.2"
             stroke-linecap="round" stroke-linejoin="round"
             style="animation:spin .8s linear infinite">
            <line x1="12" y1="2" x2="12" y2="6"/>
            <line x1="12" y1="18" x2="12" y2="22"/>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/>
            <line x1="2" y1="12" x2="6" y2="12"/>
            <line x1="18" y1="12" x2="22" y2="12"/>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/>
        </svg>
        Aguarde...`;
});
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

</body>
</html>
