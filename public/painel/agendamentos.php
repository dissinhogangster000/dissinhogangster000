<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$filtro = $_GET['status'] ?? '';
$filtro_ok = isset($STATUS_AGENDAMENTO[$filtro]);
$busca = trim($_GET['q'] ?? '');

$sql = 'SELECT a.*, s.nome AS servico FROM agendamentos a LEFT JOIN servicos s ON s.id = a.servico_id WHERE 1=1';
$params = [];
if ($filtro_ok) {
    $sql .= ' AND a.status = ?';
    $params[] = $filtro;
}
if ($busca !== '') {
    $sql .= ' AND (a.cliente_nome LIKE ? OR a.cliente_telefone LIKE ?)';
    $like = '%' . $busca . '%';
    $params[] = $like; $params[] = $like;
}
$sql .= ' ORDER BY a.data_agendamento DESC, a.hora_inicio DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$agendamentos = $stmt->fetchAll();

painel_cabecalho($pdo, 'Agendamentos', 'agendamentos');
?>
<div class="cartao-painel">
  <div class="cartao-painel-titulo">
    <div class="filtros">
      <a href="agendamentos.php" class="filtro <?= !$filtro_ok && $busca === '' ? 'ativo' : '' ?>">Todos</a>
      <?php foreach ($STATUS_AGENDAMENTO as $chave => $info): ?>
        <a href="agendamentos.php?status=<?= e($chave) ?>" class="filtro <?= $filtro === $chave ? 'ativo' : '' ?>"><?= e($info['label']) ?></a>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      <form method="get" style="display:flex;gap:8px">
        <input type="hidden" name="status" value="<?= e($filtro) ?>">
        <input type="text" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome ou telefone" style="min-width:260px">
        <button class="btn btn-secundario btn-pequeno" type="submit"><i class="fas fa-search"></i> Buscar</button>
      </form>
      <a class="btn btn-primario btn-pequeno" href="agendamento-form.php"><i class="fas fa-plus"></i> Novo agendamento</a>
    </div>
  </div>
  <div class="tabela-wrap">
  <table class="tabela">
    <thead><tr><th>Paciente</th><th>Contato</th><th>Serviço</th><th>Data/Hora</th><th>Valor</th><th>Status</th><th>Origem</th><th>Ações</th></tr></thead>
    <tbody>
      <?php if (!$agendamentos): ?>
        <tr><td colspan="8" class="texto-vazio">Nenhum agendamento encontrado.</td></tr>
      <?php endif; ?>
      <?php foreach ($agendamentos as $a): ?>
      <tr>
        <td><strong><?= e($a['cliente_nome']) ?></strong></td>
        <td><?= e($a['cliente_telefone']) ?><br><small><?= e($a['cliente_email']) ?></small></td>
        <td><?= e($a['servico'] ?: '—') ?></td>
        <td><?= fmt_data($a['data_agendamento']) ?> • <?= fmt_hora($a['hora_inicio']) ?> - <?= fmt_hora($a['hora_fim']) ?></td>
        <td><?= fmt_moeda($a['valor']) ?></td>
        <td><span class="status status-<?= e($STATUS_AGENDAMENTO[$a['status']]['cor'] ?? 'cinza') ?>"><?= e($STATUS_AGENDAMENTO[$a['status']]['label'] ?? $a['status']) ?></span></td>
        <td><?= e($a['origem']) ?></td>
        <td class="acoes">
          <a class="btn-icone" href="agendamento-form.php?id=<?= (int)$a['id'] ?>" title="Editar"><i class="fas fa-pen"></i></a>
          <a class="btn-icone js-whats" href="#" data-text="<?= e(gerar_mensagem_whats($a, $a['servico'])) ?>" title="Enviar WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <?php if ($a['status'] === 'pendente'): ?>
            <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=confirmado" title="Confirmar"><i class="fas fa-check"></i></a>
          <?php endif; ?>
          <?php if ($a['status'] !== 'realizado'): ?>
            <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=realizado" title="Marcar realizado"><i class="fas fa-calendar-check"></i></a>
          <?php endif; ?>
          <?php if (!in_array($a['status'], ['cancelado','ausente'])): ?>
            <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=cancelado" title="Cancelar"><i class="fas fa-ban"></i></a>
            <a class="btn-icone" href="agendamento-status.php?id=<?= (int)$a['id'] ?>&status=ausente" title="Ausente"><i class="fas fa-user-slash"></i></a>
          <?php endif; ?>
          <a class="btn-icone excluir" href="agendamento-excluir.php?id=<?= (int)$a['id'] ?>" title="Excluir" onclick="return confirm('Excluir este agendamento?')"><i class="fas fa-trash"></i></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php painel_rodape(); ?>