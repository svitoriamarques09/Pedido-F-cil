<?php
// Conecta ao banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=salgados_tcc", "root", "");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Deleta o pedido do banco
    $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = :id");
    $stmt->execute(['id' => $id]);
}

// Redireciona de volta para a tela de produção principal
header("Location: producao.php");
exit();
?>