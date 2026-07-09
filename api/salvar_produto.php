<?php
// Conexão correta com base no arquivo SQL enviado
$host = "localhost";
$user = "root";
$pass = "";
$db   = "salgados_tcc"; // Nome real do seu banco de dados

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_empresa   = intval($_POST['id_empresa']);
    $nome         = $_POST['nome'];
    $preco        = floatval($_POST['preco']);
    $estoque      = intval($_POST['estoque']);
    $status       = $_POST['status'];
    $descricao    = $_POST['descricao'];
    
    // Mapeamento temporário de IDs de categoria com base nas opções do modal
    // Se o seu banco tiver IDs diferentes para as categorias, basta ajustar os números abaixo
    $categoria_texto = $_POST['categoria'];
    $id_categoria = 2; // Padrão (Salgados) se algo falhar
    
    // Substitua os números abaixo pelos IDs reais que aparecem na sua tabela 'categorias'
    if ($categoria_texto === "Salgados") $id_categoria = 1; // Altere o número se necessário
    else if ($categoria_texto === "Pizzas") $id_categoria = 2; // Altere o número se necessário
    else if ($categoria_texto === "Bebidas") $id_categoria = 3; // Altere o número se necessário
    else if ($categoria_texto === "Doces") $id_categoria = 4; // Altere o número se necessário
    
    // Caminho padrão de imagem caso o usuário não envie arquivo
    $imagem_caminho = "img/default.jpg"; 

    // Processamento do Upload da Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        // Gera o nome parecido com o que o phpMyAdmin mostrou no dump
        $novo_nome = "produto_" . time() . "_" . uniqid() . "." . $extensao;
        
        $diretorio = "../img/";
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0777, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $diretorio . $novo_nome)) {
            $imagem_caminho = "img/" . $novo_nome;
        }
    }

    // QUERY CORRIGIDA: Usa exatamente as colunas do seu banco (id_categoria e imagem)
    $sql = "INSERT INTO produtos (id_empresa, id_categoria, nome, descricao, preco, estoque, imagem, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // i = int, d = double/decimal, s = string
    $stmt->bind_param("iissdiss", $id_empresa, $id_categoria, $nome, $descricao, $preco, $estoque, $imagem_caminho, $status);

    if ($stmt->execute()) {
        // Redireciona de volta para a tela de produtos com sinal de sucesso
        header("Location: ../produtos.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao cadastrar produto no banco: " . $stmt->error;
    }
}
?>