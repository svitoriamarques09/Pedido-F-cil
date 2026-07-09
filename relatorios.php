<?php
session_start();

// Inclui a sua conexão com o banco de dados
require_once 'config/conexao.php';

// Presume-se que o arquivo conexao.php define a variável $pdo como a instância do PDO

$pagina_atual = basename($_SERVER['PHP_SELF']);

$stmtLogo = $pdo->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
$stmtLogo->execute([$_SESSION['id_empresa'] ?? 1]);
$logo_empresa = $stmtLogo->fetchColumn() ?: '';

// 1. Capturar e sanitizar os filtros enviados via GET/POST
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'este_mes';
$tipo_relatorio = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// Definir o intervalo de datas com base no período selecionado
$data_inicio = date('Y-m-d 00:00:00');
$data_fim = date('Y-m-d 23:59:59');

switch ($periodo) {
    case 'hoje':
        $data_inicio = date('Y-m-d 00:00:00');
        break;
    case '7_dias':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
        break;
    case '30_dias':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-30 days'));
        break;
    case 'este_mes':
    default:
        $data_inicio = date('Y-m-01 00:00:00');
        break;
}

// Cláusula base de filtragem por status e data
$where_status = "WHERE status != 'Cancelado' AND data_pedido BETWEEN :data_inicio AND :data_fim";

// Se filtrar por um tipo específico (ex: simulação de subcategorias)
if ($tipo_relatorio !== 'todos') {
    // Pode expandir regras aqui se houver uma coluna de tipo na tabela pedidos
}

try {
    // ----------------------------------------------------
    // CONSULTA 1: Cards Indicadores (Faturamento, Total Pedidos, Clientes Únicos)
    // ----------------------------------------------------
    $sql_cards = "SELECT 
                    SUM(valor_total) as faturamento, 
                    COUNT(id_pedido) as total_pedidos,
                    COUNT(DISTINCT id_cliente) as total_clientes
                  FROM pedidos 
                  $where_status";
    
    $stmt = $pdo->prepare($sql_cards);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $dados_cards = $stmt->fetch(PDO::FETCH_ASSOC);

    $faturamento = !empty($dados_cards['faturamento']) ? $dados_cards['faturamento'] : 0;
    $total_pedidos = !empty($dados_cards['total_pedidos']) ? $dados_cards['total_pedidos'] : 0;
    $total_clientes = !empty($dados_cards['total_clientes']) ? $dados_cards['total_clientes'] : 0;
    $ticket_medio = $total_pedidos > 0 ? ($faturamento / $total_pedidos) : 0;

    // ----------------------------------------------------
    // CONSULTA 2: Lista Detalhada de Pedidos (Tabela)
    // ----------------------------------------------------
    // Faz o INNER JOIN com clientes para pegar o nome
    $sql_tabela = "SELECT p.*, c.nome as nome_cliente 
                   FROM pedidos p
                   LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                   WHERE p.data_pedido BETWEEN :data_inicio AND :data_fim
                   ORDER BY p.data_pedido DESC";
    $stmt_tabela = $pdo->prepare($sql_tabela);
    $stmt_tabela->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $pedidos_detalhado = $stmt_tabela->fetchAll(PDO::FETCH_ASSOC);

    // ----------------------------------------------------
    // CONSULTA 3: Ranking de Produtos Mais Vendidos
    // ----------------------------------------------------
    // Unifica itens_pedido ou pedido_itens dependendo de qual tabela está populada
    $tabela_itens = 'itens_pedido'; // Ajuste aqui para 'pedido_itens' se for a tabela ativa
    $sql_ranking = "SELECT pr.nome as nome_produto, SUM(it.quantidade) as total_vendido
                    FROM $tabela_itens it
                    INNER JOIN pedidos p ON it.id_pedido = p.id_pedido
                    INNER JOIN produtos pr ON it.id_produto = pr.id_produto
                    WHERE p.status != 'Cancelado' AND p.data_pedido BETWEEN :data_inicio AND :data_fim
                    GROUP BY it.id_produto
                    ORDER BY total_vendido DESC
                    LIMIT 5";
    $stmt_ranking = $pdo->prepare($sql_ranking);
    $stmt_ranking->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $produtos_ranking = $stmt_ranking->fetchAll(PDO::FETCH_ASSOC);

    // ----------------------------------------------------
    // CONSULTA 4: Dados do Gráfico de Vendas Semanais
    // ----------------------------------------------------
    $dias_semana = ['Seg' => 0, 'Ter' => 0, 'Qua' => 0, 'Qui' => 0, 'Sex' => 0, 'Sáb' => 0, 'Dom' => 0];
    $sql_grafico_vendas = "SELECT DAYNAME(data_pedido) as dia, SUM(valor_total) as total_dia 
                           FROM pedidos 
                           WHERE status != 'Cancelado' AND data_pedido >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                           GROUP BY DAYOFWEEK(data_pedido)";
    $stmt_g_vendas = $pdo->query($sql_grafico_vendas);
    while($row = $stmt_g_vendas->fetch(PDO::FETCH_ASSOC)) {
        // Mapeia o retorno em inglês do MySQL para as labels do JS
        $mapa_dias = ['Monday'=>'Seg','Tuesday'=>'Ter','Wednesday'=>'Qua','Thursday'=>'Qui','Friday'=>'Sex','Saturday'=>'Sáb','Sunday'=>'Dom'];
        if(isset($mapa_dias[$row['dia']])) {
            $dias_semana[$mapa_dias[$row['dia']]] = (float)$row['total_dia'];
        }
    }

    // ----------------------------------------------------
    // CONSULTA 5: Dados do Gráfico de Categorias (Porcentagem)
    // ----------------------------------------------------
    $sql_categorias = "SELECT c.nome as categoria, COUNT(it.id_item) as qtd
                       FROM $tabela_itens it
                       INNER JOIN produtos pr ON it.id_produto = pr.id_produto
                       INNER JOIN categorias c ON pr.id_categoria = c.id_categoria
                       INNER JOIN pedidos p ON it.id_pedido = p.id_pedido
                       WHERE p.status != 'Cancelado'
                       GROUP BY pr.id_categoria";
    $stmt_cat = $pdo->query($sql_categorias);
    $labels_cat = [];
    $dados_cat = [];
    while($row = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
        $labels_cat[] = $row['categoria'];
        $dados_cat[] = (int)$row['qtd'];
    }

} catch (PDOException $e) {
    die("Erro ao carregar os relatórios: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios | PedidoFácil</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="assets/css/relatorios.css">
</head>

<body>

    <aside class="sidebar">
        <div class="logo">
            <?php if (!empty($logo_empresa) && file_exists(__DIR__ . '/' . $logo_empresa)): ?>
                <img src="<?= htmlspecialchars($logo_empresa) ?>" alt="Logo da empresa" class="sidebar-logo-img">
            <?php else: ?>
                <i class="bi bi-chat-dots-fill"></i>
            <?php endif; ?>
            <h3>PedidoFácil</h3>
        </div>
        <ul>
            <li><a href="dashboard.php"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
            <li><a href="pedidos.php"><i class="bi bi-cart-fill"></i> Pedidos</a></li>
            <li><a href="producao.php"><i class="bi bi-fire"></i> Produção</a></li>
            <li><a href="produtos.php"><i class="bi bi-box-seam-fill"></i> Produtos</a></li>
            <li class="active"><a href="relatorios.php"><i class="bi bi-bar-chart-fill"></i> Relatórios</a></li>
            <li><a href="configuracoes.php"><i class="bi bi-gear-fill"></i> Configurações</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Relatórios</h2>
                <p class="text-secondary">Acompanhe os principais indicadores vindos do seu chatbot e balcão.</p>
                <p id="relogio" class="text-muted"></p>
            </div>
            <form action="exportar_pdf.php" method="POST" target="_blank">
                <input type="hidden" name="periodo" value="<?= $periodo ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Exportar PDF da Tela
                </button>
            </form>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Faturamento</span>
                        <h3>R$ <?= number_format($faturamento, 2, ',', '.') ?></h3>
                    </div>
                    <div class="card-icon bg-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Pedidos</span>
                        <h3><?= $total_pedidos ?></h3>
                    </div>
                    <div class="card-icon bg-primary">
                        <i class="bi bi-cart-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Clientes Atendidos</span>
                        <h3><?= $total_clientes ?></h3>
                    </div>
                    <div class="card-icon bg-warning">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Ticket Médio</span>
                        <h3>R$ <?= number_format($ticket_medio, 2, ',', '.') ?></h3>
                    </div>
                    <div class="card-icon bg-danger">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-box mb-4">
            <form method="GET" action="relatorios.php" class="row g-3 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Período</label>
                    <select name="periodo" class="form-select">
                        <option value="hoje" <?= $periodo == 'hoje' ? 'selected' : '' ?>>Hoje</option>
                        <option value="7_dias" <?= $periodo == '7_dias' ? 'selected' : '' ?>>Últimos 7 dias</option>
                        <option value="este_mes" <?= $periodo == 'este_mes' ? 'selected' : '' ?>>Este mês</option>
                        <option value="30_dias" <?= $periodo == '30_dias' ? 'selected' : '' ?>>Últimos 30 dias</option>
                    </select>
                </div>
                <div class="col-lg-3">
                    <label class="form-label fw-bold">Tipo de Relatório</label>
                    <select name="tipo" class="form-select">
                        <option value="todos" <?= $tipo_relatorio == 'todos' ? 'selected' : '' ?>>Todos</option>
                        <option value="chatbot" <?= $tipo_relatorio == 'chatbot' ? 'selected' : '' ?>>Apenas Chatbot</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-bold">Filtrar na Tabela Abaixo</label>
                    <input type="text" id="pesquisaRelatorio" class="form-control" placeholder="Digite o nome do cliente ou status...">
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="bi bi-funnel-fill"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="dashboard-box mb-4">
            <h4 class="mb-3">Resumo do Período</h4>
            <p class="text-secondary">
                Durante o período selecionado foram registrados <strong><?= $total_pedidos ?> pedidos</strong>, gerando um faturamento total de <strong>R$ <?= number_format($faturamento, 2, ',', '.') ?></strong>. 
                O ticket médio das requisições geradas de forma automatizada pelo chatbot foi avaliado em <strong>R$ <?= number_format($ticket_medio, 2, ',', '.') ?></strong> por cliente ativo.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="dashboard-box">
                    <h4>Faturamento da Semana</h4>
                    <canvas id="graficoVendas" height="120"></canvas>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dashboard-box">
                    <h4 class="mb-4"><i class="bi bi-trophy-fill text-warning"></i> Mais Vendidos</h4>
                    <?php if(!empty($produtos_ranking)): ?>
                        <?php foreach($produtos_ranking as $index => $prod): ?>
                            <div class="ranking-item">
                                <div>
                                    <strong><?= ($index+1) ?>° - <?= htmlspecialchars($prod['nome_produto']) ?></strong><br>
                                    <small class="text-secondary"><?= $prod['total_vendido'] ?> unidades vendidas</small>
                                </div>
                                <span class="badge bg-danger"><?= $prod['total_vendido'] ?></span>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Sem vendas no período.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-lg-6">
                <div class="dashboard-box">
                    <h4 class="mb-3">Categorias de maior Demandadas</h4>
                    <canvas id="graficoCategorias" height="220"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="dashboard-box">
                    <h4 class="mb-4">Insights de Operação</h4>
                    <div class="alert alert-success">
                        <strong>📈 Eficiência Operacional</strong><br>
                        Os relatórios apontam processamento fluído das requisições via banco.
                    </div>
                    <div class="alert alert-primary">
                        <strong>🚚 Automação do Chatbot</strong><br>
                        Os dados mostram a contagem de clientes integrados em tempo real.
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-box mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Relatório Detalhado</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Data/Hora</th>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($pedidos_detalhado)): ?>
                            <?php foreach($pedidos_detalhado as $pedido): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?></td>
                                    <td>#<?= $pedido['id_pedido'] ?></td>
                                    <td><?= htmlspecialchars($pedido['nome_cliente'] ?? 'Cliente WhatsApp') ?></td>
                                    <td>R$ <?= number_format($pedido['valor_total'], 2, ',', '.') ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = 'bg-secondary';
                                        if($pedido['status'] == 'Finalizado' || $pedido['status'] == 'Entregue') $badge_class = 'bg-success';
                                        if($pedido['status'] == 'Em preparo') $badge_class = 'bg-warning text-dark';
                                        if($pedido['status'] == 'Cancelado') $badge_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($pedido['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Nenhum pedido encontrado para o período filtrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Injeção segura: se estiver vazio no PHP, envia dados zerados padrão para o JS não quebrar
        const dadosGrificoVendas = <?= json_encode(array_values($dias_semana)) ?>;
        
        const labelsCategorias = <?= !empty($labels_cat) ? json_encode($labels_cat) : json_encode(["Sem Vendas"]) ?>;
        const dadosCategorias = <?= !empty($dados_cat) ? json_encode($dados_cat) : json_encode([1]) ?>;
    </script>
    <script src="relatorios.js"></script>
    <script src="assets/js/relatorio.js"></script>
</body>
</html>
