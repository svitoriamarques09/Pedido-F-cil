// ==========================================
// RELATORIOS.JS - PedidoFácil
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    criarGraficoVendas();
    criarGraficoCategorias();
    atualizarRelogio();
    pesquisarTabela();
    configurarBotoes();

});

// ==========================================
// GRÁFICO DE VENDAS DINÂMICO
// ==========================================
// ==========================================
// GRÁFICO DE VENDAS REESTILIZADO (BARRAS + REAL R$)
// ==========================================
function criarGraficoVendas() {
    const ctx = document.getElementById("graficoVendas");
    if (!ctx) return;

    // Se a variável não existir, cria um array zerado para os 7 dias
    const dadosVendas = (typeof dadosGrificoVendas !== 'undefined' && dadosGrificoVendas.length > 0) 
        ? dadosGrificoVendas 
        : [0, 0, 0, 0, 0, 0, 0];

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Seg", "Ter", "Qua", "Qui", "Sex", "Sáb", "Dom"],
            datasets: [{
                label: "Faturamento Diário",
                data: dadosVendas,
                backgroundColor: "#C62828",
                borderRadius: 8,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw || 0;
                            return " Faturamento: " + value.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString("pt-BR", { style: "currency", currency: "BRL", maximumFractionDigits: 0 });
                        }
                    }
                }
            }
        }
    });
}

// ==========================================
// GRÁFICO DE CATEGORIAS DINÂMICO
// ==========================================
function criarGraficoCategorias() {
    const ctx = document.getElementById("graficoCategorias");
    if (!ctx) return;

    // Define rótulos padrão se o banco estiver sem registros
    const labels = (typeof labelsCategorias !== 'undefined' && labelsCategorias.length > 0) ? labelsCategorias : ["Nenhum pedido"];
    const dados = (typeof dadosCategorias !== 'undefined' && dadosCategorias.length > 0) ? dadosCategorias : [1];
    const cores = (typeof labelsCategorias !== 'undefined' && labelsCategorias.length > 0) 
        ? ["#C62828", "#FF9800", "#2196F3", "#4CAF50", "#9C27B0"] 
        : ["#e0e0e0"]; // Cinza se estiver vazio

    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: labels,
            datasets: [{
                data: dados,
                backgroundColor: cores,
                borderWidth: 2,
                borderColor: "#ffffff"
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { 
                    position: "bottom",
                    labels: { font: { size: 13 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw || 0;
                            if(context.label === "Nenhum pedido") return " Sem dados cadastrados";
                            return " " + context.label + ": " + value + " itens pedidos";
                        }
                    }
                }
            }
        }
    });
}

// ==========================================
// PESQUISA (Ajustado para o ID específico)
// ==========================================

function pesquisarTabela() {

    // Ajustado para o ID do HTML para evitar bugs com outros inputs
    const pesquisa = document.getElementById("pesquisaRelatorio");

    if (!pesquisa) return;

    pesquisa.addEventListener("keyup", function () {

        const texto = this.value.toLowerCase();

        const linhas = document.querySelectorAll("tbody tr");

        linhas.forEach(function (linha) {

            linha.style.display =

                linha.innerText.toLowerCase().includes(texto)

                    ? ""

                    : "none";

        });

    });

}

// ==========================================
// BOTÕES
// ==========================================

function configurarBotoes() {

    const botoes = document.querySelectorAll("button");

    botoes.forEach(function (botao) {

        const texto = botao.innerText.toLowerCase();

        if (texto.includes("pdf")) {

            botao.addEventListener("click", function () {

                alert("Relatório em PDF gerado com sucesso! (Simulação)");

            });

        }

        if (texto.includes("excel")) {

            botao.addEventListener("click", function () {

                alert("Relatório em Excel gerado com sucesso! (Simulação)");

            });

        }

        if (texto.includes("imprimir")) {

            botao.addEventListener("click", function () {

                window.print();

            });

        }

    });

}

// ==========================================
// RELÓGIO
// ==========================================

function atualizarRelogio() {

    const relogio = document.getElementById("relogio");

    if (!relogio) return;

    function atualizar() {

        const agora = new Date();

        relogio.innerHTML =

            agora.toLocaleDateString("pt-BR") +

            " | " +

            agora.toLocaleTimeString("pt-BR");

    }

    atualizar();

    setInterval(atualizar, 1000);

}