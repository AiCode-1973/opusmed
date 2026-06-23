<?php
/**
 * Repara nomes dos módulos com charset incorreto no banco.
 * ⚠ APAGUE ESTE ARQUIVO APÓS O USO!
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_name('opusmed_sess');
@session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['key'] ?? '') !== 'opusdiag2026') {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f0f4f8;margin:0}
    .box{background:#fff;padding:40px;border-radius:12px;border:1px solid #e2e8f0;text-align:center;min-width:300px}
    input{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:12px 0;font-size:1rem;box-sizing:border-box}
    button{width:100%;padding:10px;background:#1a6fb5;color:#fff;border:none;border-radius:8px;font-size:1rem;cursor:pointer}
    </style></head><body><div class="box">
    <h2 style="margin-top:0">Reparar Módulos</h2>
    <form method="POST">
        <input type="password" name="key" placeholder="Senha" autofocus>
        <button type="submit">Executar reparo</button>
    </form>
    </div></body></html>';
    exit;
}

require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance()->getConnection();

// Força UTF-8 na conexão
$db->exec("SET NAMES utf8mb4");
$db->exec("SET CHARACTER SET utf8mb4");

$atualizacoes = [
    '/prontuario'   => 'Prontuário',
    '/internacao'   => 'Internação',
    '/farmacia'     => 'Farmácia',
    '/laboratorio'  => 'Laboratório',
    '/relatorios'   => 'Relatórios',
    '/configuracoes'=> 'Configurações',
    '/usuarios'     => 'Usuários',
];

echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>body{font-family:monospace;padding:30px;background:#f0f4f8}
.ok{color:#166534;background:#d1fae5;padding:2px 8px;border-radius:4px}
.err{color:#991b1b;background:#fee2e2;padding:2px 8px;border-radius:4px}
h2{color:#1a6fb5}
</style></head><body>';
echo '<h2>Reparo de módulos</h2>';

$totalOk  = 0;
$totalErr = 0;

foreach ($atualizacoes as $rota => $nomeCorreto) {
    try {
        $stmt = $db->prepare("UPDATE modulos SET nome = ? WHERE rota = ?");
        $stmt->execute([$nomeCorreto, $rota]);
        $afetadas = $stmt->rowCount();
        echo "Rota <b>{$rota}</b> → <b>{$nomeCorreto}</b>: ";
        echo $afetadas > 0
            ? '<span class="ok">ATUALIZADO</span>'
            : '<span style="color:#92400e">sem alteração (já estava correto?)</span>';
        echo '<br>';
        $totalOk++;
    } catch (Exception $e) {
        echo "Rota <b>{$rota}</b>: <span class='err'>ERRO — " . htmlspecialchars($e->getMessage()) . '</span><br>';
        $totalErr++;
    }
}

// Verifica resultado final
echo '<hr><h3>Verificação após reparo</h3>';
$rows = $db->query("SELECT nome, rota, bin(1) FROM modulos ORDER BY ordem")->fetchAll();
echo '<table border="1" cellpadding="6" style="border-collapse:collapse;background:#fff">';
echo '<tr style="background:#f8fafc"><th>Nome gravado</th><th>Rota</th><th>Hex do nome</th></tr>';
foreach ($rows as $r) {
    $hex = bin2hex($r['nome']);
    echo "<tr><td>{$r['nome']}</td><td>{$r['rota']}</td><td style='font-size:.75rem'>{$hex}</td></tr>";
}
echo '</table>';

echo '<br><b>Total atualizado:</b> ' . $totalOk . ' módulos<br>';
echo '<br><span style="color:#991b1b"><b>⚠ Apague repair_modulos.php do servidor agora!</b></span>';
echo '</body></html>';
