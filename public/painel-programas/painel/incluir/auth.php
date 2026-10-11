<?php
require_once __DIR__ . '/../../incluir/config.php';

function exigir_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function marcar_vencidos($pdo) {
    $pdo->exec("UPDATE alugueis SET status = 'vencido' WHERE status = 'ativo' AND data_fim < CURDATE()");
}

function admin_nome() {
    return $_SESSION['admin_nome'] ?? 'Administrador';
}

function redirecionar($destino, $aviso = null, $tipo = 'sucesso') {
    if ($aviso !== null) {
        $_SESSION['flash'] = ['texto' => $aviso, 'tipo' => $tipo];
    }
    header('Location: ' . $destino);
    exit;
}

function painel_cabecalho($pdo, $titulo, $ativo) { ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($titulo) ?> | Painel</title>
<link rel="stylesheet" href="assets/painel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<aside class="lateral">
  <a href="index.php" class="lat-marca"><i class="fas fa-bolt"></i> <?= e(SITE_NOME) ?></a>
  <nav class="lat-menu">
    <a href="index.php" class="<?= $ativo === 'dashboard' ? 'ativo' : '' ?>"><i class="fas fa-gauge-high"></i> Dashboard</a>
    <a href="programas.php" class="<?= $ativo === 'programas' ? 'ativo' : '' ?>"><i class="fas fa-box-open"></i> Programas</a>
    <a href="alugueis.php" class="<?= $ativo === 'alugueis' ? 'ativo' : '' ?>"><i class="fas fa-handshake"></i> Aluguéis</a>
    <a href="categorias.php" class="<?= $ativo === 'categorias' ? 'ativo' : '' ?>"><i class="fas fa-tags"></i> Categorias</a>
  </nav>
  <div class="lat-rodape">
    <a href="../index.php" target="_blank"><i class="fas fa-globe"></i> Ver site</a>
    <a href="logout.php"><i class="fas fa-right-from-bracket"></i> Sair</a>
  </div>
</aside>
<main class="conteudo">
  <header class="topbar">
    <div>
      <h1><?= e($titulo) ?></h1>
      <p class="topbar-sub">Bem-vindo(a), <?= e(admin_nome()) ?></p>
    </div>
  </header>
  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alerta alerta-<?= e($_SESSION['flash']['tipo']) ?>">
      <?= e($_SESSION['flash']['texto']) ?>
      <button class="alerta-fechar" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>
<?php }

function painel_rodape() { ?>
</main>
<script src="assets/painel.js"></script>
</body>
</html>
<?php }