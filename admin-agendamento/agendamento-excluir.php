<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$voltar = $_GET['voltar'] ?? '';
$data = $_GET['data'] ?? '';

if ($id) {
    $stmt = $pdo->prepare('DELETE FROM agendamentos WHERE id = ?');
    $stmt->execute([$id]);
}

$destino = 'agendamentos.php';
if ($voltar === 'agenda') {
    $destino = 'agenda.php' . ($data ? '?data=' . urlencode($data) : '');
}
redirecionar($destino, 'Agendamento excluído.');