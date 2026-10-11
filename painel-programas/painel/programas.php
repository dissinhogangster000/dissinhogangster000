<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$programas = $pdo->query(
    'SELECT p.*, c.nome AS categoria FROM programas p LEFT JOIN categorias c ON c.id = p.categoria_id ORDER BY p.nome ASC'
)->fetchAll();

painel_cabecalho($pdo, 'Programas', 'programas');
?>

<div class="cartao-painel">
  <div class="cartao-painel-titulo">
    <h2>Lista de programas (<?= count($programas) ?>)</h2>
    <a class="btn btn-primario btn-pequeno" href="programa-form.php"><i class="fas fa-plus"></i> Novo programa</a>
  </div>
  <div class="tabela-wrap">
  <table class="tabela">
    <thead><tr><th>Ícone</th><th>Nome</th><th>Categoria</th><th>Diário</th><th>Semana</th><th>Mês</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
      <?php if (!$programas): ?>
        <tr><td colspan="8" class="texto-vazio">Nenhum programa cadastrado.</td></tr>
      <?php endif; ?>
      <?php foreach ($programas as $p): ?>
      <tr>
        <td class="td-icone"><?= $p['icone'] ?: '📦' ?></td>
        <td><strong><?= e($p['nome']) ?></strong><?= (int)$p['destaque'] === 1 ? ' <span class="selo">Destaque</span>' : '' ?></td>
        <td><?= e($p['categoria'] ?? '—') ?></td>
        <td><?= fmt_moeda($p['preco_dia']) ?></td>
        <td><?= fmt_moeda($p['preco_semana']) ?></td>
        <td><?= fmt_moeda($p['preco_mes']) ?></td>
        <td><span class="status status-<?= (int)$p['ativo'] === 1 ? 'verde' : 'vermelho' ?>"><?= (int)$p['ativo'] === 1 ? 'Ativo' : 'Inativo' ?></span></td>
        <td class="acoes">
          <a class="btn-icone" href="programa-form.php?id=<?= (int)$p['id'] ?>" title="Editar"><i class="fas fa-pen"></i></a>
          <a class="btn-icone excluir" href="programa-excluir.php?id=<?= (int)$p['id'] ?>" title="Excluir" onclick="return confirm('Excluir o programa e seus aluguéis?')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php painel_rodape(); ?>