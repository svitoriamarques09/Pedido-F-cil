<?php

require_once("php/conexao.php");
// Inclui o arquivo de conexão com o banco

$id = $_GET['id'];
// Recebe o id do cliente enviado pela URL

$sql = "DELETE FROM alunos WHERE id = $id";
// Cria o comando SQL para excluir o cliente

if (mysqli_query($conexao, $sql)) {
    // Executa o comando SQL e verifica se deu certo

    header("Location: index.php");
    // Redireciona para a página principal

} else {
    echo "Erro ao excluir.";
    // Exibe mensagem de erro caso falhe
}

?>