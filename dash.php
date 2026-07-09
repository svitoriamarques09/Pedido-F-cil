<?php
include 'config/conexao.php';
$action = $_GET['action'] ?? 'list';
?>

<?php if ($action === 'list'): ?>

<?php
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY id_produto DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php foreach ($produtos as $p): ?>
    <div class="produto-card">
        <h3><?= $p['nome'] ?></h3>
        <p><?= $p['descricao'] ?></p>
        <p class="preco">R$ <?= number_format($p['preco'],2,',','.') ?></p>
        <p class="estoque">Estoque: <?= $p['estoque'] ?></p>

        <?php if ($p['imagem']): ?>
            <img src="uploads/<?= $p['imagem'] ?>" class="produto-img">
        <?php endif; ?>

        <a href="?action=edit&id=<?= $p['id_produto'] ?>" class="btn-editar">Editar</a>
        <a href="?action=delete&id=<?= $p['id_produto'] ?>" class="btn-excluir" onclick="return confirm('Excluir produto?')">Excluir</a>
    </div>
<?php endforeach; ?>

<?php endif; ?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos | PedidoFácil</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="assets/css/produtos.css">

</head>

<body>

    <p id="relogio" class="text-secondary"></p>

<div class="container-fluid">

<div class="row">

    <!-- SIDEBAR -->

    <aside class="col-lg-2 sidebar">

        <div class="logo">

            <i class="bi bi-chat-dots-fill"></i>

            <h3>PedidoFácil</h3>

        </div>

        <ul>

            <li>

                <a href="dashboard.html">

                    <i class="bi bi-grid-fill"></i>

                    Dashboard

                </a>

            </li>

            <li>

                <a href="pedidos.html">

                    <i class="bi bi-cart-fill"></i>

                    Pedidos

                </a>

            </li>

            <li>

                <a href="producao.php">

                    <i class="bi bi-fire"></i>

                    Produção

                </a>

            </li>

            <li class="active">

                <a href="produtos.php">Produtos</a>

                    <i class="bi bi-box-seam-fill"></i>

                    Produtos

                </a>

            </li>


        

            <li>

                <a href="relatorios.html">

                    <i class="bi bi-bar-chart-fill"></i>

                    Relatórios

                </a>

            </li>

            <li>

                <a href="configuracoes.html">

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

                <h2>Produtos</h2>

                <p class="text-secondary">

                    Gerencie todos os produtos da empresa.

                </p>

            </div>

            <?php if ($action === 'new'): ?>

            <form method="post" enctype="multipart/form-data" class="form-produto">
                <input type="text" name="nome" placeholder="Nome">
                <textarea name="descricao" placeholder="Descrição"></textarea>
                <input type="number" step="0.01" name="preco" placeholder="Preço">
                <input type="number" name="estoque" placeholder="Estoque">
                <input type="number" name="id_categoria" placeholder="Categoria">
                <input type="number" step="0.01" name="valor" placeholder="Valor">
                <input type="file" name="imagem">

                <button type="submit">Salvar</button>
            </form>

<?php endif; ?>


        </div>

                <!-- ==========================
             CARDS DE RESUMO
        =========================== -->

        <div class="row g-4 mb-4">

            <div class="col-lg-3 col-md-6">

                <div class="card-dashboard">

                    <div class="card-info">

                        <span>Total de Produtos</span>

                        <h3>52</h3>

                    </div>

                    <div class="card-icon bg-primary">

                        <i class="bi bi-box-seam-fill"></i>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="card-dashboard">

                    <div class="card-info">

                        <span>Categorias</span>

                        <h3>8</h3>

                    </div>

                    <div class="card-icon bg-success">

                        <i class="bi bi-tags-fill"></i>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="card-dashboard">

                    <div class="card-info">

                        <span>Disponíveis</span>

                        <h3>49</h3>

                    </div>

                    <div class="card-icon bg-info">

                        <i class="bi bi-check-circle-fill"></i>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-md-6">

                <div class="card-dashboard">

                    <div class="card-info">

                        <span>Indisponíveis</span>

                        <h3>3</h3>

                    </div>

                    <div class="card-icon bg-danger">

                        <i class="bi bi-x-circle-fill"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- ==========================
             FILTROS
        =========================== -->

        <div class="dashboard-box mb-4">

            <div class="row g-3 align-items-end">

                <div class="col-lg-4">

                    <label class="form-label">

                        Pesquisar Produto

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="text"
                            id="pesquisaProduto"
                            class="form-control"
                            placeholder="Pesquisar produto">

                    </div>

                </div>

                <div class="col-lg-3">

                    <label class="form-label">

                        Categoria

                    </label>

                    <select id="categoriaProduto" class="form-select">

                    <option value="todas">Todas</option>
                        <option value="hambúrguer">Hambúrguer</option>
                        <option value="pizza">Pizza</option>  
                        <option value="bebidas">Bebidas</option> 

                    </select>

                </div>

                <div class="col-lg-3">

                    <label class="form-label">

                        Status

                    </label>

                    <select id="statusProduto" class="form-select">

                        <option>Todos</option>
                        <option>Disponível</option>
                        <option>Indisponível</option>

                    </select>

                </div>

                <div class="col-lg-2">

                    <button class="btn btn-danger w-100">

                        <i class="bi bi-funnel-fill"></i>

                        Filtrar

                    </button>

                </div>

            </div>

        </div>

        <!-- ==========================
     LISTA DE PRODUTOS
========================== -->

<div class="row g-4">

    <!-- Produto 1 -->

    <div class="col-lg-4 col-md-6" data-categoria="hambúrguer" data-status="disponível">

        <div class="product-card">

            <img src="img/xburger.png" class="product-img" alt="X-Burger">

            <div class="product-body">

                <div class="d-flex justify-content-between align-items-center">

                    <h5>X-Burger</h5>

                    <span class="badge bg-success">
                        Disponível
                    </span>

                </div>

                <p class="text-secondary">

                    Hambúrguer artesanal com queijo, alface e tomate.

                </p>

                <div class="product-info">

                    <span>

                        <i class="bi bi-tags-fill"></i>

                        Hambúrguer

                    </span>

                    <span class="preço">

                        <i class="bi bi-currency-dollar"></i>

                        R$ 28,90

                    </span>

                </div>

                <div class="product-buttons">

                    <button class="btn btn-outline-primary btn-visualizar">

                        <i class="bi bi-eye-fill"></i>

                    </button>

                    <?php if ($action === 'edit'): ?>

                    <?php
                    $id = $_GET['id'];
                    $p = $pdo->query("SELECT * FROM produtos WHERE id_produto = $id")->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <form method="post" enctype="multipart/form-data" class="form-produto">
                        <input type="text" name="nome" value="<?= $p['nome'] ?>">
                        <textarea name="descricao"><?= $p['descricao'] ?></textarea>
                        <input type="number" step="0.01" name="preco" value="<?= $p['preco'] ?>">
                        <input type="number" name="estoque" value="<?= $p['estoque'] ?>">
                        <input type="number" name="id_categoria" value="<?= $p['id_categoria'] ?>">
                        <input type="number" step="0.01" name="valor" value="<?= $p['valor'] ?>">

                        <?php if ($p['imagem']): ?>
                            <img src="uploads/<?= $p['imagem'] ?>" class="produto-img">
                        <?php endif; ?>

                        <input type="file" name="imagem">

                        <button type="submit">Salvar</button>
                    </form>

                    <?php endif; ?>


                    <button class="btn btn-outline-danger btn-excluir">

                        <i class="bi bi-trash-fill"></i>

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- Produto 2 -->

    <div class="col-lg-4 col-md-6" data-categoria="pizza" data-status="disponível">

        <div class="product-card">

            <img src= "img/pizza.png" class="product-img" alt="Pizza">

            <div class="product-body">

                <div class="d-flex justify-content-between align-items-center">

                    <h5>Pizza Calabresa</h5>

                    <span class="badge bg-success">
                        Disponível
                    </span>

                </div>

                <p class="text-secondary">

                    Pizza grande de calabresa com queijo.

                </p>

                <div class="product-info">

                    <span>

                        <i class="bi bi-tags-fill"></i>

                        Pizza

                    </span>

                    <span class="preço">

                        <i class="bi bi-currency-dollar"></i>

                        R$ 54,90

                    </span>

                </div>

                <div class="product-buttons">

                    <button class="btn btn-outline-primary btn-visualizar">

                        <i class="bi bi-eye-fill"></i>

                    </button>

                    <button class="btn btn-outline-warning btn-editar">

                        <i class="bi bi-pencil-fill"></i>

                    </button>

                    <button class="btn btn-outline-danger btn-excluir">

                        <i class="bi bi-trash-fill"></i>

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- Produto 3 -->

    <div class="col-lg-4 col-md-6" data-categoria="bebidas" data-status="indisponível">

        <div class="product-card">

            <img src="img/cocacola.png" class="product-img" alt="Refrigerante">

            <div class="product-body">

                <div class="d-flex justify-content-between align-items-center">

                    <h5>Coca-Cola 2L</h5>

                    <span class="badge bg-danger">
                        Indisponível
                    </span>

                </div>

                <p class="text-secondary">

                    Refrigerante Coca-Cola 2 Litros.

                </p>

                <div class="product-info">

                    <span>

                        <i class="bi bi-tags-fill"></i>

                        Bebidas

                    </span>

                    <span class="preço">

                        <i class="bi bi-currency-dollar"></i>

                        R$ 14,00

                    </span>

                </div>

                <div class="product-buttons">

                    <button class="btn btn-outline-primary btn-visualizar">

                        <i class="bi bi-eye-fill"></i>

                    </button>

                    <button class="btn btn-outline-warning btn-editar">

                        <i class="bi bi-pencil-fill"></i>

                    </button>

                    <button class="btn btn-outline-danger btn-excluir">

                        <i class="bi bi-trash-fill"></i>

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Paginação -->

<nav class="mt-5">

    <ul class="pagination justify-content-center">

        <li class="page-item disabled">

            <a class="page-link">Anterior</a>

        </li>

        <li class="page-item active">

            <a class="page-link">1</a>

        </li>

        <li class="page-item">

            <a class="page-link">2</a>

        </li>

        <li class="page-item">

            <a class="page-link">3</a>

        </li>

        <li class="page-item">

            <a class="page-link">Próximo</a>

        </li>

    </ul>

</nav>

</main>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/produtos.js"></script>

</body>

</html>