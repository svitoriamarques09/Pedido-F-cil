<?php
// 1. Conexão com o banco de dados real
try {
    $pdo = new PDO("mysql:host=localhost;dbname=salgados_tcc", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// 2. AÇÃO DE DELETAR (Executa na própria página)
if (isset($_GET['acao']) && $_GET['acao'] == 'deletar' && isset($_GET['id'])) {
    $id_deletar = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = :id");
        $stmt->execute(['id' => $id_deletar]);
    } catch (PDOException $e) {
        // Se der erro por restrição de chave estrangeira (ex: itens atrelados ao pedido)
        echo "<script>alert('Não foi possível excluir o pedido pois existem itens vinculados a ele.');</script>";
    }
    
    // Recarrega a própria página limpando a URL de forma segura
    echo "<script>window.location.href = 'producao.php';</script>";
}

// 3. CAPTURA DOS FILTROS (Filtro por status)

// Captura o status selecionado no filtro (se não tiver nenhum, o padrão é 'Todos')
$statusFiltro = $_GET['status'] ?? 'Todos';

// ID fixo da empresa para o TCC
$idEmpresa = 1; 

$stmtLogo = $pdo->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
$stmtLogo->execute([$idEmpresa]);
$logo_empresa = $stmtLogo->fetchColumn() ?: '';

// Monta a query SQL dinamicamente baseado no filtro (trazendo o nome como nome_cliente)
if ($statusFiltro && $statusFiltro !== 'Todos') {
    $sql = "SELECT p.*, c.nome AS nome_cliente FROM pedidos p 
            JOIN clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_empresa = ? AND p.status = ? 
            ORDER BY p.id_pedido DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idEmpresa, $statusFiltro]);
} else {
    $sql = "SELECT p.*, c.nome AS nome_cliente FROM pedidos p 
            JOIN clientes c ON p.id_cliente = c.id_cliente 
            WHERE p.id_empresa = ? 
            ORDER BY p.id_pedido DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idEmpresa]);
}

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produção | PedidoFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/produtos.css">
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
            <a href="dashboard.php">
                <i class="bi bi-grid-fill"></i>
                Dashboard
            </a>
        </li>

        <li class="<?= $pagina_atual == 'pedidos.php' ? 'active' : '' ?>">
            <a href="pedidos.php">
                <i class="bi bi-cart-fill"></i>
                Pedidos
            </a>
        </li>

        <li class="<?= $pagina_atual == 'producao.php' ? 'active' : '' ?>">
            <a href="producao.php">
                <i class="bi bi-fire"></i>
                Produção
            </a>
        </li>

        <li class="<?= $pagina_atual == 'produtos.php' ? 'active' : '' ?>">
            <a href="produtos.php">
                <i class="bi bi-box-seam-fill"></i>
                Produtos
            </a>
        </li>

        <li class="<?= $pagina_atual == 'relatorios.php' ? 'active' : '' ?>">
            <a href="relatorios.php">
                <i class="bi bi-bar-chart-fill"></i>
                Relatórios
            </a>
        </li>

        <li class="<?= $pagina_atual == 'configuracoes.php' ? 'active' : '' ?>">
            <a href="configuracoes.php">
                <i class="bi bi-gear-fill"></i>
                Configurações
            </a>
        </li>
    </ul>
</aside>

        <main class="col-lg-10 main-content">
            <div class="mb-4">
                <h2>Painel da Produção</h2>
                <p class="text-secondary">Acompanhe os pedidos enviados pelo chatbot do WhatsApp em tempo real.</p>
            </div>

            <div class="mb-3">
                <form method="GET" action="">
                    <label for="filtrarStatus" class="form-label"><strong>Filtrar por Status:</strong></label>
                    <select name="status" id="filtrarStatus" class="form-select" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                        <option value="Todos" <?php echo $statusFiltro == 'Todos' ? 'selected' : ''; ?>>Todos os Pedidos</option>
                        <option value="Pendente" <?php echo $statusFiltro == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                        <option value="Em Preparo" <?php echo $statusFiltro == 'Em Preparo' ? 'selected' : ''; ?>>Em Preparo</option>
                        <option value="Saiu para Entrega" <?php echo $statusFiltro == 'Saiu para Entrega' ? 'selected' : ''; ?>>Saiu para Entrega</option>
                        <option value="Finalizado" <?php echo $statusFiltro == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                    </select>
                </form>
            </div>
            
            <div class="dashboard-box" style="background: white; padding: 20px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,.08);">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="padding: 15px;">ID Pedido</th>
                            <th scope="col">Cliente (WhatsApp)</th>
                            <th scope="col">Valor Total</th>
                            <th scope="col">Status</th>
                            <th scope="col" style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($pedidos)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">Nenhum pedido encontrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td style="padding: 15px;"><strong>#<?= $pedido['id_pedido'] ?></strong></td>
                                <td><?= htmlspecialchars($pedido['nome_cliente']) ?></td>
                                <td>R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></td>
                                <td>
                                    <span class="badge bg-warning text-dark" style="padding: 8px 12px; border-radius: 8px;">
                                        <?= htmlspecialchars($pedido['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="editar_pedido.php?id=<?php echo $pedido['id_pedido']; ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-fill"></i> Editar
                                    </a>

                                    <a href="producao.php?acao=deletar&id=<?= $pedido['id_pedido'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja realmente excluir este pedido da produção?')">
                                        <i class="bi bi-trash-fill"></i> Excluir
                                    </a>

                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
