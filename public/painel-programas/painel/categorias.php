<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    if ($nome === '') {
        $erro = 'Digite o nome da categoria.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (?)');
            $stmt->execute([$nome]);
            redirecionar('categorias.php', 'Categoria criada.');
        } catch (PDOException $e) {
            $erro = 'Esta categoria já existe.';
        }
    }
}

$categorias = $pdo->query(
    'SELECT c.*, (SELECT COUNT(*) FROM programas p WHERE p.categoria_id = c.id) AS total FROM categorias c ORDER BY c.nome'
)->fetchAll();

painel_cabecalho($pdo, 'Categorias', 'categorias');
?>

<div class="grid-dupla">
  <div class="cartao-painel">
    <div class="cartao-painel-titulo"><h2>Nova categoria</h2></div>
    <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
    <form method="post" class="form-inline">
      <input type="text" name="nome" required placeholder="Ex.: Streaming">
      <button class="btn btn-primario" type="submit"><i class="fas fa-plus"></i> Adicionar</button>
    </form>
  </div>

  <div class="cartao-painel">
    <div class="cartao-painel-titulo"><h2>Categorias (<?= count($categorias) ?>)</h2></div>
    <table class="tabela">
      <thead><tr><th>Nome</th><th>Programas</th><th></th></tr></thead>
      <tbody>
        <?php if (!$categorias): ?>
          <tr><td colspan="3" class="texto-vazio">Nenhuma categoria.</td></tr>
        <?php endif; ?>
        <?php foreach ($categorias as $c): ?>
        <tr>
          <td><strong><?= e($c['nome']) ?></strong></td>
          <td><?= (int)$c['total'] ?></td>
          <td class="acoes">
            <?php if ((int)$c['total'] === 0): ?>
            <a class="btn-icone excluir" href="categoria-excluir.php?id=<?= (int)$c['id'] ?>" title="Excluir" onclick="return confirm('Excluir esta categoria?')"><i class="fas fa-trash"></i></a>
            <?php else: ?><span class="texto-suave">em uso</span><?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php painel_rodape(); ?>