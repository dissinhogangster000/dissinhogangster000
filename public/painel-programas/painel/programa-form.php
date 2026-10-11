<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$programa = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM programas WHERE id = ?');
    $stmt->execute([$id]);
    $programa = $stmt->fetch();
    if (!$programa) redirecionar('programas.php', 'Programa não encontrado.', 'erro');
}

$categorias = listar_categorias($pdo);

$nome       = $programa['nome'] ?? '';
$icone      = $programa['icone'] ?? '';
$descricao  = $programa['descricao'] ?? '';
$categoria_id = $programa['categoria_id'] ?? '';
$p_dia      = $programa ? valor_para_input($programa['preco_dia']) : '0,00';
$p_semana   = $programa ? valor_para_input($programa['preco_semana']) : '0,00';
$p_mes      = $programa ? valor_para_input($programa['preco_mes']) : '0,00';
$destaque   = (int)($programa['destaque'] ?? 0);
$ativo      = (int)($programa['ativo'] ?? 1);

painel_cabecalho($pdo, $programa ? 'Editar programa' : 'Novo programa', 'programas');
?>

<div class="cartao-painel cartao-form">
  <form method="post" action="programa-salvar.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="form-grade">
      <label>Nome do programa
        <input type="text" name="nome" required value="<?= e($nome) ?>" placeholder="Ex.: Netflix Premium">
      </label>
      <label>Ícone (emoji)
        <input type="text" name="icone" value="<?= e($icone) ?>" placeholder="📺" maxlength="8">
      </label>
      <label>Categoria
        <select name="categoria_id">
          <option value="">Sem categoria</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$categoria_id === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Preço diário (R$)
        <input type="text" name="preco_dia" required value="<?= e($p_dia) ?>" placeholder="0,00">
      </label>
      <label>Preço semanal (R$)
        <input type="text" name="preco_semana" required value="<?= e($p_semana) ?>" placeholder="0,00">
      </label>
      <label>Preço mensal (R$)
        <input type="text" name="preco_mes" required value="<?= e($p_mes) ?>" placeholder="0,00">
      </label>
    </div>
    <label class="bloco">Descrição
      <textarea name="descricao" rows="4" placeholder="O que o cliente recebe, como funciona..."><?= e($descricao) ?></textarea>
    </label>
    <div class="linha-checks">
      <label class="check"><input type="checkbox" name="destaque" value="1" <?= $destaque ? 'checked' : '' ?>> Marcar como destaque</label>
      <label class="check"><input type="checkbox" name="ativo" value="1" <?= $ativo ? 'checked' : '' ?>> Programa ativo no site</label>
    </div>
    <div class="form-botoes">
      <button class="btn btn-primario" type="submit"><i class="fas fa-save"></i> Salvar</button>
      <a class="btn btn-secundario" href="programas.php">Cancelar</a>
    </div>
  </form>
</div>

<?php painel_rodape(); ?>