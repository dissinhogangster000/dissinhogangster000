<?php
require_once __DIR__ . '/incluir/config.php';
$titulo = 'Início';

$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$categorias = listar_categorias($pdo);
$programas = listar_programas($pdo, $categoria_id);
$categoria_atual = null;
foreach ($categorias as $c) {
    if ($c['id'] == $categoria_id) $categoria_atual = $c['nome'];
}

require __DIR__ . '/incluir/site_cabecalho.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div class="hero-texto">
      <span class="hero-etiqueta">Aluguel e desbloqueio em minutos</span>
      <h1>Seus <span class="destaque">programas favoritos</span> a preço de aluguel</h1>
      <p>Escolha o plano diário, semanal ou mensal. Ativação rápida e suporte via WhatsApp.</p>
      <div class="hero-botoes">
        <a href="#programas" class="btn btn-primario">Ver programas</a>
        <a href="<?= e(url_whats('Olá! Quero alugar um programa.')) ?>" target="_blank" class="btn btn-whats"><i class="fab fa-whatsapp"></i> Chamar no WhatsApp</a>
      </div>
    </div>
    <div class="hero-badge">
      <div class="badge-icone"><i class="fas fa-shield-halved"></i></div>
      <h3>Entrega rápida</h3>
      <p>Ativação após confirmação do pagamento, geralmente em poucos minutos.</p>
    </div>
  </div>
</section>

<section class="container secao" id="programas">
  <div class="secao-topo">
    <div>
      <span class="secao-etiqueta">Catálogo</span>
      <h2><?= $categoria_atual ? 'Programas de ' . e($categoria_atual) : 'Todos os programas' ?></h2>
    </div>
    <div class="filtros">
      <a href="index.php" class="filtro <?= !$categoria_id ? 'ativo' : '' ?>">Todos</a>
      <?php foreach ($categorias as $c): ?>
        <a href="index.php?categoria=<?= (int)$c['id'] ?>" class="filtro <?= $categoria_id == $c['id'] ? 'ativo' : '' ?>"><?= e($c['nome']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!$programas): ?>
    <div class="vazio"><i class="fas fa-box-open"></i><p>Nenhum programa disponível nesta categoria.</p></div>
  <?php else: ?>
  <div class="grade">
    <?php foreach ($programas as $p): ?>
    <article class="cartao">
      <?php if ((int)$p['destaque'] === 1): ?><span class="selo-destaque">Destaque</span><?php endif; ?>
      <div class="cartao-icone"><span><?= $p['icone'] ?: '📦' ?></span></div>
      <h3><?= e($p['nome']) ?></h3>
      <p class="cartao-categoria"><?= e($p['categoria'] ?? '—') ?></p>
      <p class="cartao-desc"><?= e(resumir($p['descricao'])) ?><?= tem_mais_que($p['descricao'], 90) ? '…' : '' ?></p>
      <div class="precos">
        <div class="preco"><small>Diário</small><strong><?= fmt_moeda($p['preco_dia']) ?></strong></div>
        <div class="preco"><small>Semana</small><strong><?= fmt_moeda($p['preco_semana']) ?></strong></div>
        <div class="preco"><small>Mês</small><strong><?= fmt_moeda($p['preco_mes']) ?></strong></div>
      </div>
      <a href="programa.php?id=<?= (int)$p['id'] ?>" class="btn btn-primario btn-bloco">Alugar agora</a>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<section class="como-funciona">
  <div class="container">
    <span class="secao-etiqueta">Como funciona</span>
    <h2>Simples e rápido</h2>
    <div class="passos">
      <div class="passo"><span class="passo-num">1</span><h4>Escolha o programa</h4><p>Navegue pelo catálogo e escolha o plano que atende você.</p></div>
      <div class="passo"><span class="passo-num">2</span><h4>Faça o pedido</h4><p>Envie seus dados pelo formulário e combine o pagamento.</p></div>
      <div class="passo"><span class="passo-num">3</span><h4>Receba o acesso</h4><p>Após a confirmação, você recebe o código/desbloqueio ativado.</p></div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/incluir/site_rodape.php'; ?>