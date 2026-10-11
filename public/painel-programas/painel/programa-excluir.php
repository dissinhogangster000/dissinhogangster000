<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM programas WHERE id = ?');
    $stmt->execute([$id]);
}
redirecionar('programas.php', 'Programa excluído.');