// ==========================================
// PedidoFácil - Cadastro de Empresa
// ==========================================

document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");

    const empresa = document.getElementById("empresa");
    const categoria = document.getElementById("categoria");
    const responsavel = document.getElementById("responsavel");
    const telefone = document.getElementById("telefone");
    const cnpj = document.getElementById("cnpj");
    const email = document.getElementById("email");
    const senha = document.getElementById("senha");
    const confirmarSenha = document.getElementById("confirmarSenha");
    const termos = document.getElementById("termos");

    const mostrarSenha = document.getElementById("mostrarSenha");
    const mostrarConfirmarSenha = document.getElementById("mostrarConfirmarSenha");

    //===========================
    // Mostrar senha
    //===========================

    mostrarSenha.addEventListener("click", () => {

        senha.type = senha.type === "password" ? "text" : "password";

        mostrarSenha.innerHTML =
            senha.type === "password"
                ? '<i class="bi bi-eye"></i>'
                : '<i class="bi bi-eye-slash"></i>';

    });

    mostrarConfirmarSenha.addEventListener("click", () => {

        confirmarSenha.type =
            confirmarSenha.type === "password"
                ? "text"
                : "password";

        mostrarConfirmarSenha.innerHTML =
            confirmarSenha.type === "password"
                ? '<i class="bi bi-eye"></i>'
                : '<i class="bi bi-eye-slash"></i>';

    });

    //===========================
    // Máscara Telefone
    //===========================

    telefone.addEventListener("input", () => {

        let valor = telefone.value.replace(/\D/g, "");

        valor = valor.replace(/^(\d{2})(\d)/g, "($1) $2");
        valor = valor.replace(/(\d{5})(\d)/, "$1-$2");

        telefone.value = valor;

    });

    //===========================
    // Máscara CNPJ
    //===========================

    cnpj.addEventListener("input", () => {

        let valor = cnpj.value.replace(/\D/g, "");

        valor = valor.replace(/^(\d{2})(\d)/, "$1.$2");
        valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        valor = valor.replace(/\.(\d{3})(\d)/, ".$1/$2");
        valor = valor.replace(/(\d{4})(\d)/, "$1-$2");

        cnpj.value = valor;

    });

    //===========================
    // Validação Email
    //===========================

    function validarEmail(emailDigitado){

        const regex =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        return regex.test(emailDigitado);

    }

    //===========================
    // Força da senha
    //===========================

    function senhaForte(senhaDigitada){

        return senhaDigitada.length >= 6;

    }

    //===========================
    // Cadastro
    //===========================

    form.addEventListener("submit", function(e){

        e.preventDefault();

        if(empresa.value.trim()==""){

            Swal.fire("Atenção","Informe o nome da empresa.","warning");
            empresa.focus();
            return;

        }

        if(categoria.value==""){

            Swal.fire("Atenção","Escolha uma categoria.","warning");
            categoria.focus();
            return;

        }

        if(responsavel.value.trim()==""){

            Swal.fire("Atenção","Informe o responsável.","warning");
            responsavel.focus();
            return;

        }

        if(telefone.value.trim()==""){

            Swal.fire("Atenção","Informe o telefone.","warning");
            telefone.focus();
            return;

        }

        if(cnpj.value.trim()==""){

            Swal.fire("Atenção","Informe o CNPJ.","warning");
            cnpj.focus();
            return;

        }

        if(!validarEmail(email.value)){

            Swal.fire("Erro","E-mail inválido.","error");
            email.focus();
            return;

        }

        if(!senhaForte(senha.value)){

            Swal.fire(
                "Senha Fraca",
                "A senha deve possuir pelo menos 6 caracteres.",
                "warning"
            );

            senha.focus();
            return;

        }

        if(senha.value!==confirmarSenha.value){

            Swal.fire(
                "Erro",
                "As senhas não conferem.",
                "error"
            );

            confirmarSenha.focus();
            return;

        }

        if(!termos.checked){

            Swal.fire(
                "Atenção",
                "Você deve aceitar os Termos de Uso.",
                "warning"
            );

            return;

        }

        //===========================
        // Loading
        //===========================

        const botao =
        document.querySelector(".btn-login");

        botao.disabled=true;

        botao.innerHTML=`
        <span class="spinner-border spinner-border-sm"></span>
        Cadastrando...
        `;

        //===========================
        // Envia para PHP
        //===========================

        fetch("api/cadastro-empresa.php", {

    method: "POST",

    headers: {
        "Content-Type": "application/json"
    },

    body: JSON.stringify({

        empresa: empresa.value,
        tipo: categoria.value,
        responsavel: responsavel.value,
        telefone: telefone.value,
        cnpj: cnpj.value,
        email: email.value,
        senha: senha.value

    })

})
.then(response => response.json())
.then(retorno => {

    botao.disabled = false;

    botao.innerHTML = `
        <i class="bi bi-person-plus-fill"></i>
        Criar Conta
    `;

    if(retorno.status){

        Swal.fire({

            icon:"success",
            title:"Cadastro realizado!",
            text:retorno.mensagem,
            confirmButtonColor:"#dc3545"

        }).then(()=>{

            window.location = "login.php";

        });

    }else{

        Swal.fire({

            icon:"error",
            title:"Erro",
            text:retorno.mensagem

        });

    }

})
.catch((erro)=>{

    botao.disabled = false;

    botao.innerHTML = `
        <i class="bi bi-person-plus-fill"></i>
        Criar Conta
    `;

    console.error(erro);

    Swal.fire({

        icon:"error",
        title:"Erro",
        text:"Não foi possível conectar ao servidor."

    });

});

    });

});