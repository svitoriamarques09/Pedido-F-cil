<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION["id_empresa"])) {
    header("Location: login.php");
    exit;
}

require_once("config/conexao.php");

$idEmpresa = $_SESSION["id_empresa"];
$hoje = date("Y-m-d");

$stmtLogo = $pdo->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
$stmtLogo->execute([$idEmpresa]);
$logo_empresa = $stmtLogo->fetchColumn() ?: '';

// PEDIDOS HOJE
$sql = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pedidos 
    WHERE DATE(data_pedido) = CURDATE()
    AND id_empresa = ?
");
$sql->execute([$idEmpresa]);
$pedidosHoje = $sql->fetchColumn();

// FATURAMENTO HOJE
$sql = $pdo->prepare("
    SELECT SUM(valor_total)
    FROM pedidos 
    WHERE DATE(data_pedido) = ?
    AND id_empresa = ?
");
$sql->execute([$hoje, $idEmpresa]);
$faturamento = $sql->fetchColumn() ?? 0;

// PENDENTES
$sql = $pdo->prepare("
    SELECT COUNT(*) 
    FROM pedidos 
    WHERE status = 'Pendente'
    AND id_empresa = ?
");
$sql->execute([$idEmpresa]);
$pendentes = $sql->fetchColumn();

// CLIENTES
$sql = $pdo->prepare("
    SELECT COUNT(DISTINCT id_cliente)
    FROM pedidos 
    WHERE id_empresa = ?
");
$sql->execute([$idEmpresa]);
$clientes = $sql->fetchColumn();

// =============================
// ÚLTIMOS PEDIDOS
// =============================
$sql = $pdo->prepare("
SELECT
    p.id_pedido,
    c.nome AS cliente_nome,
    p.valor_total,
    p.status,
    p.data_pedido,
    COALESCE(GROUP_CONCAT(CONCAT(pi.quantidade, 'x ', pr.nome) SEPARATOR ', '), '-') AS produtos
FROM pedidos p
LEFT JOIN clientes c
    ON c.id_cliente = p.id_cliente
LEFT JOIN pedido_itens pi
    ON pi.id_pedido = p.id_pedido
LEFT JOIN produtos pr
    ON pr.id_produto = pi.id_produto
WHERE p.id_empresa = ?
GROUP BY p.id_pedido, c.nome, p.valor_total, p.status, p.data_pedido
ORDER BY p.id_pedido DESC
LIMIT 10
");
$sql->execute([$idEmpresa]);
$pedidos = $sql->fetchAll(PDO::FETCH_ASSOC);

// FATURAMENTO DOS ULTIMOS 7 DIAS
$sql = $pdo->prepare("
    SELECT DATE(data_pedido) AS dia, SUM(valor_total) AS total
    FROM pedidos
    WHERE id_empresa = ?
    AND DATE(data_pedido) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(data_pedido)
");
$sql->execute([$idEmpresa]);
$faturamentoPorDia = $sql->fetchAll(PDO::FETCH_KEY_PAIR);

$labelsFaturamento = [];
$dadosFaturamento = [];
for ($i = 6; $i >= 0; $i--) {
    $data = date('Y-m-d', strtotime("-$i days"));
    $labelsFaturamento[] = date('d/m', strtotime($data));
    $dadosFaturamento[] = (float)($faturamentoPorDia[$data] ?? 0);
}

// PRODUTOS MAIS VENDIDOS
$sql = $pdo->prepare("
    SELECT pr.nome, SUM(pi.quantidade) AS total
    FROM pedido_itens pi
    INNER JOIN pedidos p ON p.id_pedido = pi.id_pedido
    INNER JOIN produtos pr ON pr.id_produto = pi.id_produto
    WHERE p.id_empresa = ?
    GROUP BY pr.id_produto, pr.nome
    ORDER BY total DESC
    LIMIT 5
");
$sql->execute([$idEmpresa]);
$produtosMaisVendidos = $sql->fetchAll(PDO::FETCH_ASSOC);

$labelsProdutos = array_column($produtosMaisVendidos, 'nome');
$dadosProdutos = array_map('intval', array_column($produtosMaisVendidos, 'total'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | PedidoFácil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<div class="container-fluid">
<div class="row">

    <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
    <aside class="col-lg-2 sidebar">
        <div class="logo">
            <?php if (!empty($logo_empresa) && file_exists(__DIR__ . '/' . $logo_empresa)): ?>
                <img src="<?= htmlspecialchars($logo_empresa) ?>" alt="Logo da empresa" class="sidebar-logo-img">
            <?php else: ?>
                <i class="bi bi-chat-dots-fill"></i>
            <?php endif; ?>
            <h3>PedidoFácil</h3>
        </div>
        <ul>
            <li class="<?= $pagina_atual == 'dashboard.php' ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="bi bi-grid-fill"></i> Dashboard</a>
            </li>
            <li class="<?= $pagina_atual == 'pedidos.php' ? 'active' : '' ?>">
                <a href="pedidos.php"><i class="bi bi-cart-fill"></i> Pedidos</a>
            </li>
            <li class="<?= $pagina_atual == 'producao.php' ? 'active' : '' ?>">
                <a href="producao.php"><i class="bi bi-fire"></i> Produção</a>
            </li>
            <li class="<?= $pagina_atual == 'produtos.php' ? 'active' : '' ?>">
                <a href="produtos.php"><i class="bi bi-box-seam-fill"></i> Produtos</a>
            </li>
            <li class="<?= $pagina_atual == 'relatorios.php' ? 'active' : '' ?>">
                <a href="relatorios.php"><i class="bi bi-bar-chart-fill"></i> Relatórios</a>
            </li>
            <li class="<?= $pagina_atual == 'configuracoes.php' ? 'active' : '' ?>">
                <a href="configuracoes.php"><i class="bi bi-gear-fill"></i> Configurações</a>
            </li>
        </ul>
    </aside>

    <main class="col-lg-10 main-content">

        <header class="topbar">
            <div class="search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Pesquisar...">
            </div>

            <div class="topbar-right">
                <button class="notification">
                    <i class="bi bi-bell-fill"></i>
                </button>

                <div class="profile d-flex align-items-center gap-2">
                    <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div>
                        <strong class="d-block"><?= htmlspecialchars($_SESSION["empresa"] ?? 'Lanchonete Teste'); ?></strong>
                        <small class="text-muted">Empresa</small>
                    </div>
                </div>
            </div>
        </header>

        <section class="page-title">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Dashboard</h2>
                    <p>Bem-vindo novamente</p>
                </div>
            </div>
        </section>

        <section class="cards">
            <div class="card-dashboard">
                <div>
                    <small>Pedidos Hoje</small>
                    <h2 id="pedidosHoje"><?= $pedidosHoje ?></h2>
                    <span class="positive">+12%</span>
                </div>
                <i class="bi bi-cart-check-fill"></i>
            </div>

            <div class="card-dashboard">
                <div>
                    <small>Faturamento</small>
                    <h2 id="faturamento">R$ <?= number_format($faturamento, 2, ',', '.') ?></h2>
                    <span class="positive">+8%</span>
                </div>
                <i class="bi bi-cash-stack"></i>
            </div>

            <div class="card-dashboard">
                <div>
                    <small>Pendentes</small>
                    <h2 id="pendentes"><?= $pendentes ?></h2>
                    <span class="negative">-2%</span>
                </div>
                <i class="bi bi-clock-history"></i>
            </div>

            <div class="card-dashboard">
                <div>
                    <small>Clientes</small>
                    <h2 id="clientes"><?= $clientes ?></h2>
                    <span class="positive">+20%</span>
                </div>
                <i class="bi bi-people-fill"></i>
            </div>
        </section>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="dashboard-box mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Faturamento dos últimos 7 dias</h4>
                        <select class="form-select w-auto">
                            <option>Últimos 7 dias</option>
                            <option>Últimos 30 dias</option>
                            <option>Este mês</option>
                        </select>
                    </div>
                    <canvas id="salesChart" height="110"></canvas>
                </div>

                <div class="dashboard-box">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Últimos Pedidos</h4>
                        <a href="pedidos.php" class="btn btn-danger btn-sm">Ver todos</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Produto</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaPedidos">
                                <?php if(count($pedidos) > 0): ?>
                                    <?php foreach($pedidos as $p): ?>
                                        <tr>
                                            <td>#<?= $p["id_pedido"] ?></td>
                                            <td><?= htmlspecialchars($p["cliente_nome"] ?? 'Cliente não identificado') ?></td>
                                            <td><?= htmlspecialchars($p["produtos"] ?? '-') ?></td>
                                            <td>R$ <?= number_format($p["valor_total"], 2, ",", ".") ?></td>
                                            <td>
                                                <?php
                                                switch($p["status"]){
                                                    case "Pendente":
                                                        echo '<span class="badge bg-warning text-dark">Pendente</span>';
                                                        break;
                                                    case "Produção":
                                                    case "Em Preparo":
                                                    case "Em preparo":
                                                        echo '<span class="badge bg-primary">Produção</span>';
                                                        break;
                                                    case "Entrega":
                                                    case "Saiu para Entrega":
                                                        echo '<span class="badge bg-info text-dark">Entrega</span>';
                                                        break;
                                                    case "Finalizado":
                                                        echo '<span class="badge bg-success">Finalizado</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">'.htmlspecialchars($p["status"]).'</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="pedido.php?id=<?= $p["id_pedido"] ?>" class="btn btn-primary btn-sm">Ver</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Nenhum pedido encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-box mb-4">
                    <h4 class="mb-4">Produtos mais vendidos</h4>
                    <canvas id="productChart"></canvas>
                </div>

                <div class="dashboard-box">
                    <h4 class="mb-4">Atividades Recentes</h4>
                    <?php if(count($pedidos) > 0): ?>
                        <?php foreach(array_slice($pedidos, 0, 3) as $pedidoRecente): ?>
                            <div class="activity mb-3 d-flex gap-3 align-items-start">
                                <i class="bi bi-cart-check-fill text-success fs-4"></i>
                                <div>
                                    <strong>Pedido #<?= $pedidoRecente["id_pedido"] ?> recebido</strong>
                                    <p class="m-0 text-muted"><?= htmlspecialchars($pedidoRecente["cliente_nome"] ?? 'Cliente não identificado') ?> realizou um pedido.</p>
                                    <small class="text-secondary"><?= date('d/m/Y H:i', strtotime($pedidoRecente["data_pedido"])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Nenhuma atividade recente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
window.dashboardData = {
    faturamentoLabels: <?= json_encode($labelsFaturamento) ?>,
    faturamentoValores: <?= json_encode($dadosFaturamento) ?>,
    produtosLabels: <?= json_encode($labelsProdutos) ?>,
    produtosValores: <?= json_encode($dadosProdutos) ?>
};
</script>
<script src="assets/js/dashboard.js"></script>
</body>
</html>
