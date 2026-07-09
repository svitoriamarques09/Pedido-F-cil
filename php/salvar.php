<?php
require_once("php/conexao.php");

if (isset($_POST['cadastrar'])) {

    $nome = mysqli_real_escape_string($conexao, $_POST['nome_empresa']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $telefone = mysqli_real_escape_string($conexao, $_POST['telefone']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Verifica se o e-mail já existe
    $verifica = mysqli_query($conexao, "SELECT * FROM empresa WHERE email='$email'");

    if (mysqli_num_rows($verifica) > 0) {

        echo "<script>
            alert('Este e-mail já está cadastrado!');
            window.history.back();
        </script>";

    } else {

        $sql = "INSERT INTO empresa
        (nome_empresa, email, telefone, senha)
        VALUES
        ('$nome', '$email', '$telefone', '$senha')";

        if (mysqli_query($conexao, $sql)) {

            echo "<script>
                alert('Empresa cadastrada com sucesso!');
                window.location='login.php';
            </script>";

        } else {

            echo "<script>
                alert('Erro ao cadastrar!');
            </script>";

        }

    }

}
?>