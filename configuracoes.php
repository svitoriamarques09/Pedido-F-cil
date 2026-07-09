<?php
session_start();

if (!isset($_SESSION["id_empresa"])) {
    header("Location: login.php");
    exit;
}

// 1. Incluir a conexão com o banco de dados e iniciar controle de feedback
require_once 'config/conexao.php';

$id_empresa = $_SESSION["id_empresa"];
$mensagem_sucesso = "";
$mensagem_erro = "";

// 2. Processar o formulário se houver envio via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $responsavel = isset($_POST['responsavel']) ? trim($_POST['responsavel']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $cnpj = isset($_POST['cnpj']) ? trim($_POST['cnpj']) : '';
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
    $logo = null;
    
    // Campos de senha
    $senha_atual = isset($_POST['senha_atual']) ? $_POST['senha_atual'] : '';
    $nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

    try {
        $pdo->beginTransaction();

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Não foi possível enviar a logo. Tente novamente.");
            }

            if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
                throw new Exception("A logo deve ter no máximo 2MB.");
            }

            $extensao = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($extensao, $extensoes_permitidas, true) || !getimagesize($_FILES['logo']['tmp_name'])) {
                throw new Exception("Envie uma imagem válida nos formatos JPG, PNG, WEBP ou GIF.");
            }

            $diretorio_logo = __DIR__ . '/img/logos';
            if (!is_dir($diretorio_logo) && !mkdir($diretorio_logo, 0775, true)) {
                throw new Exception("Não foi possível criar a pasta para salvar a logo.");
            }

            $nome_arquivo = 'empresa_' . $id_empresa . '_' . time() . '.' . $extensao;
            $destino = $diretorio_logo . '/' . $nome_arquivo;

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $destino)) {
                throw new Exception("Não foi possível salvar a logo enviada.");
            }

            $logo = 'img/logos/' . $nome_arquivo;
        }

        // Query de atualização dos dados básicos
        $sql_update = "UPDATE empresas SET 
                        nome = :nome, 
                        responsavel = :responsavel, 
                        email = :email, 
                        telefone = :telefone, 
                        cnpj = :cnpj, 
                        tipo = :tipo" .
                        ($logo ? ", logo = :logo" : "") . " 
                       WHERE id_empresa = :id";
        
        $stmt_update = $pdo->prepare($sql_update);
        $params_update = [
            ':nome' => $nome,
            ':responsavel' => $responsavel,
            ':email' => $email,
            ':telefone' => $telefone,
            ':cnpj' => $cnpj,
            ':tipo' => $tipo,
            ':id' => $id_empresa
        ];

        if ($logo) {
            $params_update[':logo'] = $logo;
        }

        $stmt_update->execute($params_update);

        // Lógica de alteração de senha segura
        if (!empty($senha_atual)) {
            // Busca a senha criptografada atual no banco
            $sql_senha = "SELECT senha FROM empresas WHERE id_empresa = :id";
            $stmt_senha = $pdo->prepare($sql_senha);
            $stmt_senha->execute([':id' => $id_empresa]);
            $empresa_senha = $stmt_senha->fetch(PDO::FETCH_ASSOC);

            if ($empresa_senha && password_verify($senha_atual, $empresa_senha['senha'])) {
                if (!empty($nova_senha) && $nova_senha === $confirmar_senha) {
                    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    
                    $sql_alt_senha = "UPDATE empresas SET senha = :senha WHERE id_empresa = :id";
                    $stmt_alt_senha = $pdo->prepare($sql_alt_senha);
                    $stmt_alt_senha->execute([':senha' => $nova_senha_hash, ':id' => $id_empresa]);
                } else {
                    throw new Exception("A nova senha e a confirmação não coincidem.");
                }
            } else {
                throw new Exception("A senha atual digitada está incorreta.");
            }
        }

        $pdo->commit();
        $mensagem_sucesso = "Configurações salvas com sucesso!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensagem_erro = $e->getMessage();
    }
}

// 3. Buscar os dados atualizados para exibir nos inputs
try {
    $sql_busca = "SELECT * FROM empresas WHERE id_empresa = :id";
    $stmt_busca = $pdo->prepare($sql_busca);
    $stmt_busca->execute([':id' => $id_empresa]);
    $dados_empresa = $stmt_busca->fetch(PDO::FETCH_ASSOC);
    
    if (!$dados_empresa) {
        die("Empresa padrão não encontrada no banco de dados.");
    }
} catch (PDOException $e) {
    die("Erro ao carregar configurações: " . $e->getMessage());
}

$pagina_atual = basename($_SERVER['PHP_SELF']);
$logo_empresa = $dados_empresa['logo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | PedidoFácil</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/configuracoes.css">
</head>

<body>

<div class="container-fluid">
<div class="row">

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
            <a href="dashboard.php"><i class="bi bi-grid-fill"></i> Dashboard</a>
        </li>
        <li class="<?= $pagina_atual == 'pedidos.php' ? 'active' : '' ?>">
            <a href="pedidos.php"><i class="bi bi-cart-fill"></i> Pedidos</a>
        </li>
        <li class="<?= $pagina_atual == 'producao.php' ? 'active' : '' ?>">
            <a href="producao.php"><i class="bi bi-fire"></i> Produção</a>
        </li>
        <li class="<?= $pagina_atual == 'produtos.php' ? 'active' : '' ?>">
            <a href="produtos.php"><i class="bi bi-box-seam-fill"></i> Produtos</a>
        </li>
        <li class="<?= $pagina_atual == 'relatorios.php' ? 'active' : '' ?>">
            <a href="relatorios.php"><i class="bi bi-bar-chart-fill"></i> Relatórios</a>
        </li>
        <li class="<?= $pagina_atual == 'configuracoes.php' ? 'active' : '' ?>">
            <a href="configuracoes.php"><i class="bi bi-gear-fill"></i> Configurações</a>
        </li>
    </ul>
</aside>

    <main class="col-lg-10 main-content">

        <div class="page-title">
            <h2>Configurações</h2>
            <p>Gerencie as informações da empresa e personalize o funcionamento do sistema.</p>
            <p id="dataHora" class="text-secondary"></p>
        </div>

        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $mensagem_sucesso ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagem_erro)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Erro: <?= $mensagem_erro ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="configuracoes.php" enctype="multipart/form-data">
            <div class="row g-4">

                <div class="col-lg-8">
                    <div class="config-card">
                        <h4><i class="bi bi-building"></i> Dados da Empresa</h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome da Empresa</label>
                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($dados_empresa['nome']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Responsável</label>
                                <input type="text" name="responsavel" class="form-control" value="<?= htmlspecialchars($dados_empresa['responsavel']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($dados_empresa['email']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($dados_empresa['telefone']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">CNPJ</label>
                                <input type="text" name="cnpj" class="form-control" value="<?= htmlspecialchars($dados_empresa['cnpj']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoria</label>
                                <select name="tipo" class="form-select">
                                    <option value="Hamburgueria" <?= $dados_empresa['tipo'] == 'Hamburgueria' ? 'selected' : '' ?>>Hamburgueria</option>
                                    <option value="Pizzaria" <?= $dados_empresa['tipo'] == 'Pizzaria' ? 'selected' : '' ?>>Pizzaria</option>
                                    <option value="Restaurante" <?= $dados_empresa['tipo'] == 'Restaurante' ? 'selected' : '' ?>>Restaurante</option>
                                    <option value="Lanchonete" <?= $dados_empresa['tipo'] == 'Lanchonete' ? 'selected' : '' ?>>Lanchonete</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="config-card text-center">
                        <h4><i class="bi bi-image"></i> Logo da Empresa</h4>
                        <div class="logo-preview">
                            <img src="<?= htmlspecialchars(!empty($logo_empresa) ? $logo_empresa : 'img/logo.png') ?>" alt="Logo">
                        </div>
                        <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                        <button type="button" class="btn btn-outline-danger mt-3" id="btnAlterarLogo">
                            <i class="bi bi-upload"></i> Alterar Logo
                        </button>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="config-card">
                        <h4><i class="bi bi-whatsapp text-success"></i> Integração WhatsApp</h4>
                        
                        <div class="mb-3">
                            <label class="form-label">Número conectado</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($dados_empresa['telefone']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control text-success fw-bold" value="Conectado" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Última sincronização</label>
                            <input type="text" class="form-control" value="Hoje às <?= date('H:i') ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="config-card">
                        <h4><i class="bi bi-shield-lock"></i> Segurança</h4>
                        <p class="text-muted small"><i class="bi bi-info-circle"></i> Preencha apenas se desejar alterar a senha atual do painel.</p>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Senha Atual</label>
                                <input type="password" name="senha_atual" class="form-control" placeholder="********">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" placeholder="********">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Confirmar Senha</label>
                                <input type="password" name="confirmar_senha" class="form-control" placeholder="********">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="config-card">
                        <h4 class="mb-4">
                            <i class="bi bi-sliders"></i>
                            Preferências
                        </h4>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center mb-3 ps-0">
                            <label class="form-check-label" for="notifCheck">Receber notificações</label>
                            <input class="form-check-input" type="checkbox" id="notifCheck" checked style="margin-left: 0;">
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center mb-3 ps-0">
                            <label class="form-check-label" for="somCheck">Som dos pedidos</label>
                            <input class="form-check-input" type="checkbox" id="somCheck" checked style="margin-left: 0;">
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center mb-3 ps-0">
                            <label class="form-check-label" for="escuroCheck">Modo escuro</label>
                            <input class="form-check-input" type="checkbox" id="escuroCheck" style="margin-left: 0;">
                        </div>

                        <div class="form-check form-switch d-flex justify-content-between align-items-center mb-4 ps-0">
                            <label class="form-check-label" for="autoCheck">Pedidos automáticos</label>
                            <input class="form-check-input" type="checkbox" id="autoCheck" checked style="margin-left: 0;">
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-floppy-fill"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/configuracoes.js"></script>

</body>
</html>
