<?php
require_once __DIR__ . '/incluir/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$programa_id  = (int)($_POST['programa_id'] ?? 0);
$cliente_nome = trim($_POST['cliente_nome'] ?? '');
$contato      = trim($_POST['cliente_contato'] ?? '');
$plano        = $_POST['plano'] ?? 'dia';
$observacao   = trim($_POST['observacao'] ?? '');

if (!isset($PLANOS[$plano])) $plano = 'dia';

$stmt = $pdo->prepare('SELECT * FROM programas WHERE id = ? AND ativo = 1');
$stmt->execute([$programa_id]);
$programa = $stmt->fetch();

if (!$programa || $cliente_nome === '' || $contato === '') {
    $titulo = 'Dados incompletos';
    $mensagem = 'Preencha todos os campos obrigatórios e tente novamente.';
    $mensagem_tipo = 'erro';
    require __DIR__ . '/incluir/site_cabecalho.php';
    echo '<section class="container secao"><div class="vazio"><i class="fas fa-exclamation-triangle"></i><p>' . e($mensagem) . '</p><a class="btn btn-primario" href="programa.php?id=' . $programa_id . '">Voltar</a></div></section>';
    require __DIR__ . '/incluir/site_rodape.php';
    exit;
}

$data_inicio = date('Y-m-d');
$data_fim    = data_fim_do_plano($plano, $data_inicio);
$valor       = preco_do_plano($programa, $plano);

$stmt = $pdo->prepare('INSERT INTO alugueis (programa_id, cliente_nome, cliente_contato, plano, valor, data_inicio, data_fim, status, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$programa_id, $cliente_nome, $contato, $plano, $valor, $data_inicio, $data_fim, 'ativo', $observacao]);

$texto_whats = 'Olá! Acabei de solicitar o aluguel de *' . $programa['nome'] . '*'
    . "\nPlano: *" . $PLANOS[$plano]['label'] . '*'
    . "\nValor: *" . number_format($valor, 2, ',', '.') . '* (R$)'
    . "\nNome: $cliente_nome"
    . "\nContato: $contato"
    . ($observacao !== '' ? "\nObs: $observacao" : '');

$titulo = 'Pedido recebido';
require __DIR__ . '/incluir/site_cabecalho.php';
?>
<section class="container secao">
  <div class="vazio sucesso">
    <i class="fas fa-circle-check"></i>
    <h2>Pedido registrado!</h2>
    <p><strong><?= e($programa['nome']) ?></strong> — plano <?= e($PLANOS[$plano]['label']) ?> por <?= fmt_moeda($valor) ?>.</p>
    <p>Vigência: <?= fmt_data($data_inicio) ?> até <?= fmt_data($data_fim) ?></p>
    <p>Finalize agora pelo WhatsApp para receber o acesso.</p>
    <a class="btn btn-whats" href="<?= e(url_whats($texto_whats)) ?>" target="_blank"><i class="fab fa-whatsapp"></i> Confirmar no WhatsApp</a>
    <a class="btn btn-secundario" href="index.php">Voltar ao catálogo</a>
  </div>
</section>
<?php require __DIR__ . '/incluir/site_rodape.php'; ?>