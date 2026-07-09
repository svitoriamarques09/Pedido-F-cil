<?php
$pdo = new PDO("mysql:host=localhost;dbname=salgados_tcc", "root", "");

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id_produto = :id");
    $stmt->execute(['id' => $id]);
}

header("Location: ../produtos.php");
exit();
?>