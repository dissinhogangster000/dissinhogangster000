<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$agendamento = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM agendamentos WHERE id = ?');
    $stmt->execute([$id]);
    $agendamento = $stmt->fetch();
    if (!$agendamento) redirecionar('agendamentos.php', 'Agendamento não encontrado.', 'erro');
}

$servicos = listar_servicos($pdo);
$servico_id = $agendamento['servico_id'] ?? ($_GET['servico'] ?? '');
$cliente_nome = $agendamento['cliente_nome'] ?? '';
$cliente_telefone = $agendamento['cliente_telefone'] ?? '';
$cliente_email = $agendamento['cliente_email'] ?? '';
$data_agendamento = $agendamento['data_agendamento'] ?? ($_GET['data'] ?? date('Y-m-d'));
$hora_inicio = $agendamento['hora_inicio'] ?? ($_GET['hora'] ?? '');
$hora_fim = $agendamento['hora_fim'] ?? '';
$valor = $agendamento ? valor_para_input($agendamento['valor']) : '';
$status = $agendamento['status'] ?? 'pendente';
$observacao = $agendamento['observacao'] ?? '';
$origem = $agendamento['origem'] ?? 'painel';

painel_cabecalho($pdo, $agendamento ? 'Editar agendamento #' . $id : 'Novo agendamento', 'agendamentos');
?>
<div class="cartao-painel cartao-form">
  <form method="post" action="agendamento-salvar.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="origem" value="<?= e($origem) ?>">
    <div class="form-grade">
      <label>Serviço
        <select name="servico_id" id="servico-select">
          <option value="">Selecione um serviço</option>
          <?php foreach ($servicos as $s): ?>
            <option value="<?= (int)$s['id'] ?>" data-duracao="<?= (int)$s['duracao_min'] ?>" data-preco="<?= e($s['preco']) ?>" <?= (int)$servico_id === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['nome']) ?> - <?= e($s['duracao_min']) ?>min - <?= fmt_moeda($s['preco']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Nome do paciente
        <input type="text" name="cliente_nome" required value="<?= e($cliente_nome) ?>" placeholder="Nome completo">
      </label>
      <label>Telefone/WhatsApp
        <input type="text" name="cliente_telefone" required value="<?= e($cliente_telefone) ?>" placeholder="(11) 99999-9999">
      </label>
      <label>E-mail
        <input type="email" name="cliente_email" value="<?= e($cliente_email) ?>" placeholder="email@exemplo.com">
      </label>
      <label>Data do agendamento
        <input type="date" name="data_agendamento" required value="<?= e($data_agendamento) ?>">
      </label>
      <label>Horário de início
        <input type="time" name="hora_inicio" id="hora-inicio" required value="<?= e($hora_inicio) ?>">
      </label>
      <label>Horário de término
        <input type="time" name="hora_fim" id="hora-fim" required value="<?= e($hora_fim) ?>">
      </label>
      <label>Valor (R$)
        <input type="text" name="valor" id="valor-input" required value="<?= e($valor) ?>" placeholder="0,00">
      </label>
      <label>Status
        <select name="status">
          <?php foreach ($STATUS_AGENDAMENTO as $chave => $info): ?>
            <option value="<?= e($chave) ?>" <?= $status === $chave ? 'selected' : '' ?>><?= e($info['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label class="bloco">Observações
      <textarea name="observacao" rows="3" placeholder="Anotações sobre o paciente ou agendamento..."><?= e($observacao) ?></textarea>
    </label>
    <div class="form-botoes">
      <button class="btn btn-primario" type="submit"><i class="fas fa-save"></i> Salvar agendamento</button>
      <a class="btn btn-secundario" href="agendamentos.php">Cancelar</a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const servico = document.getElementById('servico-select');
  const horaInicio = document.getElementById('hora-inicio');
  const horaFim = document.getElementById('hora-fim');
  const valorInput = document.getElementById('valor-input');
  
  function calcularFim() {
    const opt = servico.options[servico.selectedIndex];
    if (!opt || !opt.dataset.duracao) return;
    const duracao = parseInt(opt.dataset.duracao);
    const preco = opt.dataset.preco;
    if (preco && !valorInput.value) {
      valorInput.value = parseFloat(preco).toFixed(2).replace('.', ',');
    }
    if (horaInicio.value) {
      const [h,m] = horaInicio.value.split(':');
      const data = new Date();
      data.setHours(parseInt(h), parseInt(m) + duracao, 0, 0);
      const fim = String(data.getHours()).padStart(2,'0') + ':' + String(data.getMinutes()).padStart(2,'0');
      horaFim.value = fim;
    }
  }
  servico.addEventListener('change', calcularFim);
  horaInicio.addEventListener('change', calcularFim);
});
</script>
<?php painel_rodape(); ?>