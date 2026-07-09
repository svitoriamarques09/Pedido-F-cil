document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("form");

    const email = document.querySelector("input[type='email']");

    const senha = document.getElementById("senha");

    const mostrarSenha = document.getElementById("mostrarSenha");

    const lembrar = document.getElementById("lembrar");

    const botaoLogin = document.querySelector(".btn-login");

    // ==========================
    // Mostrar senha
    // ==========================

    mostrarSenha.addEventListener("click", () => {

        if (senha.type === "password") {

            senha.type = "text";

            mostrarSenha.innerHTML =
                '<i class="bi bi-eye-slash"></i>';

        } else {

            senha.type = "password";

            mostrarSenha.innerHTML =
                '<i class="bi bi-eye"></i>';

        }

    });

    // ==========================
    // Recuperar email salvo
    // ==========================

    const emailSalvo = localStorage.getItem("emailPedidoFacil");

    if (emailSalvo) {

        email.value = emailSalvo;

        lembrar.checked = true;

    }

    // ==========================
    // Validação dos campos
    // ==========================

    function validarEmail(emailDigitado) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailDigitado);

    }

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        const usuario = email.value.trim();

        const senhaDigitada = senha.value.trim();

        if (usuario === "") {

            Swal.fire({

                icon: "warning",
                title: "Campo obrigatório",
                text: "Informe seu e-mail."

            });

            email.focus();

            return;

        }

        if (!validarEmail(usuario)) {

            Swal.fire({

                icon: "error",
                title: "E-mail inválido",
                text: "Digite um endereço de e-mail válido."

            });

            email.focus();

            return;

        }

        if (senhaDigitada.length < 6) {

            Swal.fire({

                icon: "warning",
                title: "Senha inválida",
                text: "A senha deve possuir pelo menos 6 caracteres."

            });

            senha.focus();

            return;

        }

        if (lembrar.checked) {

            localStorage.setItem("emailPedidoFacil", usuario);

        } else {

            localStorage.removeItem("emailPedidoFacil");

        }

        // ==========================
        // Loading
        // ==========================

        botaoLogin.disabled = true;

        botaoLogin.innerHTML = `

            <span class="spinner-border spinner-border-sm"></span>

            Entrando...

        `;

fetch("api/login.php", {

    method: "POST",

    headers: {
        "Content-Type": "application/json"
    },

    body: JSON.stringify({

        email: usuario,
        senha: senhaDigitada

    })

})
.then(async (res) => {

    const texto = await res.text();

    console.log(texto);

    alert(texto);

    return JSON.parse(texto);

})
.then(resposta => {
    botaoLogin.disabled = false;

    botaoLogin.innerHTML = `

        <i class="bi bi-box-arrow-in-right"></i>

        Entrar

    `;

    if(resposta.status){

        Swal.fire({

            icon:"success",

            title:"Bem-vindo!",

            text:resposta.mensagem,

            timer:1800,

            showConfirmButton:false

        });

        setTimeout(()=>{

            window.location.href = "dashboard.php";

        },1800);

    }else{

        Swal.fire({

            icon:"error",

            title:"Erro",

            text:resposta.mensagem

        });

    }

})
.catch(()=>{

    botaoLogin.disabled = false;

    botaoLogin.innerHTML = `

        <i class="bi bi-box-arrow-in-right"></i>

        Entrar

    `;

    Swal.fire({

        icon:"error",

        title:"Servidor",

        text:"Não foi possível conectar ao servidor."

    });

});
    });

});