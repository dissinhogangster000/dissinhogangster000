<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare('UPDATE servicos SET ativo = 0 WHERE id = ?');
    $stmt->execute([$id]);
    // Ou DELETE se preferir: $pdo->prepare('DELETE FROM servicos WHERE id = ?')->execute([$id]);
}
redirecionar('servicos.php', 'Serviço desativado.');