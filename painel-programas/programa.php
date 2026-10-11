<?php
require_once __DIR__ . '/incluir/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT p.*, c.nome AS categoria FROM programas p LEFT JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ? AND p.ativo = 1');
$stmt->execute([$id]);
$programa = $stmt->fetch();

if (!$programa) {
    http_response_code(404);
    $titulo = 'Não encontrado';
    require __DIR__ . '/incluir/site_cabecalho.php';
    echo '<section class="container secao"><div class="vazio"><i class="fas fa-ban"></i><p>Programa não encontrado.</p><a class="btn btn-primario" href="index.php">Voltar ao catálogo</a></div></section>';
    require __DIR__ . '/incluir/site_rodape.php';
    exit;
}

$titulo = $programa['nome'];
require __DIR__ . '/incluir/site_cabecalho.php';
?>

<section class="container secao">
  <div class="detalhe">
    <div class="detalhe-info">
      <div class="detalhe-icone"><span><?= $programa['icone'] ?: '📦' ?></span></div>
      <span class="secao-etiqueta"><?= e($programa['categoria'] ?? 'Programa') ?></span>
      <h1><?= e($programa['nome']) ?></h1>
      <p class="detalhe-desc"><?= nl2br(e($programa['descricao'])) ?></p>
      <table class="tabela-precos">
        <tr><th>Plano</th><th>Duração</th><th>Preço</th></tr>
        <tr><td>Diário</td><td>1 dia</td><td><strong><?= fmt_moeda($programa['preco_dia']) ?></strong></td></tr>
        <tr><td>Semanal</td><td>7 dias</td><td><strong><?= fmt_moeda($programa['preco_semana']) ?></strong></td></tr>
        <tr><td>Mensal</td><td>30 dias</td><td><strong><?= fmt_moeda($programa['preco_mes']) ?></strong></td></tr>
      </table>
      <a class="btn btn-whats" href="<?= e(url_whats('Olá! Quero alugar ' . $programa['nome'] . ' por R$ ' . number_format($programa['preco_dia'], 2, ',', '.') . ' (diário).')) ?>" target="_blank"><i class="fab fa-whatsapp"></i> Pedir pelo WhatsApp</a>
    </div>
    <div class="detalhe-form">
      <h3>Fazer pedido de aluguel</h3>
      <form method="post" action="alugar.php" class="form">
        <input type="hidden" name="programa_id" value="<?= (int)$programa['id'] ?>">
        <label>Seu nome
          <input type="text" name="cliente_nome" required placeholder="Ex.: João Silva">
        </label>
        <label>WhatsApp / E-mail
          <input type="text" name="cliente_contato" required placeholder="(11) 99999-9999">
        </label>
        <label>Plano
          <select name="plano">
            <option value="dia">Diário — 1 dia — <?= fmt_moeda($programa['preco_dia']) ?></option>
            <option value="semana">Semanal — 7 dias — <?= fmt_moeda($programa['preco_semana']) ?></option>
            <option value="mes">Mensal — 30 dias — <?= fmt_moeda($programa['preco_mes']) ?></option>
          </select>
        </label>
        <label>Observações (opcional)
          <textarea name="observacao" rows="3" placeholder="Alguma preferência?"></textarea>
        </label>
        <button type="submit" class="btn btn-primario btn-bloco">Solicitar aluguel</button>
        <p class="form-obs">Você receberá as instruções de pagamento e a ativação pelo WhatsApp.</p>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/incluir/site_rodape.php'; ?>