// ==========================================
// PRODUTOS.JS - PedidoFácil
// ==========================================

// Aguarda o carregamento da página
document.addEventListener("DOMContentLoaded", () => {

    carregarProdutos();

    atualizarRelogio();

});



function carregarProdutos(){

fetch("api/produtos/listar.php")
.then(r => r.json())
.then(data => {

    document.querySelector("#totalProdutos").innerText = data.total;

    let container = document.querySelector("#listaProdutos");
    if (!container) return;

    container.innerHTML = "";

    data.produtos.forEach(p => {

        container.innerHTML += `
        <div class="produto">

            <h5>${p.nome}</h5>
            <p>${p.descricao}</p>

            <div class="preco">R$ ${p.preco}</div>
            <div>Estoque: ${p.estoque}</div>

        </div>
        `;
    });

});

}


    function atualizarEventosBotoes(){

document.querySelectorAll(".btn-excluir").forEach(btn => {
    btn.onclick = function(){

        const card = this.closest(".produto");
        const id = card.dataset.id;

        if(confirm("Deseja excluir este produto?")){

            fetch("api/produtos/deletar.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: JSON.stringify({id})
            })
            .then(r => r.json())
            .then(() => carregarProdutos());

        }
    }
});

document.querySelectorAll(".btn-editar").forEach(btn => {
    btn.onclick = function(){
        alert("Aqui vai abrir modal de edição depois 👍");
    }
});

document.querySelectorAll(".btn-visualizar").forEach(btn => {
    btn.onclick = function(){
        const card = this.closest(".produto");

        alert(
            card.querySelector("h5").innerText + "\n" +
            card.querySelector("p").innerText
        );
    }
});

}


    // Editar

    document.querySelectorAll(".btn-editar").forEach(botao=>{

        botao.addEventListener("click",function(){

            alert("Tela de edição será implementada futuramente.");

        });

    });

    // Excluir

    document.querySelectorAll(".btn-excluir").forEach(botao=>{

        botao.addEventListener("click",function(){

            if(confirm("Deseja excluir este produto?")){

                this.closest(".produto").remove();

                atualizarContador();

            }

        });

    });



// ==========================================
// CONTADOR
// ==========================================

function atualizarContador(){

    const produtos=document.querySelectorAll(".produto");

    let total=0;

    produtos.forEach(produto=>{

        if(produto.style.display!="none"){

            total++;

        }

    });

    const contador=document.getElementById("contadorProdutos");

    if(contador){

        contador.innerText=total;

    }

}

// ==========================================
// BOTÃO NOVO PRODUTO
// ==========================================

const novo=document.getElementById("novoProduto");

if(novo){

    novo.addEventListener("click",()=>{

        alert("Abrirá o formulário para cadastrar um novo produto.");

    });

}

// ==========================================
// DATA E HORA
// ==========================================

function atualizarRelogio(){

    const agora=new Date();

    const relogio=document.getElementById("relogio");

    if(relogio){

        relogio.innerHTML=

        agora.toLocaleDateString("pt-BR")+

        " | "+

        agora.toLocaleTimeString("pt-BR");

    }

}

setInterval(atualizarRelogio,1000);

atualizarRelogio();