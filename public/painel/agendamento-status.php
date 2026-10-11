<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = $_GET['status'] ?? '';
$voltar = $_GET['voltar'] ?? '';
$data = $_GET['data'] ?? '';

if (!$id || !isset($STATUS_AGENDAMENTO[$status])) {
    redirecionar('agendamentos.php');
}

$stmt = $pdo->prepare('UPDATE agendamentos SET status = ? WHERE id = ?');
$stmt->execute([$status, $id]);

$destino = 'agendamentos.php';
if ($voltar === 'agenda') {
    $destino = 'agenda.php' . ($data ? '?data=' . urlencode($data) : '');
}
redirecionar($destino, 'Status do agendamento atualizado.');