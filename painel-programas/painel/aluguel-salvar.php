<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('alugueis.php');
}

function destino_filtro() {
    return 'alugueis.php' . (!empty($_POST['status']) ? '?status=' . urlencode($_POST['status']) : '');
}

$id           = (int)($_POST['id'] ?? 0);
$programa_id  = (int)($_POST['programa_id'] ?? 0);
$cliente      = trim($_POST['cliente_nome'] ?? '');
$contato      = trim($_POST['cliente_contato'] ?? '');
$plano        = $_POST['plano'] ?? 'dia';
$valor        = $_POST['valor'] ?? '0,00';
$data_inicio  = $_POST['data_inicio'] ?? '';
$data_fim     = $_POST['data_fim'] ?? '';
$codigo       = trim($_POST['codigo_acesso'] ?? '');
$status       = $_POST['status'] ?? 'ativo';
$observacao   = trim($_POST['observacao'] ?? '');

if (!isset($PLANOS[$plano])) $plano = 'dia';
if (!isset($STATUS_ALUGUEL[$status])) $status = 'ativo';

$stmt = $pdo->prepare('SELECT id FROM programas WHERE id = ?');
$stmt->execute([$programa_id]);
if (!$stmt->fetch() || $cliente === '' || $contato === '' || $data_inicio === '') {
    header('Location: ' . ($id ? 'aluguel-form.php?id=' . $id : 'aluguel-form.php'));
    exit;
}

$valor_numerico = converter_valor($valor);
if ($data_fim === '') $data_fim = data_fim_do_plano($plano, $data_inicio);

if ($id) {
    $stmt = $pdo->prepare('UPDATE alugueis SET programa_id = ?, cliente_nome = ?, cliente_contato = ?, plano = ?, valor = ?, data_inicio = ?, data_fim = ?, codigo_acesso = ?, status = ?, observacao = ? WHERE id = ?');
    $stmt->execute([$programa_id, $cliente, $contato, $plano, $valor_numerico, $data_inicio, $data_fim, $codigo, $status, $observacao, $id]);
    redirecionar(destino_filtro(), 'Aluguel atualizado.');
} else {
    $stmt = $pdo->prepare('INSERT INTO alugueis (programa_id, cliente_nome, cliente_contato, plano, valor, data_inicio, data_fim, codigo_acesso, status, observacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$programa_id, $cliente, $contato, $plano, $valor_numerico, $data_inicio, $data_fim, $codigo, $status, $observacao]);
    redirecionar(destino_filtro(), 'Aluguel registrado.');
}