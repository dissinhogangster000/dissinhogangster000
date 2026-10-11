<?php
require_once __DIR__ . '/incluir/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if ($email === '' || $senha === '') {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header('Location: index.php');
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
<title>Login | Painel de Agendamento</title>
<link rel="stylesheet" href="assets/painel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-wrap">
  <form class="login-cartao" method="post">
    <div class="login-marca"><i class="fas fa-calendar-check"></i> Painel de Agendamento</div>
    <h2 style="text-align:center">Entrar no painel</h2>
    <?php if ($erro): ?>
      <div class="alerta alerta-erro" style="margin:0"><?= e($erro) ?></div>
    <?php endif; ?>
    <div class="form-linha">
      <label>E-mail
        <input type="email" name="email" required placeholder="admin@admin.com" value="<?= e($_POST['email'] ?? '') ?>">
      </label>
    </div>
    <div class="form-linha">
      <label>Senha
        <input type="password" name="senha" required placeholder="••••••••">
      </label>
    </div>
    <button class="btn btn-primario" type="submit"><i class="fas fa-right-to-bracket"></i> Entrar</button>
    <small style="text-align:center;color:#6b7280">Acesso inicial: admin@admin.com / admin123</small>
  </form>
</div>
</body>
</html>