<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ?');
    $stmt->execute([$id]);
}
redirecionar('categorias.php', 'Categoria excluída.');