<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$servicos = $pdo->query('SELECT * FROM servicos ORDER BY nome')->fetchAll();

painel_cabecalho($pdo, 'Serviços', 'servicos');
?>
<div class="cartao-painel">
  <div class="cartao-painel-titulo">
    <h2>Gerenciar serviços</h2>
    <a class="btn btn-primario btn-pequeno" href="servico-form.php"><i class="fas fa-plus"></i> Novo serviço</a>
  </div>
  <div class="tabela-wrap">
    <table class="tabela">
      <thead><tr><th>Nome</th><th>Duração</th><th>Preço</th><th>Status</th><th>Ações</th></tr></thead>
      <tbody>
        <?php if (!$servicos): ?>
          <tr><td colspan="5" class="texto-vazio">Nenhum serviço cadastrado.</td></tr>
        <?php endif; ?>
        <?php foreach ($servicos as $s): ?>
        <tr>
          <td><strong><?= e($s['nome']) ?></strong><br><small><?= e($s['descricao']) ?></small></td>
          <td><?= (int)$s['duracao_min'] ?> min</td>
          <td><?= fmt_moeda($s['preco']) ?></td>
          <td><?= $s['ativo'] ? '<span class="status status-verde">Ativo</span>' : '<span class="status status-cinza">Inativo</span>' ?></td>
          <td class="acoes">
            <a class="btn-icone" href="servico-form.php?id=<?= (int)$s['id'] ?>"><i class="fas fa-pen"></i></a>
            <a class="btn-icone excluir" href="servico-excluir.php?id=<?= (int)$s['id'] ?>" onclick="return confirm('Excluir este serviço?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php painel_rodape(); ?>