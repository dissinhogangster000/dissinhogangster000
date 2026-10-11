<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$servico = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM servicos WHERE id = ?');
    $stmt->execute([$id]);
    $servico = $stmt->fetch();
    if (!$servico) redirecionar('servicos.php', 'Serviço não encontrado.', 'erro');
}

$nome = $servico['nome'] ?? '';
$descricao = $servico['descricao'] ?? '';
$duracao = $servico['duracao_min'] ?? 60;
$preco = $servico ? valor_para_input($servico['preco']) : '150,00';
$ativo = $servico['ativo'] ?? 1;

painel_cabecalho($pdo, $servico ? 'Editar serviço' : 'Novo serviço', 'servicos');
?>
<div class="cartao-painel cartao-form">
  <form method="post" action="servico-salvar.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-grade">
      <label>Nome do serviço
        <input type="text" name="nome" required value="<?= e($nome) ?>">
      </label>
      <label>Duração (minutos)
        <input type="number" name="duracao_min" required value="<?= (int)$duracao ?>" min="15" step="5">
      </label>
      <label>Preço (R$)
        <input type="text" name="preco" required value="<?= e($preco) ?>">
      </label>
      <label>Status
        <select name="ativo">
          <option value="1" <?= $ativo ? 'selected' : '' ?>>Ativo</option>
          <option value="0" <?= !$ativo ? 'selected' : '' ?>>Inativo</option>
        </select>
      </label>
    </div>
    <label class="bloco">Descrição
      <textarea name="descricao" rows="3"><?= e($descricao) ?></textarea>
    </label>
    <div class="form-botoes">
      <button class="btn btn-primario" type="submit"><i class="fas fa-save"></i> Salvar</button>
      <a class="btn btn-secundario" href="servicos.php">Cancelar</a>
    </div>
  </form>
</div>
<?php painel_rodape(); ?>