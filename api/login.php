<?php
session_start();

header("Content-Type: application/json");

require_once("../config/conexao.php");

$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados) {
    echo json_encode([
        "status" => false,
        "mensagem" => "Dados não recebidos."
    ]);
    exit;
}

$email = trim($dados["email"] ?? "");
$senha = trim($dados["senha"] ?? "");

$sql = $pdo->prepare("SELECT * FROM empresas WHERE email = ?");
$sql->execute([$email]);

$empresa = $sql->fetch(PDO::FETCH_ASSOC);

if (!$empresa) {

    echo json_encode([
        "status" => false,
        "mensagem" => "Usuário não encontrado."
    ]);
    exit;
}

if (!password_verify($senha, $empresa["senha"])) {

    echo json_encode([
        "status" => false,
        "mensagem" => "Senha incorreta."
    ]);
    exit;
}

$_SESSION["id_empresa"] = $empresa["id_empresa"];
$_SESSION["empresa"] = $empresa["nome"];
$_SESSION["email"] = $empresa["email"];

echo json_encode([
    "status" => true,
    "mensagem" => "Login realizado com sucesso."
]);