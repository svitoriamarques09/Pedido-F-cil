<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); 
header('Content-Type: application/json; charset=utf-8');

require_once("../config/conexao.php");

$idEmpresa = $_GET['id_empresa'] ?? 1;

try {
    // 1. CONTA OS TOTAIS PARA OS CARDS
    // Total de hoje
    $pHoje = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id_empresa = ? AND DATE(data_pedido) = CURDATE()");
    $pHoje->execute([$idEmpresa]);
    $totalHoje = $pHoje->fetchColumn();

    // Em Preparo
    $pPreparo = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id_empresa = ? AND status = 'Em Preparo'");
    $pPreparo->execute([$idEmpresa]);
    $totalPreparo = $pPreparo->fetchColumn();

    // Em Entrega / Saiu para Entrega
    $pEntrega = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id_empresa = ? AND status = 'Saiu para Entrega'");
    $pEntrega->execute([$idEmpresa]);
    $totalEntrega = $pEntrega->fetchColumn();

    // Finalizados
    $pFinalizados = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE id_empresa = ? AND status = 'Finalizado'");
    $pFinalizados->execute([$idEmpresa]);
    $totalFinalizados = $pFinalizados->fetchColumn();


    // 2. BUSCA A LISTA DE PEDIDOS REAL
    // Mude o SELECT da sua consulta para ficar exatamente assim:
    $sql = "SELECT p.id_pedido, p.valor_total, p.status, p.data_pedido, 
               p.data_pedido AS data_completa,
               c.nome AS cliente_nome, c.telefone 
        FROM pedidos p 
        JOIN clientes c ON p.id_cliente = c.id_cliente 
        WHERE p.id_empresa = ? 
        ORDER BY p.id_pedido DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idEmpresa]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pedidos as &$p) {
        $p['produto'] = "Salgados"; 
        $p['pagamento'] = "WhatsApp"; 
        $p['data_pedido'] = $p['data_pedido'] ? date('H:i', strtotime($p['data_pedido'])) : '-';
    }

    // Retorna os totais junto com a lista de pedidos empacotados em um único JSON!
    echo json_encode([
        "totais" => [
            "hoje" => $totalHoje,
            "preparo" => $totalPreparo,
            "entrega" => $totalEntrega,
            "finalizados" => $totalFinalizados
        ],
        "pedidos" => $pedidos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}