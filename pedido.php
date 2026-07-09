<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["id_empresa"])) {
    header("Location: login.php");
    exit;
}

require_once("config/conexao.php");

$idPedido = $_GET["id"] ?? 0;

if ($idPedido == 0) {
    die("Pedido não informado.");
}

/*
|--------------------------------------------------------------------------
| DADOS DO PEDIDO
|--------------------------------------------------------------------------
*/

$sql = $pdo->prepare("
SELECT
    p.*,
    c.nome AS cliente,
    c.telefone,
    c.endereco

FROM pedidos p

LEFT JOIN clientes c
    ON c.id_cliente = p.id_cliente

WHERE p.id_pedido = ?
");

$sql->execute([$idPedido]);

$pedido = $sql->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("Pedido não encontrado.");
}

/*
|--------------------------------------------------------------------------
| ITENS DO PEDIDO
|--------------------------------------------------------------------------
*/

$sql = $pdo->prepare("
SELECT
    pr.nome,
    pi.quantidade,
    pi.preco_unitario

FROM pedido_itens pi

INNER JOIN produtos pr
    ON pr.id_produto = pi.id_produto

WHERE pi.id_pedido = ?
");

$sql->execute([$idPedido]);

$itens = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Pedido #<?= $pedido["id_pedido"] ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body class="bg-light">

<div class="container py-5">

<div class="card shadow">

<div class="card-header bg-danger text-white">

<h3>

Pedido #<?= $pedido["id_pedido"] ?>

</h3>

</div>

<div class="card-body">

<h5>Cliente</h5>

<p>

<strong>Nome:</strong>

<?= $pedido["cliente"] ?>

</p>

<p>

<strong>Telefone:</strong>

<?= $pedido["telefone"] ?>

</p>

<p>

<strong>Endereço:</strong>

<?= $pedido["endereco"] ?>

</p>

<hr>

<h5>Produtos</h5>

<table class="table table-bordered">

<thead>

<tr>

<th>Produto</th>

<th>Quantidade</th>

<th>Valor Unitário</th>

<th>Total</th>

</tr>

</thead>

<tbody>

<?php foreach($itens as $item): ?>

<tr>

<td>

<?= $item["nome"] ?>

</td>

<td>

<?= $item["quantidade"] ?>

</td>

<td>

R$
<?= number_format($item["preco_unitario"],2,",",".") ?>

</td>

<td>

R$

<?= number_format(
$item["preco_unitario"] * $item["quantidade"],
2,
",",
"."
) ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<div class="mt-4">

<h4>

Total do Pedido

</h4>

<h2 class="text-danger">

R$

<?= number_format(
$pedido["valor_total"],
2,
",",
"."
) ?>

</h2>

</div>

<hr>

<div class="d-flex gap-3">

<a href="pedidos.php" class="btn btn-secondary">
 Voltar
</a>

</div>

</div>

</div>

</div>

</body>

</html>