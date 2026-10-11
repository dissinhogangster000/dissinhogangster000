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

    $stmt = $pdo->prepare('SELECT * FROM admin WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($senha, $admin['senha'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_nome'] = $admin['nome'];
        header('Location: index.php');
        exit;
    }
    $erro = 'E-mail ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrar | Painel</title>
<link rel="stylesheet" href="assets/painel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="pagina-login">
<div class="caixa-login">
  <div class="login-logo"><i class="fas fa-bolt"></i></div>
  <h1><?= e(SITE_NOME) ?></h1>
  <p class="login-sub">Acesse o painel de administração</p>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
  <form method="post" class="form-login">
    <label>E-mail
      <input type="email" name="email" required autofocus placeholder="admin@admin.com">
    </label>
    <label>Senha
      <input type="password" name="senha" required placeholder="••••••••">
    </label>
    <button type="submit" class="btn btn-primario btn-bloco">Entrar</button>
  </form>
  <a class="login-voltar" href="../index.php"><i class="fas fa-arrow-left"></i> Voltar ao site</a>
</div>
</body>
</html>