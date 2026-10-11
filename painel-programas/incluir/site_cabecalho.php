<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titulo) ? e($titulo) . ' | ' : '' ?><?= e(SITE_NOME) ?></title>
<meta name="description" content="<?= e(SITE_DESCRICAO) ?>">
<link rel="stylesheet" href="assets/css/site.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<nav class="topo">
  <div class="container topo-inner">
    <a href="index.php" class="marca">
      <span class="marca-icone"><i class="fas fa-bolt"></i></span>
      <span class="marca-texto"><?= e(SITE_NOME) ?></span>
    </a>
    <ul class="topo-menu">
      <li><a href="index.php">Início</a></li>
      <li><a href="painel/login.php">Painel</a></li>
      <li><a href="<?= e(url_whats('Olá! Quero saber mais sobre os programas.')) ?>" target="_blank"><i class="fab fa-whatsapp"></i> Suporte</a></li>
    </ul>
  </div>
</nav>
<?php if (isset($mensagem)): ?>
<div class="container aviso aviso-<?= e($mensagem_tipo ?? 'info') ?>"><?= e($mensagem) ?></div>
<?php endif; ?>