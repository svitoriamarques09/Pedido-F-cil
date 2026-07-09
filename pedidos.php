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
$idPedido = $_GET["id"] ?? 0;

$stmtLogo = $pdo->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
$stmtLogo->execute([$idEmpresa]);
$logo_empresa = $stmtLogo->fetchColumn() ?: '';


/*
==========================================
DADOS DO PEDIDO
==========================================
*/

$sql = $pdo->prepare("
SELECT
    p.*,
    c.nome,
    c.telefone

FROM pedidos p

LEFT JOIN clientes c
ON c.id_cliente = p.id_cliente

WHERE
    p.id_pedido = ?
AND
    p.id_empresa = ?
");

$sql->execute([$idPedido, $idEmpresa]);

$pedido = $sql->fetch(PDO::FETCH_ASSOC);


/*
==========================================
ITENS DO PEDIDO
==========================================
*/

$sql = $pdo->prepare("
SELECT
    pi.quantidade,
    pi.preco_unitario,
    pr.nome
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pedidos | PedidoFácil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="assets/css/pedidos.css">

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

    <!-- CONTEÚDO -->

    <main class="col-lg-10 main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2>Pedidos</h2>

                <p class="text-secondary">

                    Gerencie todos os pedidos recebidos pelo chatbot.

                </p>

            </div>

        </div>

        <!-- ==========================
        CARDS DE RESUMO
        =========================== -->
            <div class="row g-4 mb-4">

            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Pedidos Hoje</span>
                        <h3 id="cardHoje">0</h3>
                    </div>
                    <div class="card-icon bg-primary">
                        <i class="bi bi-cart-fill"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Em Preparo</span>
                        <h3 id="cardPreparo">0</h3>
                    </div>
                    <div class="card-icon bg-warning">
                        <i class="bi bi-fire"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Em Entrega</span>
                        <h3 id="cardEntrega">0</h3>
                    </div>
                    <div class="card-icon bg-info">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card-dashboard">
                    <div class="card-info">
                        <span>Finalizados</span>
                        <h3 id="cardFinalizados">0</h3>
                    </div>
                    <div class="card-icon bg-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>

        </div>

        <div class="dashboard-box mb-4">

            <div class="row g-3 align-items-end">

                <div class="col-lg-4">
                    <label class="form-label">Pesquisar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            id="filtroPesquisa"
                            class="form-control"
                            placeholder="Cliente ou nº do pedido"
                            oninput="filtrarTabela()">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Status</label>
                    <select id="filtroStatus" class="form-select" onchange="filtrarTabela()">
                        <option value="Todos">Todos</option>
                        <option value="Pendente">Pendente</option>
                        <option value="Em Preparo">Em preparo</option>
                        <option value="Saiu para Entrega">Em entrega</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Pagamento</label>
                    <select id="filtroPagamento" class="form-select" onchange="filtrarTabela()">
                        <option value="Todos">Todos</option>
                        <option value="Pix">Pix</option>
                        <option value="Cartão">Cartão</option>
                        <option value="Dinheiro">Dinheiro</option>
                    </select>
                </div>

                <div class="col-lg-2">
                    <label class="form-label">Data</label>
                    <input type="date" id="filtroData" class="form-control" oninput="filtrarTabela()">
                </div>

            </div>

        </div>

        <div class="dashboard-box">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Lista de Pedidos</h4>
                <span class="badge bg-danger" id="badgeTotalLista">Total: 0 Pedidos</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>WhatsApp</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th>Horário</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                   <tbody id="tabelaPedidos"></tbody>
                </table>
            </div>

        </div>
    </main>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let todosOsPedidos = []; 

function carregarPedidos() {
    fetch("api/pedidos.php?id_empresa=1")
        .then(res => res.json())
        .then(data => {
            // Atualiza os cards
            if (document.getElementById("cardHoje")) document.getElementById("cardHoje").innerText = data.totais.hoje;
            if (document.getElementById("cardPreparo")) document.getElementById("cardPreparo").innerText = data.totais.preparo;
            if (document.getElementById("cardEntrega")) document.getElementById("cardEntrega").innerText = data.totais.entrega;
            if (document.getElementById("cardFinalizados")) document.getElementById("cardFinalizados").innerText = data.totais.finalizados;

            // Salva e renderiza
            todosOsPedidos = data.pedidos;
            filtrarTabela(); 
        })
        .catch(err => console.error("Erro ao sincronizar dashboard:", err));
}

function filtrarTabela() {
    const inputPesquisa = document.getElementById("filtroPesquisa");
    const selectStatus = document.getElementById("filtroStatus");
    const selectPagamento = document.getElementById("filtroPagamento");
    const inputData = document.getElementById("filtroData");

    const termo = inputPesquisa ? inputPesquisa.value.toLowerCase().trim() : "";
    const statusSel = selectStatus ? selectStatus.value : "Todos";
    const pagamentoSel = selectPagamento ? selectPagamento.value : "Todos";
    const dataSel = inputData ? inputData.value : "";

    const pedidosFiltrados = todosOsPedidos.filter(p => {
        const nomeCliente = p.cliente_nome ? p.cliente_nome.toLowerCase() : "";
        const idPedido = p.id_pedido ? p.id_pedido.toString() : "";
        const bateTexto = nomeCliente.includes(termo) || idPedido.includes(termo);
        
        let statusBanco = p.status ? p.status.toLowerCase().trim() : "";
        let statusFiltro = statusSel.toLowerCase().trim();
        if (statusFiltro === "em entrega") statusFiltro = "saiu para entrega";
        const bateStatus = (statusSel === "Todos") || (statusBanco === statusFiltro);
        
        let pagBanco = p.pagamento ? p.pagamento.toLowerCase().trim() : "whatsapp";
        let pagFiltro = pagamentoSel.toLowerCase().trim();
        const batePagamento = (pagamentoSel === "Todos") || (pagBanco === pagFiltro);
        
        let bateData = true;
        if (dataSel) {
            const dataPedidoFormato = p.data_completa ? p.data_completa.split(' ')[0] : ''; 
            bateData = (dataPedidoFormato === dataSel);
        }

        return bateTexto && bateStatus && batePagamento && bateData;
    });

    if (document.getElementById("badgeTotalLista")) {
        document.getElementById("badgeTotalLista").innerText = `Total: ${pedidosFiltrados.length} Pedidos`;
    }

    // CRUCIAL: Limpa completamente a tabela antes de recriar as linhas!
    const tabelaBody = document.getElementById("tabelaPedidos");
    if (!tabelaBody) return;
    tabelaBody.innerHTML = ""; 

    if (pedidosFiltrados.length === 0) {
        tabelaBody.innerHTML = `<tr><td colspan="9" style="text-align: center; padding: 20px; color: #888;">Nenhum pedido encontrado.</td></tr>`;
        return;
    }

    let htmllinhas = "";
    pedidosFiltrados.forEach(p => {
        let classeCor = 'bg-secondary text-white';
        let statusExibido = p.status;

        if (p.status === 'Pendente') classeCor = 'bg-warning text-dark';
        else if (p.status === 'Em Preparo' || p.status === 'Em preparo') classeCor = 'bg-primary text-white';
        else if (p.status === 'Saiu para Entrega' || p.status === 'Em entrega') {
            classeCor = 'bg-info text-dark';
            statusExibido = 'Em entrega';
        }
        else if (p.status === 'Finalizado') classeCor = 'bg-success text-white';

        htmllinhas += `
        <tr>
            <td>#${p.id_pedido}</td>
            <td>${p.cliente_nome}</td>
            <td>${p.telefone ?? '-'}</td>
            <td><span class="badge bg-light text-dark border">${p.produto}</span></td>
            <td><strong>R$ ${Number(p.valor_total).toFixed(2).replace('.', ',')}</strong></td>
            <td>${p.pagamento ?? 'WhatsApp'}</td>
            <td>
                <span class="badge ${classeCor} p-2" style="font-weight: bold; min-width: 110px; display: inline-block; text-align: center;">
                    ${statusExibido}
                </span>
            </td>
            <td>${p.data_pedido ?? ''}</td>
            <td>
                <button class="btn btn-sm btn-outline-warning btn-visualizar">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </td>
        </tr>
        `;
    });

    tabelaBody.innerHTML = htmllinhas;
    ativarEventos(); 
}

function ativarEventos() {
    document.querySelectorAll(".btn-visualizar").forEach(btn => {
        const novoBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(novoBtn, btn);
        novoBtn.addEventListener("click", function () {
            let linha = this.closest("tr");
            alert(`PEDIDO\n\nCliente: ${linha.cells[1].innerText}\nTotal: ${linha.cells[4].innerText}\nStatus: ${linha.cells[6].innerText}`);
        });
    });
}

// Inicializa sem acumular múltiplos intervalos
if (window.timerDashboard) clearInterval(window.timerDashboard);
carregarPedidos();
window.timerDashboard = setInterval(carregarPedidos, 5000);
</script>

</body>
</html>
