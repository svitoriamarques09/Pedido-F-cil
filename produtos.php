<?php
// 1. Conexão com o Banco de Dados
$pdo = new PDO("mysql:host=localhost;dbname=salgados_tcc", "root", "");

// 2. AÇÃO DE DELETAR
if (isset($_GET['acao']) && $_GET['acao'] == 'deletar' && isset($_GET['id'])) {
    $id_deletar = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id_produto = :id");
    $stmt->execute(['id' => $id_deletar]);
    header("Location: produtos.php");
    exit();
}

// 3. AÇÃO DE SALVAR ALTERAÇÕES (Com upload de foto corrigido)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_form']) && $_POST['acao_form'] === 'editar') {
    $id_produto = $_POST['id_produto'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $id_categoria = $_POST['id_categoria'];
    $preco = str_replace(',', '.', $_POST['preco']);
    $estoque = intval($_POST['estoque']);
    
    // Busca a imagem atual do banco para não perder se não enviar uma nova
    $stmt_img = $pdo->prepare("SELECT imagem FROM produtos WHERE id_produto = :id");
    $stmt_img->execute(['id' => $id_produto]);
    $prod_atual = $stmt_img->fetch(PDO::FETCH_ASSOC);
    $nome_imagem = $prod_atual['imagem'] ?? 'img/xburger.png';

    // Processa a nova imagem se ela foi enviada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $novo_nome = "produto_" . time() . "_" . uniqid() . "." . $extensao;
        $pasta_destino = "img/" . $novo_nome;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $pasta_destino)) {
            $nome_imagem = $pasta_destino;
        }
    }

    $sql_update = "UPDATE produtos SET nome = :nome, descricao = :descricao, id_categoria = :id_categoria, preco = :preco, estoque = :estoque, imagem = :imagem WHERE id_produto = :id_produto";
    $stmt_up = $pdo->prepare($sql_update);
    $stmt_up->execute([
        'nome' => $nome,
        'descricao' => $descricao,
        'id_categoria' => $id_categoria,
        'preco' => $preco,
        'estoque' => $estoque,
        'imagem' => $nome_imagem,
        'id_produto' => $id_produto
    ]);

    header("Location: produtos.php");
    exit();
}

// 4. BUSCAR CATEGORIAS
$stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias_banco = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

$nomes_categorias = [];
foreach ($categorias_banco as $c) {
    $nomes_categorias[$c['id_categoria']] = $c['nome'];
}

// 5. FILTROS
$pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
$categoria_selecionada = isset($_GET['categoria']) ? $_GET['categoria'] : 'todas';

$sql = "SELECT * FROM produtos WHERE id_empresa = 1";
$params = [];

if ($pesquisa !== '') {
    $sql .= " AND nome LIKE :pesquisa";
    $params['pesquisa'] = "%$pesquisa%";
}

if ($categoria_selecionada !== 'todas') {
    $sql .= " AND id_categoria = :id_categoria";
    $params['id_categoria'] = $categoria_selecionada;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtLogo = $pdo->prepare("SELECT logo FROM empresas WHERE id_empresa = ?");
$stmtLogo->execute([1]);
$logo_empresa = $stmtLogo->fetchColumn() ?: '';
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos | PedidoFácil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/produtos.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <?php $pagina_atual = basename($_SERVER['PHP_SELF']); ?>
<aside class="col-lg-2 sidebar">
    <div class="logo">
        <?php if (!empty($logo_empresa) && file_exists(__DIR__ . '/' . $logo_empresa)): ?>
            <img src="<?= htmlspecialchars($logo_empresa) ?>" alt="Logo da empresa" class="sidebar-logo-img">
        <?php else: ?>
            <i class="bi bi-chat-dots-fill"></i>
        <?php endif; ?>
        <h3>PedidoFácil</h3>
    </div>
    <ul>
        <li class="<?= $pagina_atual == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="bi bi-grid-fill"></i>
                Dashboard
            </a>
        </li>

        <li class="<?= $pagina_atual == 'pedidos.php' ? 'active' : '' ?>">
            <a href="pedidos.php">
                <i class="bi bi-cart-fill"></i>
                Pedidos
            </a>
        </li>

        <li class="<?= $pagina_atual == 'producao.php' ? 'active' : '' ?>">
            <a href="producao.php">
                <i class="bi bi-fire"></i>
                Produção
            </a>
        </li>

        <li class="<?= $pagina_atual == 'produtos.php' ? 'active' : '' ?>">
            <a href="produtos.php">
                <i class="bi bi-box-seam-fill"></i>
                Produtos
            </a>
        </li>

        <li class="<?= $pagina_atual == 'relatorios.php' ? 'active' : '' ?>">
            <a href="relatorios.php">
                <i class="bi bi-bar-chart-fill"></i>
                Relatórios
            </a>
        </li>

        <li class="<?= $pagina_atual == 'configuracoes.php' ? 'active' : '' ?>">
            <a href="configuracoes.php">
                <i class="bi bi-gear-fill"></i>
                Configurações
            </a>
        </li>
    </ul>
</aside>

        <main class="col-lg-10 main-content py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Produtos</h2>
                    <p class="text-secondary">Gerencie todos os produtos da empresa.</p>
                </div>

                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
                    <i class="bi bi-plus-circle-fill"></i> Novo Produto
                </button>
            
            </div>

            <div class="dashboard-box mb-4" style="background: white; padding: 20px; border-radius: 15px;">
                <form method="GET" action="produtos.php" class="row g-3 align-items-end">
                    <div class="col-lg-6">
                        <label class="form-label">Pesquisar Produto</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="pesquisa" class="form-control" placeholder="Digite o nome do produto..." value="<?= htmlspecialchars($pesquisa) ?>">
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label">Categoria</label>
                        <select name="categoria" class="form-select">
                            <option value="todas" <?= $categoria_selecionada == 'todas' ? 'selected' : '' ?>>Todas as Categorias</option>
                            <?php foreach ($categorias_banco as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= $categoria_selecionada == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-funnel-fill"></i> Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="row g-4">
                <?php if (empty($produtos)): ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-secondary fs-5">Nenhum produto encontrado.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($produtos as $prod): ?>
                    <?php 
                        $imagem_exibir = (!empty($prod['imagem']) && file_exists($prod['imagem'])) ? $prod['imagem'] : 'img/xburger.png';
                        $cat_nome = $nomes_categorias[$prod['id_categoria']] ?? 'Sem Categoria';
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="product-card" style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                            <img src="<?= $imagem_exibir ?>" class="product-img" style="width: 100%; height: 200px; object-fit: cover;" alt="Imagem">
                            <div class="product-body" style="padding: 20px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0"><strong><?= htmlspecialchars($prod['nome']) ?></strong></h5>
                                    <span class="badge <?= ($prod['estoque'] > 0) ? 'bg-success' : 'bg-danger' ?>">
                                        <?= ($prod['estoque'] > 0) ? 'Disponível' : 'Indisponível' ?>
                                    </span>
                                </div>
                                <p class="text-secondary small"><?= htmlspecialchars($prod['descricao']) ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-secondary">Estoque: <strong><?= $prod['estoque'] ?></strong></span>
                                    <span class="text-dark"><strong>R$ <?= number_format($prod['preco'], 2, ',', '.') ?></strong></span>
                                </div>
                                
                                <div class="d-flex justify-content-center gap-2 pt-2" style="border-top: 1px solid #eee;">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modalOlho"
                                            data-nome="<?= htmlspecialchars($prod['nome']) ?>"
                                            data-desc="<?= htmlspecialchars($prod['descricao']) ?>"
                                            data-preco="R$ <?= number_format($prod['preco'], 2, ',', '.') ?>"
                                            data-estoque="<?= $prod['estoque'] ?>"
                                            data-cat="<?= htmlspecialchars($cat_nome) ?>"
                                            data-img="<?= $imagem_exibir ?>">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-warning" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditar" 
                                            data-id="<?= $prod['id_produto'] ?>"
                                            data-nome="<?= htmlspecialchars($prod['nome']) ?>"
                                            data-desc="<?= htmlspecialchars($prod['descricao']) ?>"
                                            data-preco="<?= number_format($prod['preco'], 2, ',', '') ?>"
                                            data-estoque="<?= $prod['estoque'] ?>"
                                            data-cat="<?= $prod['id_categoria'] ?>">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>

                                    <a href="produtos.php?acao=deletar&id=<?= $prod['id_produto'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="modalOlho" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
            <img id="olho-img" src="" style="width: 100%; height: 250px; object-fit: cover;">
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="mb-0"><strong id="olho-nome"></strong></h4>
                    <span id="olho-preco" class="badge bg-danger fs-6" style="padding: 8px 15px; border-radius: 8px;"></span>
                </div>
                <span id="olho-cat" class="badge bg-secondary mb-3"></span>
                <p id="olho-desc" class="text-secondary mb-4"></p>
                <div class="p-3 bg-light rounded" style="border-left: 4px solid #dc3545;">
                    Estoque no Sistema: <strong id="olho-estoque"></strong> unidades.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header">
                <h5 class="modal-title"><strong>Editar Produto</strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="produtos.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="acao_form" value="editar">
                    <input type="hidden" name="id_produto" id="edit-id">

                    <div class="mb-3">
                        <label class="form-label">Nome do Produto</label>
                        <input type="text" name="nome" id="edit-nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" id="edit-desc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Preço (R$)</label>
                            <input type="text" name="preco" id="edit-preco" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estoque</label>
                            <input type="number" name="estoque" id="edit-estoque" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="id_categoria" id="edit-cat" class="form-select" required>
                            <?php foreach ($categorias_banco as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mudar Foto do Salgado</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <div class="form-text">Selecione uma foto para substituir o hambúrguer.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Configura o Modal de Edição
    const modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-nome').value = button.getAttribute('data-nome');
            document.getElementById('edit-desc').value = button.getAttribute('data-desc');
            document.getElementById('edit-preco').value = button.getAttribute('data-preco');
            document.getElementById('edit-estoque').value = button.getAttribute('data-estoque');
            document.getElementById('edit-cat').value = button.getAttribute('data-cat');
        });
    }

    // Configura o Modal de Visualização (Olho)
    const modalOlho = document.getElementById('modalOlho');
    if (modalOlho) {
        modalOlho.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            document.getElementById('olho-img').src = button.getAttribute('data-img');
            document.getElementById('olho-nome').innerText = button.getAttribute('data-nome');
            document.getElementById('olho-desc').innerText = button.getAttribute('data-desc');
            document.getElementById('olho-preco').innerText = button.getAttribute('data-preco');
            document.getElementById('olho-estoque').innerText = button.getAttribute('data-estoque');
            document.getElementById('olho-cat').innerText = button.getAttribute('data-cat');
        });
    }
</script>

<div class="modal fade" id="modalNovoProduto" tabindex="-1" aria-labelledby="modalNovoProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNovoProdutoLabel"><i class="bi bi-box-seam-fill text-danger"></i> Cadastrar Novo Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="api/salvar_produto.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    
                    <input type="hidden" name="id_empresa" value="1">

                    <div class="mb-3">
                        <label class="form-label">Nome do Produto</label>
                        <input type="text" name="nome" class="form-control" placeholder="Ex: Coxinha de Frango, Pizza, Coca-Cola" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select" required>
                                <option value="Salgados">Salgados</option>
                                <option value="Pizzas">Pizzas</option>
                                <option value="Bebidas">Bebidas</option>
                                <option value="Doces">Doces</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Preço de Venda (R$)</label>
                            <input type="number" name="preco" step="0.01" class="form-control" placeholder="0,00" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Estoque Inicial</label>
                            <input type="number" name="estoque" class="form-control" value="50" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Disponível">Disponível</option>
                                <option value="Indisponível">Indisponível</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição / Ingredientes</label>
                        <textarea name="descricao" class="form-control" rows="2" placeholder="Ex: Coxinha artesanal super recheada..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto do Produto</label>
                        <input type="file" name="foto" class="form-control" accept="image/*">
                        <div class="form-text">Formatos aceitos: JPG, PNG.</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>
</div>


</body>
</html>
