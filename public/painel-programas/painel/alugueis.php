<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();
marcar_vencidos($pdo);

$filtro = $_GET['status'] ?? '';
$filtro_ok = isset($STATUS_ALUGUEL[$filtro]);

$sql = 'SELECT a.*, p.nome AS programa, p.icone FROM alugueis a INNER JOIN programas p ON p.id = a.programa_id';
$params = [];
if ($filtro_ok) {
    $sql .= ' WHERE a.status = ?';
    $params[] = $filtro;
}
$sql .= ' ORDER BY a.criado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$alugueis = $stmt->fetchAll();

painel_cabecalho($pdo, 'Aluguéis', 'alugueis');
?>

<div class="cartao-painel">
  <div class="cartao-painel-titulo">
    <div class="filtros">
      <a href="alugueis.php" class="filtro <?= !$filtro_ok ? 'ativo' : '' ?>">Todos</a>
      <?php foreach ($STATUS_ALUGUEL as $chave => $info): ?>
        <a href="alugueis.php?status=<?= e($chave) ?>" class="filtro <?= $filtro === $chave ? 'ativo' : '' ?>"><?= e($info['label']) ?></a>
      <?php endforeach; ?>
    </div>
    <a class="btn btn-primario btn-pequeno" href="aluguel-form.php"><i class="fas fa-plus"></i> Novo aluguel</a>
  </div>
  <div class="tabela-wrap">
  <table class="tabela">
    <thead><tr><th>#</th><th>Cliente</th><th>Programa</th><th>Plano</th><th>Valor</th><th>Vigência</th><th>Código</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
      <?php if (!$alugueis): ?>
        <tr><td colspan="9" class="texto-vazio">Nenhum aluguel<?= $filtro_ok ? ' com este status' : '' ?>.</td></tr>
      <?php endif; ?>
      <?php foreach ($alugueis as $a): ?>
      <tr>
        <td>#<?= (int)$a['id'] ?></td>
        <td><strong><?= e($a['cliente_nome']) ?></strong><br><small><?= e($a['cliente_contato']) ?></small></td>
        <td><?= $a['icone'] ?: '' ?> <?= e($a['programa']) ?></td>
        <td><?= e($PLANOS[$a['plano']]['label'] ?? $a['plano']) ?></td>
        <td><strong><?= fmt_moeda($a['valor']) ?></strong></td>
        <td><small><?= fmt_data($a['data_inicio']) ?><br>até <?= fmt_data($a['data_fim']) ?></small></td>
        <td><?= $a['codigo_acesso'] !== '' ? '<code class="codigo">' . e($a['codigo_acesso']) . '</code>' : '—' ?></td>
        <td>
          <span class="status status-<?= e($STATUS_ALUGUEL[$a['status']]['cor'] ?? 'azul') ?>"><?= e($STATUS_ALUGUEL[$a['status']]['label'] ?? $a['status']) ?></span>
          <?php if ($a['status'] === 'ativo' && !ativo_hoje($a['data_fim'])): ?>
            <br><small class="texto-suave">fim em breve</small>
          <?php endif; ?>
        </td>
        <td class="acoes">
          <a class="btn-icone" href="aluguel-form.php?id=<?= (int)$a['id'] ?>" title="Editar"><i class="fas fa-pen"></i></a>
          <?php if ($a['status'] === 'ativo'): ?>
          <a class="btn-icone" href="aluguel-status.php?id=<?= (int)$a['id'] ?>&status=concluido&voltar=<?= e($filtro) ?>" title="Concluir"><i class="fas fa-check"></i></a>
          <a class="btn-icone" href="aluguel-status.php?id=<?= (int)$a['id'] ?>&status=cancelado&voltar=<?= e($filtro) ?>" title="Cancelar"><i class="fas fa-ban"></i></a>
          <?php endif; ?>
          <?php if ($a['status'] !== 'ativo'): ?>
          <a class="btn-icone" href="aluguel-status.php?id=<?= (int)$a['id'] ?>&status=ativo&voltar=<?= e($filtro) ?>" title="Reativar"><i class="fas fa-rotate-left"></i></a>
          <?php endif; ?>
          <a class="btn-icone excluir" href="aluguel-excluir.php?id=<?= (int)$a['id'] ?>" title="Excluir" onclick="return confirm('Excluir este aluguel?')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>

<?php painel_rodape(); ?>