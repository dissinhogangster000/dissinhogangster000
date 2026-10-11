<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('servicos.php');
}

$id = (int)($_POST['id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$duracao = (int)($_POST['duracao_min'] ?? 60);
$preco = $_POST['preco'] ?? '0,00';
$ativo = (int)($_POST['ativo'] ?? 1);

if ($nome === '' || $duracao <= 0) {
    redirecionar($id ? 'servico-form.php?id=' . $id : 'servico-form.php', 'Preencha os campos obrigatórios.', 'erro');
}

$valor_numerico = converter_valor($preco);

if ($id) {
    $stmt = $pdo->prepare('UPDATE servicos SET nome = ?, descricao = ?, duracao_min = ?, preco = ?, ativo = ? WHERE id = ?');
    $stmt->execute([$nome, $descricao, $duracao, $valor_numerico, $ativo, $id]);
    redirecionar('servicos.php', 'Serviço atualizado.');
} else {
    $stmt = $pdo->prepare('INSERT INTO servicos (nome, descricao, duracao_min, preco, ativo) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$nome, $descricao, $duracao, $valor_numerico, $ativo]);
    redirecionar('servicos.php', 'Serviço cadastrado.');
}