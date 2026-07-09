<?php include 'config/conexao.php'; ?>

<?php
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY id_produto DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Produtos</h1>
<a href="api/produtos/produto_novo.php">Novo Produto</a>
<hr>

<?php foreach ($produtos as $p): ?>
    <div style="border:1px solid #ccc;padding:10px;margin-bottom:10px;">
        <h3><?= $p['nome'] ?></h3>
        <p><?= $p['descricao'] ?></p>
        <p><strong>Preço:</strong> R$ <?= number_format($p['preco'],2,',','.') ?></p>
        <p><strong>Estoque:</strong> <?= $p['estoque'] ?></p>

        <?php if ($p['imagem']): ?>
            <img src="uploads/<?= $p['imagem'] ?>" width="120">
        <?php endif; ?>

        <br><br>
        <a href="api/produtos/editar_pedido.php?id=<?= $p['id_produto'] ?>">Editar</a> |
        <a href="api/produtos/deletar_pedido.php?id=<?= $p['id_produto'] ?>" onclick="return confirm('Excluir produto?')">Excluir</a>
    </div>
<?php endforeach; ?>
