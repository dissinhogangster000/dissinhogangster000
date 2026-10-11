<?php
require_once __DIR__ . '/incluir/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$erro = null;
$sucesso = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    
    if ($nome === '' || $email === '' || $senha === '' || $confirmar === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif ($senha !== $confirmar) {
        $erro = 'As senhas não coincidem.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM admin WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erro = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admin (nome, email, senha, criado_em) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$nome, $email, $hash]);
            $sucesso = 'Cadastro realizado com sucesso! Faça login para acessar o painel.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro | Painel de Agendamento</title>
<link rel="stylesheet" href="assets/painel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-wrap">
  <form class="login-cartao" method="post">
    <div class="login-marca"><i class="fas fa-calendar-check"></i> Painel de Agendamento</div>
    <h2 style="text-align:center">Criar conta</h2>
    <?php if ($erro): ?>
      <div class="alerta alerta-erro" style="margin:0"><?= e($erro) ?></div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
      <div class="alerta alerta-sucesso" style="margin:0"><?= e($sucesso) ?></div>
    <?php endif; ?>
    <div class="form-linha">
      <label>Nome completo
        <input type="text" name="nome" required placeholder="Seu nome" value="<?= e($_POST['nome'] ?? '') ?>">
      </label>
    </div>
    <div class="form-linha">
      <label>E-mail
        <input type="email" name="email" required placeholder="seu@email.com" value="<?= e($_POST['email'] ?? '') ?>">
      </label>
    </div>
    <div class="form-linha">
      <label>Senha
        <input type="password" name="senha" required placeholder="Mínimo 6 caracteres">
      </label>
    </div>
    <div class="form-linha">
      <label>Confirmar senha
        <input type="password" name="confirmar" required placeholder="Digite novamente">
      </label>
    </div>
    <button class="btn btn-primario" type="submit"><i class="fas fa-user-plus"></i> Cadastrar</button>
    <a class="btn" href="login.php" style="text-align:center;text-decoration:none;display:block"><i class="fas fa-arrow-left"></i> Já tenho conta</a>
  </form>
</div>
</body>
</html>