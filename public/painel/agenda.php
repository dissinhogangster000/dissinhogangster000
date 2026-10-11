<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$data_selecionada = $_GET['data'] ?? date('Y-m-d');
$agendamentos = buscar_agendamentos_por_data($pdo, $data_selecionada);

$hora_inicial = '08:00';
$hora_final = '19:00';
$intervalo_min = 30;

function hora_lista($inicio, $fim, $intervalo) {
    $lista = [];
    $atual = strtotime($inicio);
    $limite = strtotime($fim);
    while ($atual < $limite) {
        $lista[] = date('H:i', $atual);
        $atual = strtotime('+' . (int)$intervalo . ' minutes', $atual);
    }
    return $lista;
}

$slots = hora_lista($hora_inicial, $hora_final, $intervalo_min);
$mapa = [];
foreach ($agendamentos as $a) {
    $hin = substr($a['hora_inicio'], 0, 5);
    $mapa[$hin] = $a;
}

painel_cabecalho($pdo, 'Agenda do Dia', 'agenda');
?>
<div class="agenda-grade">
  <div class="agenda-lateral">
    <div class="agenda-calendario">
      <form method="get">
        <label>Selecionar data
          <input type="date" name="data" value="<?= e($data_selecionada) ?>" onchange="this.form.submit()">
        </label>
      </form>
    </div>
    <h3>Resumo do dia (<?= fmt_data($data_selecionada) ?>)</h3>
    <div class="agenda-lista">
      <?php if (!$agendamentos): ?>
        <p class="texto-vazio">Nenhum agendamento para esta data.</p>
      <?php else: ?>
        <?php foreach ($agendamentos as $a): ?>
        <div class="agenda-item <?= e($a['status']) ?>">
          <div class="agenda-cab">
            <strong><?= e($a['cliente_nome']) ?></strong>
            <span class="status status-<?= e($STATUS_AGENDAMENTO[$a['status']]['cor'] ?? 'cinza') ?>"><?= e($STATUS_AGENDAMENTO[$a['status']]['label'] ?? $a['status']) ?></span>
          </div>
          <small><?= e($a['servico'] ?: 'Consulta') ?> • <?= fmt_hora($a['hora_inicio']) ?> - <?= fmt_hora($a['hora_fim']) ?></small>
          <small><?= e($a['cliente_telefone']) ?></small>
          <div class="acoes" style="margin-top:6px">
            <a class="btn-icone" href="agendamento-form.php?id=<?= (int)$a['id'] ?>" title="Editar"><i class="fas fa-pen"></i></a>
            <a class="btn-icone js-whats" href="#" data-text="<?= e(gerar_mensagem_whats($a, $a['servico'])) ?>" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=confirmado&voltar=agenda&data=<?= e($data_selecionada) ?>" title="Confirmar"><i class="fas fa-check"></i></a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <a class="btn btn-primario" href="agendamento-form.php?data=<?= e($data_selecionada) ?>"><i class="fas fa-plus"></i> Novo agendamento</a>
  </div>

  <div class="agenda-main">
    <div class="agenda-header">
      <h2>Grade de horários — <?= fmt_data($data_selecionada) ?></h2>
      <div class="acoes">
        <a class="btn btn-secundario btn-pequeno" href="agenda.php?data=<?= date('Y-m-d', strtotime($data_selecionada.' -1 day')) ?>">Anterior</a>
        <a class="btn btn-secundario btn-pequeno" href="agenda.php?data=<?= date('Y-m-d') ?>">Hoje</a>
        <a class="btn btn-secundario btn-pequeno" href="agenda.php?data=<?= date('Y-m-d', strtotime($data_selecionada.' +1 day')) ?>">Próximo</a>
      </div>
    </div>
    <div class="grade-horas">
      <?php foreach ($slots as $slot): ?>
        <?php $a = $mapa[$slot] ?? null; ?>
        <div class="hora-card <?= $a ? 'ocupada' : '' ?>">
          <div class="hora-titulo">
            <strong><?= $slot ?></strong>
            <?php if ($a): ?>
              <span class="status status-<?= e($STATUS_AGENDAMENTO[$a['status']]['cor'] ?? 'cinza') ?>"><?= e($STATUS_AGENDAMENTO[$a['status']]['label'] ?? $a['status']) ?></span>
            <?php else: ?>
              <span class="status">Livre</span>
            <?php endif; ?>
          </div>
          <?php if ($a): ?>
            <div class="paciente-info">
              <strong><?= e($a['cliente_nome']) ?></strong>
              <small><?= e($a['servico'] ?: 'Consulta') ?> • <?= fmt_hora($a['hora_inicio']) ?> - <?= fmt_hora($a['hora_fim']) ?></small>
              <small><?= e($a['cliente_telefone']) ?></small>
              <?php if ($a['cliente_email']): ?><small><?= e($a['cliente_email']) ?></small><?php endif; ?>
            </div>
            <div class="acoes">
              <a class="btn-icone" href="agendamento-form.php?id=<?= (int)$a['id'] ?>" title="Editar"><i class="fas fa-pen"></i></a>
              <a class="btn-icone js-whats" href="#" data-text="<?= e(gerar_mensagem_whats($a, $a['servico'])) ?>" title="Enviar WhatsApp"><i class="fab fa-whatsapp"></i></a>
              <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=realizado&voltar=agenda&data=<?= e($data_selecionada) ?>" title="Realizado"><i class="fas fa-check-double"></i></a>
              <a class="btn-icone excluir" href="agendamento-excluir.php?id=<?= (int)$a['id'] ?>&voltar=agenda&data=<?= e($data_selecionada) ?>" title="Excluir" onclick="return confirm('Excluir este agendamento?')"><i class="fas fa-trash"></i></a>
            </div>
          <?php else: ?>
            <p class="texto-vazio" style="padding:8px 0">Horário disponível</p>
            <a class="btn btn-secundario btn-pequeno" href="agendamento-form.php?data=<?= e($data_selecionada) ?>&hora=<?= e($slot) ?>"><i class="fas fa-plus"></i> Marcar</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php painel_rodape(); ?>