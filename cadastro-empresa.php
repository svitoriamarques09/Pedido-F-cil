<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro da Empresa | PedidoFácil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="assets/css/cadastro-empresa.css">

</head>

<body>

<div class="container-fluid vh-100">

<div class="row h-100">

<!-- Banner -->

<div class="col-lg-5 d-none d-lg-block p-0">

<div class="login-banner">

<img src="img/login-banner.png"
alt="Banner">

</div>

</div>

<!-- Formulário -->

<div class="col-lg-7 d-flex align-items-center justify-content-center py-5">
    
<div class="login-card cadastro-card">

<div class="text-center mb-4">

<div class="logo-circle">

<i class="bi bi-shop"></i>

</div>

</div>

<h2 class="fw-bold text-center">

Cadastre sua empresa

</h2>

<p class="text-center text-secondary mb-4">

Comece a automatizar seus pedidos.

</p>

<form id="formCadastro">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Nome da Empresa

</label>

<input
    id="empresa"
    type="text"
    class="form-control"
    placeholder="Ex: Burguer House">

</div>

<div class="col-md-6 mb-3">

    <label class="form-label">Categoria</label>

    <select id="categoria" class="form-select">

        <option value="">Selecione</option>
        <option value="Hamburgueria">Hamburgueria</option>
        <option value="Pizzaria">Pizzaria</option>
        <option value="Restaurante">Restaurante</option>
        <option value="Confeitaria">Confeitaria</option>
        <option value="Açaíteria">Açaíteria</option>
        <option value="Outro">Outro</option>

    </select>

</div>
<div class="col-md-6 mb-3">

<label class="form-label">

Responsável

</label>

<input
    id="responsavel"
    type="text"
    class="form-control"
    placeholder="Seu nome">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Telefone

</label>

<input
    id="telefone"
    type="text"
    class="form-control"
    placeholder="(99) 99999-9999">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

CNPJ

</label>

<input
    id="cnpj"
    type="text"
    class="form-control"
    placeholder="00.000.000/0000-00">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

E-mail

</label>

<input
    id="email"
    type="email"
    class="form-control"
    placeholder="empresa@email.com">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Senha

</label>

<div class="input-group">

<input
    id="senha"
    type="password"
    class="form-control"
    placeholder="********">

<button
    id="mostrarSenha"
    class="btn btn-outline-secondary"
    type="button">

    <i class="bi bi-eye"></i>

</button>

</div>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Confirmar Senha

</label>

<div class="input-group">

<input
    id="confirmarSenha"
    type="password"
    class="form-control"
    placeholder="********">

<button
    id="mostrarConfirmarSenha"
    class="btn btn-outline-secondary"
    type="button">

    <i class="bi bi-eye"></i>

</button>
</div>

</div>

</div>

<div class="form-check my-3">

<input
class="form-check-input"
type="checkbox"
id="termos">

<label
class="form-check-label"
for="termos">

Li e aceito os Termos de Uso e Política de Privacidade.

</label>

</div>

<button
    type="submit"
    class="btn btn-login w-100">

Criar Conta

</button>

<div class="text-center mt-4">

Já possui uma conta?

<a
href="login.php"
class="text-danger fw-bold text-decoration-none">

Entrar

</a>

</div>

</form>



</div> <!-- login-card -->

</div> <!-- col-lg-7 -->

</div> <!-- row -->

</div> <!-- container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="assets/js/cadastro-empresa.js"></script>
</body>

</html>