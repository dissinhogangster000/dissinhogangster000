<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

$host = 'localhost';
$db   = 'painel_programas';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO('mysql:host=' . $host . ';charset=utf8mb4', $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die('Não foi possível conectar ao MySQL: ' . $e->getMessage());
}

$pdo->exec('CREATE DATABASE IF NOT EXISTS `' . $db . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo->exec('USE `' . $db . '`');

$pdo->exec('
CREATE TABLE IF NOT EXISTS categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$pdo->exec('
CREATE TABLE IF NOT EXISTS programas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NULL,
  nome VARCHAR(120) NOT NULL,
  icone VARCHAR(8) NOT NULL DEFAULT "",
  descricao TEXT NOT NULL,
  preco_dia DECIMAL(10,2) NOT NULL DEFAULT 0,
  preco_semana DECIMAL(10,2) NOT NULL DEFAULT 0,
  preco_mes DECIMAL(10,2) NOT NULL DEFAULT 0,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$pdo->exec('
CREATE TABLE IF NOT EXISTS alugueis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  programa_id INT NOT NULL,
  cliente_nome VARCHAR(120) NOT NULL,
  cliente_contato VARCHAR(60) NOT NULL,
  plano ENUM("dia","semana","mes") NOT NULL DEFAULT "dia",
  valor DECIMAL(10,2) NOT NULL DEFAULT 0,
  data_inicio DATE NOT NULL,
  data_fim DATE NOT NULL,
  codigo_acesso VARCHAR(120) NOT NULL DEFAULT "",
  status ENUM("ativo","concluido","vencido","cancelado") NOT NULL DEFAULT "ativo",
  observacao TEXT NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$pdo->exec('
CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$conta = $pdo->query('SELECT COUNT(*) FROM admin')->fetchColumn();
if (!$conta) {
    $stmt = $pdo->prepare('INSERT INTO admin (nome, email, senha) VALUES (?, ?, ?)');
    $stmt->execute(['Administrador', 'admin@admin.com', password_hash('admin123', PASSWORD_DEFAULT)]);
    $adminCriado = true;
} else {
    $adminCriado = false;
}

$categorias = $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
if (!$categorias) {
    $pdo->exec('
    INSERT INTO categorias (nome) VALUES
    ("Streaming"), ("Aplicativos"), ("Softwares"), ("Jogos")');

    $pdo->exec('
    INSERT INTO programas (categoria_id, nome, icone, descricao, preco_dia, preco_semana, preco_mes, destaque, ativo) VALUES
    (1, "Netflix Premium", "📺", "Acesso Netflix em 4K, perfil completo. Renove quando quiser.", 2.50, 9.90, 24.90, 1, 1),
    (1, "Spotify Premium", "🎵", "Spotify sem anúncios com download de músicas.", 1.50, 5.90, 14.90, 1, 1),
    (2, "Canva Pro", "🎨", "Todos os recursos premium do Canva.", 2.00, 6.90, 17.90, 0, 1),
    (3, "Office 365", "💼", "Pacote Office completo com 1TB de OneDrive.", 3.00, 9.90, 29.90, 0, 1),
    (4, "Game Pass Ultimate", "🎮", "Catálogo de jogos no console e PC.", 4.00, 14.90, 39.90, 1, 1)');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalação | <?= htmlspecialchars('Painel de Programas') ?></title>
<style>
  body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
  .card { background: #1e293b; padding: 40px; border-radius: 16px; max-width: 520px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
  h1 { margin-top: 0; font-size: 22px; }
  .ok { color: #4ade80; }
  ul { padding-left: 20px; }
  li { margin: 8px 0; }
  a { display: inline-block; margin-top: 16px; text-decoration: none; }
  .btn { background: #6366f1; color: white; padding: 12px 20px; border-radius: 10px; margin-right: 8px; font-weight: 600; }
  code { background: #0f172a; padding: 2px 8px; border-radius: 6px; color: #93c5fd; }
</style>
</head>
<body>
<div class="card">
  <h1 class="ok">Instalação concluída</h1>
  <p>O banco de dados foi criado e configurado com sucesso.</p>
  <ul>
    <li>Banco: <code>painel_programas</code></li>
    <li>Admin: <code>admin@admin.com</code></li>
    <li>Senha: <code>admin123</code></li>
    <?php if ($adminCriado): ?>
    <li class="ok">Administrador padrão criado.</li>
    <?php else: ?>
    <li>O administrador padrão já existia, ninguém foi sobrescrito.</li>
    <?php endif; ?>
  </ul>
  <p><strong>Por segurança, delete o arquivo <code>instalar.php</code> do servidor agora.</strong></p>
  <a class="btn" href="index.php">Ver site</a>
  <a class="btn" href="painel/login.php">Acessar painel</a>
</div>
</body>
</html>