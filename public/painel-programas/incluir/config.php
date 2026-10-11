<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

define('DB_HOST', 'localhost');
define('DB_NAME', 'painel_programas');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NOME', 'Desbloqueio Pro');
define('SITE_DESCRICAO', 'Aluguel de programas e desbloqueios com entrega rápida');
define('WHATSAPP', '5511999999999');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erro de conexão com o banco de dados.');
}

$PLANOS = [
    'dia'    => ['label' => 'Diário',    'dias' => 1],
    'semana' => ['label' => 'Semanal',   'dias' => 7],
    'mes'    => ['label' => 'Mensal',    'dias' => 30],
];

$STATUS_ALUGUEL = [
    'ativo'     => ['label' => 'Ativo',     'cor' => 'verde'],
    'concluido' => ['label' => 'Concluído', 'cor' => 'azul'],
    'vencido'   => ['label' => 'Vencido',   'cor' => 'laranja'],
    'cancelado' => ['label' => 'Cancelado', 'cor' => 'vermelho'],
];

function e($str) {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

function resumir($texto, $limite = 90) {
    if (function_exists('mb_substr')) {
        return mb_substr((string)$texto, 0, $limite);
    }
    return substr((string)$texto, 0, $limite);
}

function tem_mais_que($texto, $limite) {
    if (function_exists('mb_strlen')) {
        return mb_strlen((string)$texto) > $limite;
    }
    return strlen((string)$texto) > $limite;
}

function fmt_moeda($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function valor_para_input($valor) {
    return number_format((float)$valor, 2, ',', '.');
}

function converter_valor($texto) {
    $v = trim((string)$texto);
    $v = preg_replace('/[^\d.,\-]/', '', $v);
    if (strpos($v, ',') !== false) {
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
    }
    return (float)$v;
}

function fmt_data($data) {
    return $data && $data !== '0000-00-00' ? date('d/m/Y', strtotime($data)) : '—';
}

function url_whats($texto) {
    return 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($texto);
}

function data_fim_do_plano($plano, $inicio) {
    global $PLANOS;
    $dias = $PLANOS[$plano]['dias'] ?? 1;
    return date('Y-m-d', strtotime($inicio . ' + ' . $dias . ' days'));
}

function preco_do_plano($programa, $plano) {
    $campo = 'preco_' . $plano;
    return (float)($programa[$campo] ?? 0);
}

function ativo_hoje($data_fim) {
    return $data_fim && strtotime($data_fim) >= strtotime(date('Y-m-d'));
}

function listar_categorias($pdo) {
    return $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();
}

function listar_programas($pdo, $categoria_id = null) {
    $sql = 'SELECT p.*, c.nome AS categoria FROM programas p LEFT JOIN categorias c ON c.id = p.categoria_id WHERE p.ativo = 1';
    $params = [];
    if ($categoria_id) {
        $sql .= ' AND p.categoria_id = ?';
        $params[] = (int)$categoria_id;
    }
    $sql .= ' ORDER BY p.destaque DESC, p.nome ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}