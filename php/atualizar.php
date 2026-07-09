<?php
require_once("php/conexao.php");

if (isset($_POST['atualizar'])) {

    $id = mysqli_real_escape_string($conexao, $_POST['id_empresa']);
    $nome = mysqli_real_escape_string($conexao, $_POST['nome_empresa']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $telefone = mysqli_real_escape_string($conexao, $_POST['telefone']);

    // Se o usuário informou uma nova senha
    if (!empty($_POST['senha'])) {

        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

        $sql = "UPDATE empresa SET
                nome_empresa = '$nome',
                email = '$email',
                telefone = '$telefone',
                senha = '$senha'
                WHERE id_empresa = '$id'";

    } else {

        $sql = "UPDATE empresa SET
                nome_empresa = '$nome',
                email = '$email',
                telefone = '$telefone'
                WHERE id_empresa = '$id'";
    }

    if (mysqli_query($conexao, $sql)) {

        echo "<script>
                alert('Empresa atualizada com sucesso!');
                window.location='listar-empresas.php';
              </script>";

    } else {

        echo "<script>
                alert('Erro ao atualizar os dados!');
                window.history.back();
              </script>";
    }

} else {

    header("Location: listar-empresas.php");
    exit;
}
?>