<?php
// Ativa a exibição de erros na tela para descobrirmos o problema se persistir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui a sua conexão com o banco de dados
require_once 'config/conexao.php';

$periodo = isset($_POST['periodo']) ? $_POST['periodo'] : 'este_mes';

$data_inicio = date('Y-m-d 00:00:00');
$data_fim = date('Y-m-d 23:59:59');

switch ($periodo) {
    case 'hoje':
        $data_inicio = date('Y-m-d 00:00:00');
        $texto_periodo = "Hoje (" . date('d/m/Y') . ")";
        break;
    case '7_dias':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $texto_periodo = "Últimos 7 dias (" . date('d/m/Y', strtotime('-7 days')) . " até " . date('d/m/Y') . ")";
        break;
    case '30_dias':
        $data_inicio = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $texto_periodo = "Últimos 30 dias (" . date('d/m/Y', strtotime('-30 days')) . " até " . date('d/m/Y') . ")";
        break;
    case 'este_mes':
    default:
        $data_inicio = date('Y-m-01 00:00:00');
        $texto_periodo = "Este Mês (" . date('m/Y') . ")";
        break;
}

try {
    // 1. Buscar Indicadores Gerais (Faturamento e Total de Pedidos)
    $sql_cards = "SELECT SUM(valor_total) as faturamento, COUNT(id_pedido) as total_pedidos 
                  FROM pedidos 
                  WHERE status != 'Cancelado' AND data_pedido BETWEEN :data_inicio AND :data_fim";
    $stmt = $pdo->prepare($sql_cards);
    $stmt->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $faturamento = $dados['faturamento'] ?? 0;
    $total_pedidos = $dados['total_pedidos'] ?? 0;

    // 2. Buscar Tabela de Pedidos Detalhada
    $sql_tabela = "SELECT p.*, c.nome as nome_cliente 
                   FROM pedidos p 
                   LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                   WHERE p.data_pedido BETWEEN :data_inicio AND :data_fim 
                   ORDER BY p.data_pedido DESC";
    $stmt_tab = $pdo->prepare($sql_tabela);
    $stmt_tab->execute([':data_inicio' => $data_inicio, ':data_fim' => $data_fim]);
    $pedidos = $stmt_tab->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div style='color:red; padding:20px; border:1px solid red; background:#fff5f5; font-family:sans-serif;'>
            <h3>Erro de Banco de Dados:</h3>" . $e->getMessage() . "
         </div>");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Vendas - PedidoFácil</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #333; margin: 20px; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #C62828; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #C62828; font-size: 26px; }
        .header p { margin: 5px 0 0 0; color: #666; }
        .meta-info { margin-bottom: 20px; background: #f8f9fa; padding: 10px; border-radius: 6px; }
        .resumo-cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; background: #fff; }
        .card span { font-size: 12px; color: #777; display: block; margin-bottom: 5px; text-transform: uppercase; }
        .card strong { font-size: 20px; color: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; color: #444; font-weight: 600; }
        tr:nth-child(even) { background-color: #fafafa; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; display: inline-block; }
        .bg-success { background: #d4edda; color: #155724; }
        .bg-warning { background: #fff3cd; color: #856404; }
        .bg-danger { background: #f8d7da; color: #721c24; }
        .bg-secondary { background: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>

    <div class="header">
        <h1>PedidoFácil — Relatório Gerencial</h1>
        <p>Dados integrados do sistema automatizado de chatbot e vendas</p>
    </div>

    <div class="meta-info">
        <strong>Período Exportado:</strong> <?= htmlspecialchars($texto_periodo) ?><br>
        <strong>Data de Emissão:</strong> <?= date('d/m/Y H:i:s') ?>
    </div>

    <div class="resumo-cards">
        <div class="card">
            <span>Faturamento Total</span>
            <strong>R$ <?= number_format($faturamento, 2, ',', '.') ?></strong>
        </div>
        <div class="card">
            <span>Total de Pedidos</span>
            <strong><?= $total_pedidos ?></strong>
        </div>
    </div>

    <h3>Detalhamento dos Pedidos</h3>
    <table>
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>ID</th>
                <th>Cliente</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($pedidos)): ?>
                <?php foreach($pedidos as $p): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($p['data_pedido'])) ?></td>
                        <td>#<?= $p['id_pedido'] ?></td>
                        <td><?= htmlspecialchars($p['nome_cliente'] ?? 'Cliente WhatsApp') ?></td>
                        <td>R$ <?= number_format($p['valor_total'], 2, ',', '.') ?></td>
                        <td>
                            <?php 
                            $class = 'bg-secondary';
                            if($p['status'] == 'Finalizado' || $p['status'] == 'Entregue') $class = 'bg-success';
                            if($p['status'] == 'Em preparo') $class = 'bg-warning';
                            if($p['status'] == 'Cancelado') $class = 'bg-danger';
                            ?>
                            <span class="badge <?= $class ?>"><?= htmlspecialchars($p['status']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; color:#999;">Nenhum registro encontrado para este período.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Abre a janela de impressão do sistema
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>