// ==========================================
// CONFIGURACOES.JS - PedidoFácil
// ==========================================

document.addEventListener("DOMContentLoaded", () => {

    mostrarDataHora();
    configurarUploadLogo();
    configurarModoEscuro();
    validarSenha();

});

// ==========================================
// DATA E HORA
// ==========================================

function mostrarDataHora(){

    const relogio = document.getElementById("dataHora");

    if(!relogio) return;

    function atualizar(){

        const agora = new Date();

        relogio.innerHTML =
            agora.toLocaleDateString("pt-BR") +
            " | " +
            agora.toLocaleTimeString("pt-BR");

    }

    atualizar();

    setInterval(atualizar,1000);

}

// ==========================================
// SALVAR CONFIGURAÇÕES
// ==========================================

function configurarSalvar(){}

// ==========================================
// ALTERAR LOGO
// ==========================================

function configurarUploadLogo(){

    const botao = document.getElementById("btnAlterarLogo");
    const input = document.getElementById("logoInput");
    const imagem = document.querySelector(".logo-preview img");

    if(!botao || !input || !imagem) return;

    botao.addEventListener("click",function(){

        input.click();

    });

    input.addEventListener("change",function(){

        const arquivo = this.files[0];

        if(!arquivo) return;

        const leitor = new FileReader();

        leitor.onload = function(e){

            imagem.src = e.target.result;

        }

        leitor.readAsDataURL(arquivo);

    });

}

// ==========================================
// MODO ESCURO (SIMULAÇÃO)
// ==========================================

function configurarModoEscuro(){

    const switches = document.querySelectorAll(".form-check-input");

    switches.forEach(function(item){

        item.addEventListener("change",function(){

            const texto = this.parentElement.innerText;

            if(texto.includes("Modo escuro")){

                if(this.checked){

                    document.body.style.background="#2c2c2c";

                }else{

                    document.body.style.background="#f5f6fa";

                }

            }

        });

    });

}

// ==========================================
// VALIDAÇÃO DE SENHA
// ==========================================

function validarSenha(){

    const campos = document.querySelectorAll("input[type='password']");

    if(campos.length < 3) return;

    const nova = campos[1];

    const confirmar = campos[2];

    confirmar.addEventListener("keyup",function(){

        if(nova.value === "") return;

        if(nova.value === confirmar.value){

            confirmar.style.borderColor="green";

        }else{

            confirmar.style.borderColor="red";

        }

    });

}

// ==========================================
// CONTADOR DE NOTIFICAÇÕES
// ==========================================

const notificacoes = document.querySelectorAll(".form-check-input");

notificacoes.forEach(item=>{

    item.addEventListener("change",()=>{

        console.log("Preferência alterada.");

    });

});
