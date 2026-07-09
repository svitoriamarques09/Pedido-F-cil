<?php
require_once("php/conexao.php");

// Verifica se recebeu o ID
if (!isset($_GET['id'])) {
    die("ID não informado.");
}

$id = $_GET['id'];

// Busca os dados do usuário
$sql = "SELECT * FROM usuarios WHERE id = '$id'";
$resultado = mysqli_query($conexao, $sql);

if (mysqli_num_rows($resultado) == 0) {
    die("Usuário não encontrado.");
}

$dados = mysqli_fetch_assoc($resultado);

// Atualiza os dados
if (isset($_POST['atualizar'])) {

    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email = mysqli_real_escape_string($conexao, $_POST['email']);
    $senha = mysqli_real_escape_string($conexao, $_POST['senha']);

    $sql = "UPDATE usuarios
            SET nome='$nome',
                email='$email',
                senha='$senha'
            WHERE id='$id'";

    if (mysqli_query($conexao, $sql)) {

        echo "<script>
                alert('Usuário atualizado com sucesso!');
                window.location='listar.php';
              </script>";

    } else {

        echo "Erro: " . mysqli_error($conexao);

    }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h3>Editar Usuário</h3>

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">Nome</label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?php echo $dados['nome']; ?>"
                        required>

                </div>

                <div class="mb-3">

                    <label class="form-label">E-mail</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?php echo $dados['email']; ?>"
                        required>

                </div>

                <div class="mb-3">

                    <label class="form-label">Senha</label>

                    <input
                        type="text"
                        name="senha"
                        class="form-control"
                        value="<?php echo $dados['senha']; ?>"
                        required>

                </div>

                <button
                    type="submit"
                    name="atualizar"
                    class="btn btn-success">

                    Atualizar

                </button>

                <a href="listar.php" class="btn btn-secondary">

                    Cancelar

                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>