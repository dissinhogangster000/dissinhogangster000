<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$aluguel = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM alugueis WHERE id = ?');
    $stmt->execute([$id]);
    $aluguel = $stmt->fetch();
    if (!$aluguel) redirecionar('alugueis.php', 'Aluguel não encontrado.', 'erro');
}

$programas = $pdo->query('SELECT id, nome, icone FROM programas WHERE ativo = 1 ORDER BY nome')->fetchAll();

$programa_id = $aluguel['programa_id'] ?? '';
$cliente     = $aluguel['cliente_nome'] ?? '';
$contato     = $aluguel['cliente_contato'] ?? '';
$plano       = $aluguel['plano'] ?? 'dia';
$valor       = $aluguel ? valor_para_input($aluguel['valor']) : '';
$data_inicio = $aluguel['data_inicio'] ?? date('Y-m-d');
$data_fim    = $aluguel['data_fim'] ?? '';
$codigo      = $aluguel['codigo_acesso'] ?? '';
$status      = $aluguel['status'] ?? 'ativo';
$observacao  = $aluguel['observacao'] ?? '';

painel_cabecalho($pdo, $aluguel ? 'Editar aluguel #' . $id : 'Novo aluguel', 'alugueis');
?>

<div class="cartao-painel cartao-form">
  <form method="post" action="aluguel-salvar.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-grade">
      <label>Programa
        <select name="programa_id" required id="programa-select">
          <?php foreach ($programas as $p): ?>
            <option value="<?= (int)$p['id'] ?>" data-dia="<?= e($p['preco_dia']) ?>" data-semana="<?= e($p['preco_semana']) ?>" data-mes="<?= e($p['preco_mes']) ?>" <?= (int)$programa_id === (int)$p['id'] ? 'selected' : '' ?>><?= $p['icone'] ?: '📦' ?> <?= e($p['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Plano
        <select name="plano" id="plano-select">
          <?php foreach ($PLANOS as $chave => $info): ?>
            <option value="<?= e($chave) ?>" <?= $plano === $chave ? 'selected' : '' ?>><?= e($info['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Valor (R$)
        <input type="text" name="valor" id="valor-input" required value="<?= e($valor) ?>" placeholder="0,00">
      </label>
      <label>Nome do cliente
        <input type="text" name="cliente_nome" required value="<?= e($cliente) ?>" placeholder="João Silva">
      </label>
      <label>Contato (WhatsApp/E-mail)
        <input type="text" name="cliente_contato" required value="<?= e($contato) ?>" placeholder="(11) 99999-9999">
      </label>
      <label>Data de início
        <input type="date" name="data_inicio" required value="<?= e($data_inicio) ?>">
      </label>
      <label>Data final
        <input type="date" name="data_fim" required value="<?= e($data_fim) ?>">
      </label>
      <label>Status
        <select name="status">
          <?php foreach ($STATUS_ALUGUEL as $chave => $info): ?>
            <option value="<?= e($chave) ?>" <?= $status === $chave ? 'selected' : '' ?>><?= e($info['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label class="bloco">Código de acesso / desbloqueio
      <input type="text" name="codigo_acesso" value="<?= e($codigo) ?>" placeholder="Enviado para o cliente">
    </label>
    <label class="bloco">Observações
      <textarea name="observacao" rows="3" placeholder="Anotações internas..."><?= e($observacao) ?></textarea>
    </label>
    <div class="form-botoes">
      <button class="btn btn-primario" type="submit"><i class="fas fa-save"></i> Salvar</button>
      <a class="btn btn-secundario" href="alugueis.php">Cancelar</a>
    </div>
  </form>
</div>

<?php painel_rodape(); ?>