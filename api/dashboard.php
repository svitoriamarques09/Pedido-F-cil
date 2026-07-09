<?php
require_once("../config/conexao.php");

header('Content-Type: application/json');

$idEmpresa = $_GET['id_empresa'] ?? 0;
$hoje = date("Y-m-d");


// PEDIDOS HOJE
$sql = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pedidos 
    WHERE DATE(data_pedido) = CURDATE()
    AND id_empresa = ?
");
$sql->execute([$idEmpresa]);
$pedidosHoje = $sql->fetchColumn();


// FATURAMENTO (VERSÃO COMPLETA)
$dataHoje = date('Y-m-d'); // Pega a data atual do PHP (Ex: 2026-07-06)

$sql = $pdo->prepare("
    SELECT SUM(valor_total) as total 
    FROM pedidos 
    WHERE data_pedido LIKE ? 
    AND id_empresa = ?
");
$sql->execute([$dataHoje . '%', $idEmpresa]); // O '%' garante que vai pegar qualquer hora do dia de hoje
$resultadoFaturamento = $sql->fetch(PDO::FETCH_ASSOC);

// Força o valor a ser numérico (caso o banco retorne nulo)
$faturamento = isset($resultadoFaturamento['total']) ? (float)$resultadoFaturamento['total'] : 0.00;


// PENDENTES
$sql = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pedidos 
    WHERE status = 'Pendente'
    AND id_empresa = ?
");
$sql->execute([$idEmpresa]);
$pendentes = $sql->fetchColumn();


// CLIENTES
$sql = $pdo->prepare("
    SELECT COUNT(DISTINCT id_cliente)
    FROM pedidos 
    WHERE id_empresa = ?
");
$sql->execute([$idEmpresa]);
$clientes = $sql->fetchColumn();


echo json_encode([
    "pedidosHoje" => $pedidosHoje,
    "faturamento" => $faturamento,
    "pendentes" => $pendentes,
    "clientes" => $clientes
]);