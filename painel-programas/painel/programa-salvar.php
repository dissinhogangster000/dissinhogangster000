<?php
require_once __DIR__ . '/incluir/auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('programas.php');
}

$id       = (int)($_POST['id'] ?? 0);
$nome     = trim($_POST['nome'] ?? '');
$icone    = trim($_POST['icone'] ?? '');
$desc     = trim($_POST['descricao'] ?? '');
$cat_id   = $_POST['categoria_id'] !== '' ? (int)$_POST['categoria_id'] : null;
$destaque = isset($_POST['destaque']) ? 1 : 0;
$ativo    = isset($_POST['ativo']) ? 1 : 0;

function converter_preco($valor) {
    return converter_valor($valor);
}

$precos = [
    converter_preco($_POST['preco_dia'] ?? '0'),
    converter_preco($_POST['preco_semana'] ?? '0'),
    converter_preco($_POST['preco_mes'] ?? '0'),
];

if ($nome === '' || $desc === '') {
    redirecionar($id ? 'programa-form.php?id=' . $id : 'programa-form.php', 'Preencha os campos obrigatórios.', 'erro');
}

if ($id) {
    $stmt = $pdo->prepare('UPDATE programas SET nome = ?, icone = ?, descricao = ?, categoria_id = ?, preco_dia = ?, preco_semana = ?, preco_mes = ?, destaque = ?, ativo = ? WHERE id = ?');
    $stmt->execute([$nome, $icone, $desc, $cat_id, $precos[0], $precos[1], $precos[2], $destaque, $ativo, $id]);
    redirecionar('programas.php', 'Programa atualizado com sucesso.');
} else {
    $stmt = $pdo->prepare('INSERT INTO programas (nome, icone, descricao, categoria_id, preco_dia, preco_semana, preco_mes, destaque, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$nome, $icone, $desc, $cat_id, $precos[0], $precos[1], $precos[2], $destaque, $ativo]);
    redirecionar('programas.php', 'Programa cadastrado com sucesso.');
}