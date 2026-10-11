<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('agendamentos.php');
}

$id = (int)($_POST['id'] ?? 0);
$servico_id = !empty($_POST['servico_id']) ? (int)$_POST['servico_id'] : null;
$cliente_nome = trim($_POST['cliente_nome'] ?? '');
$cliente_telefone = trim($_POST['cliente_telefone'] ?? '');
$cliente_email = trim($_POST['cliente_email'] ?? '');
$data_agendamento = $_POST['data_agendamento'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$hora_fim = $_POST['hora_fim'] ?? '';
$valor = $_POST['valor'] ?? '0,00';
$status = $_POST['status'] ?? 'pendente';
$observacao = trim($_POST['observacao'] ?? '');
$origem = $_POST['origem'] ?? 'painel';

if (!isset($STATUS_AGENDAMENTO[$status])) $status = 'pendente';
if ($cliente_nome === '' || $cliente_telefone === '' || $data_agendamento === '' || $hora_inicio === '' || $hora_fim === '') {
    redirecionar($id ? 'agendamento-form.php?id=' . $id : 'agendamento-form.php', 'Preencha todos os campos obrigatórios.', 'erro');
}

$valor_numerico = converter_valor($valor);

// Verifica conflito de horário (mesmo dia, horários sobrepostos)
$check_sql = 'SELECT id FROM agendamentos WHERE data_agendamento = ? AND id != ? AND status NOT IN ("cancelado","ausente") AND ( (hora_inicio <= ? AND hora_fim > ?) OR (hora_inicio < ? AND hora_fim >= ?) OR (hora_inicio >= ? AND hora_fim <= ?) )';
$stmt_check = $pdo->prepare($check_sql);
$stmt_check->execute([$data_agendamento, $id, $hora_inicio, $hora_inicio, $hora_fim, $hora_fim, $hora_inicio, $hora_fim]);
if ($stmt_check->fetch()) {
    redirecionar($id ? 'agendamento-form.php?id=' . $id : 'agendamento-form.php', 'Já existe um agendamento neste horário para esta data.', 'erro');
}

if ($id) {
    $stmt = $pdo->prepare('UPDATE agendamentos SET servico_id = ?, cliente_nome = ?, cliente_telefone = ?, cliente_email = ?, data_agendamento = ?, hora_inicio = ?, hora_fim = ?, valor = ?, status = ?, observacao = ?, origem = ? WHERE id = ?');
    $stmt->execute([$servico_id, $cliente_nome, $cliente_telefone, $cliente_email, $data_agendamento, $hora_inicio, $hora_fim, $valor_numerico, $status, $observacao, $origem, $id]);
    redirecionar('agendamentos.php', 'Agendamento atualizado com sucesso.');
} else {
    $stmt = $pdo->prepare('INSERT INTO agendamentos (servico_id, cliente_nome, cliente_telefone, cliente_email, data_agendamento, hora_inicio, hora_fim, valor, status, observacao, origem) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$servico_id, $cliente_nome, $cliente_telefone, $cliente_email, $data_agendamento, $hora_inicio, $hora_fim, $valor_numerico, $status, $observacao, $origem]);
    redirecionar('agendamentos.php', 'Agendamento criado com sucesso.');
}