// ==========================================
// PEDIDOS.JS - VERSÃO CORRIGIDA E PROTEGIDA
// ==========================================

let todosOsPedidos = []; // Guarda a lista vinda do banco

function carregarPedidos() {
    fetch("api/pedidos.php?id_empresa=1")
        .then(res => res.json())
        .then(data => {
            // 1. Atualiza os números dos cards dinamicamente
            if (document.getElementById("cardHoje")) document.getElementById("cardHoje").innerText = data.totais.hoje;
            if (document.getElementById("cardPreparo")) document.getElementById("cardPreparo").innerText = data.totais.preparo;
            if (document.getElementById("cardEntrega")) document.getElementById("cardEntrega").innerText = data.totais.entrega;
            if (document.getElementById("cardFinalizados")) document.getElementById("cardFinalizados").innerText = data.totais.finalizados;

            // 2. Atualiza a nossa lista global e renderiza
            todosOsPedidos = data.pedidos;
            filtrarTabela(); 
        })
        .catch(err => {
            console.error("Erro ao carregar pedidos da API:", err);
        });
}

// ==========================================
// SISTEMA DE FILTRAGEM MULTI-CAMPO
// ==========================================
function filtrarTabela() {
    const inputPesquisa = document.getElementById("filtroPesquisa");
    const selectStatus = document.getElementById("filtroStatus");
    const selectPagamento = document.getElementById("filtroPagamento");
    const inputData = document.getElementById("filtroData");

    const termo = inputPesquisa ? inputPesquisa.value.toLowerCase() : "";
    const statusSel = selectStatus ? selectStatus.value : "Todos";
    const pagamentoSel = selectPagamento ? selectPagamento.value : "Todos";
    const dataSel = inputData ? inputData.value : "";

    // Filtra os dados combinando todas as opções escolhidas na tela
    const pedidosFiltrados = todosOsPedidos.filter(p => {
        const bateTexto = p.cliente_nome.toLowerCase().includes(termo) || p.id_pedido.toString().includes(termo);
        
        // Trata "Em preparo" e "Em entrega" para ignorar maiúsculas/minúsculas
        let statusBanco = p.status ? p.status.toLowerCase() : "";
        let statusFiltro = statusSel.toLowerCase();
        if (statusFiltro === "em entrega") statusFiltro = "saiu para entrega"; // Ajuste para bater com o banco

        const bateStatus = (statusSel === "Todos") || (statusBanco === statusFiltro);
        const batePagamento = (pagamentoSel === "Todos") || (p.pagamento.toLowerCase() === pagamentoSel.toLowerCase());
        
        let bateData = true;
        if (dataSel) {
            const dataPedidoFormato = p.data_completa ? p.data_completa.split(' ')[0] : ''; 
            bateData = (dataPedidoFormato === dataSel);
        }

        return bateTexto && bateStatus && batePagamento && bateData;
    });

    // Atualiza o contador vermelho da tabela
    const badgeTotal = document.getElementById("badgeTotalLista");
    if (badgeTotal) {
        badgeTotal.innerText = `Total: ${pedidosFiltrados.length} Pedidos`;
    }

    // Monta o HTML limpo (garantindo que comece vazio)
    let html = "";
    if (pedidosFiltrados.length === 0) {
        html = `<tr><td colspan="9" style="text-align: center; padding: 20px; color: #888;">Nenhum pedido encontrado.</td></tr>`;
    } else {
        pedidosFiltrados.forEach(p => {
            let classeCor = 'bg-secondary text-white';
            switch (p.status) {
                case 'Pendente': classeCor = 'bg-warning text-dark'; break;
                case 'Em Preparo': case 'Em preparo': classeCor = 'bg-primary text-white'; break;
                case 'Saiu para Entrega': case 'Em entrega': classeCor = 'bg-info text-dark'; break;
                case 'Finalizado': classeCor = 'bg-success text-white'; break;
            }

            html += `
            <tr>
                <td>#${p.id_pedido}</td>
                <td>${p.cliente_nome}</td>
                <td>${p.telefone ?? '-'}</td>
                <td><span class="badge bg-light text-dark border">${p.produto}</span></td>
                <td><strong>R$ ${Number(p.valor_total).toFixed(2).replace('.', ',')}</strong></td>
                <td>${p.pagamento ?? '-'}</td>
                <td>
                    <span class="badge ${classeCor} p-2" style="font-weight: bold; min-width: 90px; display: inline-block; text-align: center;">
                        ${p.status}
                    </span>
                </td>
                <td>${p.data_pedido ?? ''}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary btn-visualizar">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </td>
            </tr>
            `;
        });
    }

    // Injeta o HTML resetando o antigo (isso impede a duplicação!)
    document.getElementById("tabelaPedidos").innerHTML = html;
    
    // Executa a função corrigida dos cliques
    ativarEventos(); 
}

// ==========================================
// VISUALIZAR PEDIDO (CORRIGIDA SEM "C")
// ==========================================
function ativarEventos() {
    document.querySelectorAll(".btn-visualizar").forEach(btn => {
        // Remove ouvintes antigos clonando o botão para não acumular cliques nas repetições
        const novoBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(novoBtn, btn);

        novoBtn.addEventListener("click", function () {
            let linha = this.closest("tr");

            let cliente = linha.cells[1].innerText;
            let telefone = linha.cells[2].innerText;
            let total = linha.cells[4].innerText;
            let status = linha.cells[6].innerText;

            alert(
`PEDIDO REALIZADO

Cliente: ${cliente}
Telefone: ${telefone}
Total: ${total}
Status: ${status}`
            );
        });
    });
}

// ==========================================
// INICIALIZAÇÃO
// ==========================================
carregarPedidos();