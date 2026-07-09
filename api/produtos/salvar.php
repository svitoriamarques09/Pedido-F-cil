<?php
require_once "config/conexao.php";

$data = json_decode(file_get_contents("php://input"), true);

$nome = $data["nome"];
$descricao = $data["descricao"];
$preco = $data["preco"];
$estoque = $data["estoque"];

$sql = $pdo->prepare("
    INSERT INTO produtos (nome, descricao, preco, estoque)
    VALUES (?, ?, ?, ?)
");

$sql->execute([$nome, $descricao, $preco, $estoque]);

echo json_encode([
    "status" => "ok",
    "mensagem" => "Produto cadastrado com sucesso"
]);