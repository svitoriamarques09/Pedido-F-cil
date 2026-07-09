<?php include 'config/conexao.php'; ?>

<?php
if ($_POST) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $id_categoria = $_POST['id_categoria'];
    $valor = $_POST['valor'];

    // Upload da imagem
    $imagem = null;
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = time() . "_" . $_FILES['imagem']['name'];
        move_uploaded_file($_FILES['imagem']['tmp_name'], "uploads/" . $imagem);
    }

    $sql = "INSERT INTO produtos (id_empresa, id_categoria, nome, descricao, preco, estoque, imagem, valor)
            VALUES (1, :cat, :nome, :desc, :preco, :estoque, :imagem, :valor)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cat' => $id_categoria,
        ':nome' => $nome,
        ':desc' => $descricao,
        ':preco' => $preco,
        ':estoque' => $estoque,
        ':imagem' => $imagem,
        ':valor' => $valor
    ]);

    header("Location: produtos.php");
    exit;
}
?>

<h2>Novo Produto</h2>

<form method="post" enctype="multipart/form-data">
    Nome: <input type="text" name="nome"><br><br>
    Descrição: <textarea name="descricao"></textarea><br><br>
    Preço: <input type="number" step="0.01" name="preco"><br><br>
    Estoque: <input type="number" name="estoque"><br><br>
    Categoria: <input type="number" name="id_categoria"><br><br>
    Valor: <input type="number" step="0.01" name="valor"><br><br>
    Imagem: <input type="file" name="imagem"><br><br>

    <button type="submit">Salvar</button>
</form>
