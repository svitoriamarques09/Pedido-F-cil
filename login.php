<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | PedidoFácil</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="container-fluid vh-100">

        <div class="row h-100">

            <!-- Lado Esquerdo -->
            <div class="col-lg-5 d-none d-lg-block p-0">

                <div class="login-banner">

                    <img src="img/login-banner.png"
                        alt="Banner PedidoFácil">

                </div>

            </div>

            <!-- Lado Direito -->
            <div class="col-lg-7 col-12 d-flex align-items-center justify-content-center">

                <div class="login-card">

                    <!-- Logo -->

                    <div class="text-center mb-4">

                        <div class="logo-circle">

                            <i class="bi bi-bag-check-fill"></i>

                        </div>

                    </div>

                    <h1 class="text-center fw-bold">

                        Bem-vindo de volta!

                    </h1>

                    <p class="text-center text-secondary mb-5">

                        Faça login para acessar sua conta

                    </p>

                    <form id="formLogin">

                        <!-- Email -->

                        <label class="form-label">

                            E-mail

                        </label>

                        <div class="input-group mb-4">

                            <span class="input-group-text">

                                <i class="bi bi-envelope"></i>

                            </span>

                            <input
                                id="email"
                                type="email"
                                class="form-control"
                                placeholder="Digite seu e-mail">
                        </div>

                        <!-- Senha -->

                        <label class="form-label">

                            Senha

                        </label>

                        <div class="input-group mb-3">

                            <span class="input-group-text">

                                <i class="bi bi-lock"></i>

                            </span>

                            <input type="password"
                                id="senha"
                                class="form-control"
                                placeholder="Digite sua senha">

                            <button
                                class="btn btn-outline-secondary"
                                type="button"
                                id="mostrarSenha">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                        <!-- Opções -->

                        <div class="d-flex justify-content-between mb-4">

                            <div class="form-check">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="lembrar">

                                <label
                                    class="form-check-label"
                                    for="lembrar">

                                    Lembrar de mim

                                </label>

                            </div>

                            <a href="#"
                                class="text-danger text-decoration-none">

                                Esqueci minha senha

                            </a>

                        </div>

                        <!-- Botão -->

                        <button
                            class="btn btn-login w-100">

                            <i class="bi bi-box-arrow-in-right"></i>

                            Entrar

                        </button>

                    </form>

                    <!-- Divisor -->

                    <div class="divider">

                        <span>ou</span>

                    </div>

                    <!-- Google -->

                    <button
                        class="btn btn-google w-100">

                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
                            width="22">

                        Entrar com Google

                    </button>

                    <!-- Cadastro -->

                   <p class="text-center mt-5">

                       Ainda não tem uma conta?

                       <a href="cadastro-empresa.php"
                          class="text-danger fw-bold text-decoration-none">

                          Cadastre-se

                        </a>

                    </p>

                    <!-- Recursos -->

                    <div class="row text-center mt-5">

                        <div class="col">

                            <i class="bi bi-bar-chart-fill text-danger fs-3"></i>

                            <h6 class="mt-2">

                                Relatórios

                            </h6>

                            <small>

                                Acompanhe tudo em tempo real

                            </small>

                        </div>

                        <div class="col">

                            <i class="bi bi-chat-dots-fill text-warning fs-3"></i>

                            <h6 class="mt-2">

                                Automação

                            </h6>

                            <small>

                                Pedidos automáticos pelo WhatsApp

                            </small>

                        </div>

                        <div class="col">

                            <i class="bi bi-shield-lock-fill text-danger fs-3"></i>

                            <h6 class="mt-2">

                                Segurança

                            </h6>

                            <small>

                                Seus dados protegidos

                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="assets/js/login.js"></script>

</body>

</html>