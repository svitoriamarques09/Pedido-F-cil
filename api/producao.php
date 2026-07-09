<?php
// Conexão com o banco (ajuste com seus dados)
$pdo = new PDO("mysql:host=localhost;dbname=salgados_tcc", "root", "");

// 1. FILTRO: Verifica se o usuário escolheu um status para filtrar
$status_filtro = isset($_GET['status']) ? $_GET['status'] : 'Todos';

if ($status_filtro && $status_filtro != 'Todos') {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE status = :status ORDER BY data_pedido DESC");
    $stmt->execute(['status' => $status_filtro]);
} else {
    $stmt = $pdo->query("SELECT * FROM pedidos ORDER BY data_pedido DESC");
}
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Área de Produção</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-del { background-color: #dc3545; }
    </style>
</head>
<body>

    <h2>Painel da Produção - Gestão de Pedidos</h2>

    <form method="GET" action="producao.php">
        <label for="status">Filtrar por Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="Todos" <?= $status_filtro == 'Todos' ? 'selected' : '' ?>>Todos</option>
            <option value="Pendente" <?= $status_filtro == 'Pendente' ? 'selected' : '' ?>>Pendentes</option>
            <option value="Em Produção" <?= $status_filtro == 'Em Produção' ? 'selected' : '' ?>>Em Produção</option>
            <option value="Concluído" <?= $status_filtro == 'Concluído' ? 'selected' : '' ?>>Concluídos</option>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Item</th>
                <th>Qtd</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?= $pedido['id'] ?></td>
                <td><?= $pedido['item'] ?></td>
                <td><?= $pedido['quantidade'] ?></td>
                <td><strong><?= $pedido['status'] ?></strong></td>
                <td>
                    <a href="api/produtos/editar_pedido.php?id=<?= $pedido['id'] ?>" class="btn btn-edit">Editar</a>
                    <a href=deletar_pedido.php"php?id=<?= $pedido['id'] ?>" class="btn btn-del" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>