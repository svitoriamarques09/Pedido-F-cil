<?php

header("Content-Type: application/json");

require_once("../config/conexao.php");

// Recebe os dados enviados pelo JavaScript
$dados = json_decode(file_get_contents("php://input"), true);

if (!$dados) {
    echo json_encode([
        "status" => false,
        "mensagem" => "Nenhum dado recebido."
    ]);
    exit;
}

// Recebe os campos
$nome = trim($dados["empresa"] ?? "");
$tipo = trim($dados["tipo"] ?? "");
$responsavel = trim($dados["responsavel"] ?? "");
$telefone = trim($dados["telefone"] ?? "");
$cnpj = trim($dados["cnpj"] ?? "");
$email = trim($dados["email"] ?? "");
$senha = trim($dados["senha"] ?? "");

// Validação
if (
    empty($nome) ||
    empty($tipo) ||
    empty($responsavel) ||
    empty($telefone) ||
    empty($cnpj) ||
    empty($email) ||
    empty($senha)
) {

    echo json_encode([
        "status" => false,
        "mensagem" => "Preencha todos os campos."
    ]);

    exit;
}

// Verifica se já existe e-mail
$sql = $pdo->prepare("SELECT id_empresa FROM empresas WHERE email = ?");
$sql->execute([$email]);

if ($sql->rowCount() > 0) {

    echo json_encode([
        "status" => false,
        "mensagem" => "Este e-mail já está cadastrado."
    ]);

    exit;
}

// Verifica se já existe CNPJ
$sql = $pdo->prepare("SELECT id_empresa FROM empresas WHERE cnpj = ?");
$sql->execute([$cnpj]);

if ($sql->rowCount() > 0) {

    echo json_encode([
        "status" => false,
        "mensagem" => "Este CNPJ já está cadastrado."
    ]);

    exit;
}

// Criptografa a senha
$senha = password_hash($senha, PASSWORD_DEFAULT);

// Insere no banco
$sql = $pdo->prepare("
INSERT INTO empresas
(nome,tipo,responsavel,telefone,cnpj,email,senha)
VALUES
(?,?,?,?,?,?,?)
");

$sql->execute([
    $nome,
    $tipo,
    $responsavel,
    $telefone,
    $cnpj,
    $email,
    $senha
]);

echo json_encode([
    "status" => true,
    "mensagem" => "Empresa cadastrada com sucesso!"
]);

?>