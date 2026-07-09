<?php
require_once("config/conexao.php");

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: dashboard.php"); exit; }

// Busca os dados do pedido atual
$stmt = $pdo->prepare("SELECT p.*, c.nome FROM pedidos p JOIN clientes c ON p.id_cliente = c.id_cliente WHERE p.id_pedido = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o formulário for enviado, atualiza o status no banco
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoStatus = $_POST['status'];
    $update = $pdo->prepare("UPDATE pedidos SET status = ? WHERE id_pedido = ?");
    $update->execute([$novoStatus, $id]);
    
    // Redireciona de volta para a sua tela de produção (ajuste o nome do arquivo se for diferente)
    header("Location: dashboard.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Pedido #<?php echo $id; ?></title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        select, button { width: 100%; padding: 10px; margin-top: 15px; border-radius: 5px; border: 1px solid #ccc; font-size: 16px; }
        button { background: #ffc107; color: black; font-weight: bold; cursor: pointer; border: none; }
        button:hover { background: #e0a800; }
    </style>
</head>
<body>

<div class="card">
    <h2>Mudar Status do Pedido #<?php echo $id; ?></h2>
    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nome']); ?></p>
    <p><strong>Valor:</strong> R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></p>

    <form method="POST">
        <label for="status">Selecione o novo Status:</label>
        <select name="status" id="status">
            <option value="Pendente" <?php echo $pedido['status'] == 'Pendente' ? 'selected' : ''; ?>>Pendente 🟡</option>
            <option value="Em Preparo" <?php echo $pedido['status'] == 'Em Preparo' ? 'selected' : ''; ?>>Em Preparo 🔵</option>
            <option value="Saiu para Entrega" <?php echo $pedido['status'] == 'Saiu para Entrega' ? 'selected' : ''; ?>>Saiu para Entrega 🚚</option>
            <option value="Finalizado" <?php echo $pedido['status'] == 'Finalizado' ? 'selected' : ''; ?>>Finalizado 🟢</option>
        </select>
        
        <button type="submit">Salvar Alteração</button>
    </form>
    <br>
    <a href="javascript:history.back()" style="display:block; text-align:center; color:#666;">Voltar</a>
</div>

</body>
</html>