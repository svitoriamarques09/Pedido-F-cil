// ======================================
// ANIMAÇÃO DOS CARDS
// ======================================

const cards = document.querySelectorAll(".card-dashboard h2");

cards.forEach(card => {

    const texto = card.innerText;

    const isMoeda = texto.includes("R$");
    const numero = isMoeda
        ? Number(texto.replace("R$", "").replace(/\./g, "").replace(",", ".").trim())
        : parseInt(texto.replace(/\D/g, ""));

    if (!isNaN(numero)) {

        let contador = 0;

        const incremento = Math.max(numero / 45, 1);

        const intervalo = setInterval(() => {

            contador += incremento;

            if (contador >= numero) {

                contador = numero;
                clearInterval(intervalo);

            }

            if (isMoeda) {

                card.innerText = contador.toLocaleString("pt-BR", {
                    style: "currency",
                    currency: "BRL"
                });

            } else {

                card.innerText = Math.round(contador);

            }

        }, 20);

    }

});


// ======================================
// GRÁFICO DE FATURAMENTO
// ======================================

const salesCanvas = document.getElementById("salesChart");
const dashboardData = window.dashboardData || {};

if (salesCanvas) {

    new Chart(salesCanvas, {

        type: "line",

        data: {

            labels: dashboardData.faturamentoLabels || [],

            datasets: [{

                label: "Faturamento",

                data: dashboardData.faturamentoValores || [],

                borderColor: "#b22222",

                backgroundColor: "rgba(178,34,34,0.15)",

                fill: true,

                tension: .4

            }]

        },

        options: {

            responsive: true,

            plugins: {

                legend: {

                    display: false

                }

            }

        }

    });

}


// ======================================
// PRODUTOS MAIS VENDIDOS
// ======================================

const productCanvas = document.getElementById("productChart");

if (productCanvas) {

    const labelsProdutos = dashboardData.produtosLabels || [];
    const dadosProdutos = dashboardData.produtosValores || [];

    new Chart(productCanvas, {

        type: "doughnut",

        data: {

            labels: labelsProdutos.length ? labelsProdutos : ["Sem vendas"],

            datasets: [{

                data: dadosProdutos.length ? dadosProdutos : [1],

                backgroundColor: [

                    "#b22222",
                    "#ff9800",
                    "#4caf50",
                    "#2196f3"

                ]

            }]

        },

        options: {

            responsive: true,

            plugins: {

                legend: {

                    position: "bottom"

                }

            }

        }

    });

}


// ======================================
// PESQUISA NA TABELA
// ======================================

const pesquisa = document.querySelector(".search input");

if (pesquisa) {

    pesquisa.addEventListener("keyup", function () {

        const valor = pesquisa.value.toLowerCase();

        const linhas = document.querySelectorAll("tbody tr");

        linhas.forEach(linha => {

            linha.style.display = linha.innerText
                .toLowerCase()
                .includes(valor)

                ? ""

                : "none";

        });

    });

}


// ======================================
// BOTÕES VER
// ======================================

const botoes = document.querySelectorAll(".btn-outline-danger");

botoes.forEach(botao => {

    if (botao.innerText.trim() === "Ver") {

        botao.addEventListener("click", () => {

            alert("Aqui abrirá os detalhes do pedido.");

        });

    }

});


// ======================================
// DATA ATUAL
// ======================================

const hoje = new Date();

console.log(

    "Sistema iniciado em:",

    hoje.toLocaleDateString("pt-BR")

);


// ======================================
// BOAS-VINDAS
// ======================================

setTimeout(() => {

    console.log("Bem-vindo ao Dashboard do PedidoFácil!");

}, 1000);
