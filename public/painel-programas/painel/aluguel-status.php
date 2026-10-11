<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = $_GET['status'] ?? '';
$filtro = $_GET['voltar'] ?? '';

if ($id && isset($STATUS_ALUGUEL[$status])) {
    $stmt = $pdo->prepare('UPDATE alugueis SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
}

$destino = 'alugueis.php';
if (isset($STATUS_ALUGUEL[$filtro])) $destino .= '?status=' . urlencode($filtro);
header('Location: ' . $destino);
exit;