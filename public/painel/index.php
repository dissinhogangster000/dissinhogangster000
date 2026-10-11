<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$hoje = date('Y-m-d');
$total_agendamentos = (int)$pdo->query('SELECT COUNT(*) FROM agendamentos')->fetchColumn();
$agendamentos_hoje = (int)$pdo->query('SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = CURDATE()')->fetchColumn();
$confirmados_hoje = (int)$pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = CURDATE() AND status = 'confirmado'")->fetchColumn();
$pendentes = (int)$pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente'")->fetchColumn();
$realizados_mes = (int)$pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'realizado' AND MONTH(data_agendamento) = MONTH(CURDATE()) AND YEAR(data_agendamento) = YEAR(CURDATE())")->fetchColumn();
$receita_mes = (float)$pdo->query("SELECT COALESCE(SUM(valor), 0) FROM agendamentos WHERE status IN ('confirmado','realizado') AND MONTH(data_agendamento) = MONTH(CURDATE()) AND YEAR(data_agendamento) = YEAR(CURDATE())")->fetchColumn();

$proximos = $pdo->prepare('SELECT a.*, s.nome AS servico FROM agendamentos a LEFT JOIN servicos s ON s.id = a.servico_id WHERE a.data_agendamento >= CURDATE() AND a.status IN ("pendente","confirmado") ORDER BY a.data_agendamento, a.hora_inicio LIMIT 10');
$proximos->execute();
$proximos = $proximos->fetchAll();

$recentes = $pdo->query('SELECT a.*, s.nome AS servico FROM agendamentos a LEFT JOIN servicos s ON s.id = a.servico_id ORDER BY a.criado_em DESC LIMIT 8')->fetchAll();

painel_cabecalho($pdo, 'Dashboard', 'dashboard');
?>
<div class="cartoes-stats">
  <div class="stat">
    <div class="stat-icone" style="background:#6366f1"><i class="fas fa-calendar-alt"></i></div>
    <div><small>Agendamentos hoje</small><strong><?= $agendamentos_hoje ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#10b981"><i class="fas fa-check-circle"></i></div>
    <div><small>Confirmados hoje</small><strong><?= $confirmados_hoje ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#f59e0b"><i class="fas fa-clock"></i></div>
    <div><small>Pendentes</small><strong><?= $pendentes ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#3b82f6"><i class="fas fa-calendar-check"></i></div>
    <div><small>Realizados no mês</small><strong><?= $realizados_mes ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#8b5cf6"><i class="fas fa-sack-dollar"></i></div>
    <div><small>Receita no mês</small><strong><?= fmt_moeda($receita_mes) ?></strong></div>
  </div>
  <div class="stat">
    <div class="stat-icone" style="background:#0ea5e9"><i class="fas fa-list-ul"></i></div>
    <div><small>Total de agendamentos</small><strong><?= $total_agendamentos ?></strong></div>
  </div>
</div>

<div class="cartao-painel">
  <div class="cartao-painel-titulo">
    <h2>Próximos agendamentos</h2>
    <a class="btn btn-secundario btn-pequeno" href="agendamento-form.php"><i class="fas fa-plus"></i> Novo agendamento</a>
  </div>
  <?php if (!$proximos): ?>
    <p class="texto-vazio">Nenhum agendamento pendente ou confirmado para os próximos dias.</p>
  <?php else: ?>
  <table class="tabela">
    <thead><tr><th>Paciente</th><th>Contato</th><th>Serviço</th><th>Data/Hora</th><th>Valor</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
      <?php foreach ($proximos as $a): ?>
      <tr>
        <td><strong><?= e($a['cliente_nome']) ?></strong></td>
        <td><?= e($a['cliente_telefone']) ?><br><small><?= e($a['cliente_email']) ?></small></td>
        <td><?= e($a['servico'] ?: '—') ?></td>
        <td><?= fmt_data($a['data_agendamento']) ?> | <?= fmt_hora($a['hora_inicio']) ?> - <?= fmt_hora($a['hora_fim']) ?></td>
        <td><?= fmt_moeda($a['valor']) ?></td>
        <td><span class="status status-<?= e($STATUS_AGENDAMENTO[$a['status']]['cor'] ?? 'cinza') ?>"><?= e($STATUS_AGENDAMENTO[$a['status']]['label'] ?? $a['status']) ?></span></td>
        <td class="acoes">
          <a class="btn-icone" href="agendamento-form.php?id=<?= (int)$a['id'] ?>" title="Editar"><i class="fas fa-pen"></i></a>
          <a class="btn-icone js-whats" href="#" data-text="<?= e(gerar_mensagem_whats($a, $a['servico'])) ?>" title="Enviar WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=confirmado" title="Confirmar"><i class="fas fa-check"></i></a>
          <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=realizado" title="Marcar realizado"><i class="fas fa-calendar-check"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php painel_rodape(); ?>