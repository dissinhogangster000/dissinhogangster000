<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();
marcar_vencidos($pdo);

$total_programas = (int)$pdo->query("SELECT COUNT(*) FROM programas WHERE ativo = 1")->fetchColumn();
$total_alugueis  = (int)$pdo->query('SELECT COUNT(*) FROM alugueis')->fetchColumn();
$alugueis_ativos = (int)$pdo->query("SELECT COUNT(*) FROM alugueis WHERE status = 'ativo'")->fetchColumn();
$alugueis_hoje   = (int)$pdo->query('SELECT COUNT(*) FROM alugueis WHERE data_inicio = CURDATE()')->fetchColumn();
$receita = (float)$pdo->query("SELECT COALESCE(SUM(valor), 0) FROM alugueis WHERE status IN ('ativo', 'concluido')")->fetchColumn();
$receita_mes = (float)$pdo->query("SELECT COALESCE(SUM(valor), 0) FROM alugueis WHERE MONTH(data_inicio) = MONTH(CURDATE()) AND YEAR(data_inicio) = YEAR(CURDATE())")->fetchColumn();

$recentes = $pdo->query(
    'SELECT a.*, p.nome AS programa, p.icone FROM alugueis a INNER JOIN programas p ON p.id = a.programa_id ORDER BY a.criado_em DESC LIMIT 8'
)->fetchAll();

painel_cabecalho($pdo, 'Dashboard', 'dashboard');
?>

<div class="cartoes-stats">
  <div class="stat">
    <div class="stat-icone" style="background:#6366f1"><i class="fas fa-box-open"></i></div>
    <div><small>Programas ativos</small><strong><?= $total_programas ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#10b981"><i class="fas fa-handshake"></i></div>
    <div><small>Aluguéis ativos</small><strong><?= $alugueis_ativos ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#f59e0b"><i class="fas fa-calendar-day"></i></div>
    <div><small>Pedidos hoje</small><strong><?= $alugueis_hoje ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#ec4899"><i class="fas fa-sack-dollar"></i></div>
    <div><small>Receita no mês</small><strong><?= fmt_moeda($receita_mes) ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#8b5cf6"><i class="fas fa-coins"></i></div>
    <div><small>Receita total</small><strong><?= fmt_moeda($receita) ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#0ea5e9"><i class="fas fa-clock"></i></div>
    <div><small>Total de aluguéis</small><strong><?= $total_alugueis ?></strong></div>
  </div>
</div>

<div class="cartao-painel">
  <div class="cartao-painel-titulo">
    <h2>Últimos aluguéis</h2>
    <a class="btn btn-secundario btn-peg btn-pequeno" href="alugueis.php">Ver todos</a>
  </div>
  <?php if (!$recentes): ?>
    <p class="texto-vazio">Nenhum aluguel registrado ainda. Os pedidos do site aparecerão aqui.</p>
  <?php else: ?>
  <table class="tabela">
    <thead><tr><th>#</th><th>Cliente</th><th>Programa</th><th>Plano</th><th>Valor</th><th>Vigência</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($recentes as $a): ?>
      <tr>
        <td>#<?= (int)$a['id'] ?></td>
        <td><strong><?= e($a['cliente_nome']) ?></strong><br><small><?= e($a['cliente_contato']) ?></small></td>
        <td><?= $a['icone'] ?: '' ?> <?= e($a['programa']) ?></td>
        <td><?= e($PLANOS[$a['plano']]['label'] ?? $a['plano']) ?></td>
        <td><strong><?= fmt_moeda($a['valor']) ?></strong></td>
        <td><small><?= fmt_data($a['data_inicio']) ?> — <?= fmt_data($a['data_fim']) ?></small></td>
        <td><span class="status status-<?= e($STATUS_ALUGUEL[$a['status']]['cor'] ?? 'azul') ?>"><?= e($STATUS_ALUGUEL[$a['status']]['label'] ?? $a['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<?php painel_rodape(); ?>