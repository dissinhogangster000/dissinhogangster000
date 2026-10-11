<?php
require_once __DIR__ . '/../../agendamento-config/config.php';

function exigir_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
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
<title><?= e($titulo) ?> | Painel de Agendamento</title>
<link rel="stylesheet" href="assets/painel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<aside class="lateral">
  <a href="index.php" class="lat-marca"><i class="fas fa-calendar-check"></i> Agendamentos</a>
  <nav class="lat-menu">
    <a href="index.php" class="<?= $ativo === 'dashboard' ? 'ativo' : '' ?>"><i class="fas fa-gauge-high"></i> Dashboard</a>
    <a href="agenda.php" class="<?= $ativo === 'agenda' ? 'ativo' : '' ?>"><i class="fas fa-calendar-week"></i> Agenda</a>
    <a href="agendamentos.php" class="<?= $ativo === 'agendamentos' ? 'ativo' : '' ?>"><i class="fas fa-clock"></i> Agendamentos</a>
    <a href="servicos.php" class="<?= $ativo === 'servicos' ? 'ativo' : '' ?>"><i class="fas fa-clipboard-list"></i> Serviços</a>
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