<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("config/conexao.php");

$idEmpresa = 1;

$telefoneCliente = $_GET['telefone'] ?? null;
$mensagemCliente = isset($_GET['mensagem']) ? trim($_GET['mensagem']) : null;

if (!$telefoneCliente || !$mensagemCliente) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Aguardando parâmetros válidos...";
    exit;
}

$telefoneCliente = preg_replace('/[^0-9]/', '', $telefoneCliente);

try {
    $saudacoes = ['oi', 'ola', 'olá', 'bom dia', 'boa tarde', 'boa noite', 'menu', 'cardapio', 'reiniciar'];
    if (in_array(strtolower($mensagemCliente), $saudacoes)) {
        $pdo->prepare("DELETE FROM chatbot_sessoes WHERE id_empresa = ? AND telefone_cliente = ?")
            ->execute([$idEmpresa, $telefoneCliente]);
        $sessao = false;
    } else {
        $stmt = $pdo->prepare("SELECT * FROM chatbot_sessoes WHERE id_empresa = ? AND telefone_cliente = ?");
        $stmt->execute([$idEmpresa, $telefoneCliente]);
        $sessao = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$sessao) {
        $pdo->prepare("INSERT INTO chatbot_sessoes (id_empresa, telefone_cliente, estado, dados_temporarios) VALUES (?, ?, 'inicio', '{}')")
            ->execute([$idEmpresa, $telefoneCliente]);
        $estadoAtual = 'inicio';
        $dadosTemporarios = ['carrinho' => []]; // Inicializa o carrinho vazio
    } else {
        $estadoAtual = $sessao['estado'];
        $dadosTemporarios = $sessao['dados_temporarios'] ? json_decode($sessao['dados_temporarios'], true) : [];
        if (!isset($dadosTemporarios['carrinho'])) {
            $dadosTemporarios['carrinho'] = [];
        }
    }

    $respostaBot = "";

    switch ($estadoAtual) {
        case 'inicio':
            exibirCardapio($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios);
            break;

        case 'aguardando_produto':
            $opcaoDigitada = intval($mensagemCliente);
            $idRealProduto = $dadosTemporarios['cardapio_mapa'][$opcaoDigitada] ?? null;

            $stmtProd = $pdo->prepare("SELECT * FROM produtos WHERE id_produto = ? AND id_empresa = ?");
            $stmtProd->execute([$idRealProduto, $idEmpresa]);
            $produtoEscolhido = $stmtProd->fetch(PDO::FETCH_ASSOC);

            if ($produtoEscolhido) {
                // Guarda qual item está sendo manipulado no momento
                $dadosTemporarios['item_atual'] = [
                    'id_produto' => $produtoEscolhido['id_produto'],
                    'nome_produto' => $produtoEscolhido['nome'],
                    'preco_produto' => $produtoEscolhido['preco']
                ];

                unset($dadosTemporarios['cardapio_mapa']);

                $respostaBot = "Ótima escolha: *" . $produtoEscolhido['nome'] . "*!\n";
                $respostaBot .= "Quantas unidades você vai querer? (Digite apenas o número)";
                atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_quantidade', $dadosTemporarios);
            } else {
                $respostaBot = "⚠️ Opção inválida! Por favor, digite um número correspondente ao menu.";
            }
            break;

        case 'aguardando_quantidade':
            if (is_numeric($mensagemCliente) && intval($mensagemCliente) > 0) {
                $qtd = intval($mensagemCliente);
                
                // Monta o item e joga dentro da lista do carrinho
                $itemNovo = $dadosTemporarios['item_atual'];
                $itemNovo['quantidade'] = $qtd;
                $itemNovo['subtotal'] = $itemNovo['preco_produto'] * $qtd;
                
                $dadosTemporarios['carrinho'][] = $itemNovo;
                unset($dadosTemporarios['item_atual']); // Limpa o temporário provisório

                // Pergunta o que o cliente quer fazer agora
                $respostaBot = "✨ Adicionado com sucesso!\n\n";
                $respostaBot .= "O que deseja fazer agora?\n";
                $respostaBot .= "1️⃣ - Adicionar mais itens ao pedido 🛒\n";
                $respostaBot .= "2️⃣ - Fechar e finalizar o pedido 🏁\n\n";
                $respostaBot .= "Digite o número da opção:";
                
                atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_decisao_carrinho', $dadosTemporarios);
            } else {
                $respostaBot = "⚠️ Por favor, digite uma quantidade válida em números.";
            }
            break;

        case 'aguardando_decisao_carrinho':
            if ($mensagemCliente === '1') {
                // Volta para o menu para escolher mais coisas
                exibirCardapio($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios);
            } else if ($mensagemCliente === '2') {
                // Calcula o valor total juntando tudo do carrinho
                $total = 0;
                foreach ($dadosTemporarios['carrinho'] as $item) {
                    $total += $item['subtotal'];
                }
                $dadosTemporarios['valor_total'] = $total;

                $respostaBot = "Legal! Vamos fechar a conta.\n";
                $respostaBot .= "Por favor, digite o seu *NOME* completo:";
                atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_nome', $dadosTemporarios);
            } else {
                $respostaBot = "⚠️ Opção inválida. Digite 1 para continuar comprando ou 2 para finalizar.";
            }
            break;

        case 'aguardando_nome':
            $dadosTemporarios['nome_cliente'] = $mensagemCliente;
            $respostaBot = "Obrigado, " . $mensagemCliente . "!\n";
            $respostaBot .= "Agora, digite o *ENDEREÇO* completo para entrega (Rua, Número, Bairro):";
            atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_endereco', $dadosTemporarios);
            break;

        case 'aguardando_endereco':
            $dadosTemporarios['endereco'] = $mensagemCliente;
            $totalPedido = $dadosTemporarios['valor_total'] ?? 0;

            $respostaBot = "O seu pedido ficou em um total de: *R$ " . number_format($totalPedido, 2, ',', '.') . "* 💰\n\n";
            $respostaBot .= "Qual será a *FORMA DE PAGAMENTO*? 💳\n\n";
            $respostaBot .= "1️⃣ - *Pix*\n";
            $respostaBot .= "2️⃣ - *Cartão* (Levar maquininha)\n";
            $respostaBot .= "3️⃣ - *Dinheiro*\n\n";
            $respostaBot .= "Digite o número correspondente:";
            atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_pagamento', $dadosTemporarios);
            break;

        case 'aguardando_pagamento':
            $opcao = trim($mensagemCliente);
            if ($opcao === '1') {
                $dadosTemporarios['pagamento'] = 'Pix';
                $dadosTemporarios['observacao'] = '';
                finalizarPedidoEEnviar($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios);
            } else if ($opcao === '2') {
                $dadosTemporarios['pagamento'] = 'Cartão';
                $dadosTemporarios['observacao'] = 'Levar maquininha';
                finalizarPedidoEEnviar($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios);
            } else if ($opcao === '3') {
                $dadosTemporarios['pagamento'] = 'Dinheiro';
                $respostaBot = "💵 Escolheu *Dinheiro*.\n";
                $respostaBot .= "Precisa de troco? Se sim, digite para quanto. Se não, digite *Não*:";
                atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_troco', $dadosTemporarios);
            } else {
                $respostaBot = "⚠️ Opção inválida! Digite 1 para Pix, 2 para Cartão ou 3 para Dinheiro.";
            }
            break;

        case 'aguardando_troco':
            $respostaTroco = trim($mensagemCliente);
            $dadosTemporarios['observacao'] = (strtolower($respostaTroco) === 'não' || strtolower($respostaTroco) === 'nao') ? 'Sem troco' : 'Precisa de ' . $respostaTroco;
            finalizarPedidoEEnviar($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios);
            break;
    }

    if (!empty($respostaBot)) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $respostaBot;
    }

} catch (PDOException $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Erro no Webhook: " . $e->getMessage();
}

function exibirCardapio($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios) {
    $stmtProdutos = $pdo->prepare("SELECT id_produto, nome, preco FROM produtos WHERE id_empresa = ?");
    $stmtProdutos->execute([$idEmpresa]);
    $produtos = $stmtProdutos->fetchAll(PDO::FETCH_ASSOC);

    $respostaBot = "Aqui está o nosso cardápio de salgados e bebidas fresquinhas: 😋\n\n";

    if (count($produtos) > 0) {
        $itemNumero = 1;
        $mapeamentoCardapio = [];

        foreach ($produtos as $p) {
            $respostaBot .= "👉 *" . $itemNumero . "* - " . $p['nome'] . " (R$ " . number_format($p['preco'], 2, ',', '.') . ")\n";
            $mapeamentoCardapio[$itemNumero] = $p['id_produto'];
            $itemNumero++;
        }
        
        $dadosTemporarios['cardapio_mapa'] = $mapeamentoCardapio;
        $respostaBot .= "\nDigite o *NÚMERO* do item que deseja adicionar ao carrinho:";
        
        atualizarSessao($pdo, $idEmpresa, $telefoneCliente, 'aguardando_produto', $dadosTemporarios);
    } else {
        $respostaBot .= "Desculpe, não temos itens disponíveis no momento. 😔";
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo $respostaBot;
    exit;
}

function atualizarSessao($pdo, $idEmpresa, $telefoneCliente, $novoEstado, $dados) {
    $dadosJson = json_encode($dados, JSON_UNESCAPED_UNICODE);
    $stmt = $pdo->prepare("UPDATE chatbot_sessoes SET estado = ?, dados_temporarios = ? WHERE id_empresa = ? AND telefone_cliente = ?");
    $stmt->execute([$novoEstado, $dadosJson, $idEmpresa, $telefoneCliente]);
}

function finalizarPedidoEEnviar($pdo, $idEmpresa, $telefoneCliente, $dadosTemporarios) {
    // 1. Cria o cliente
    $insCliente = $pdo->prepare("INSERT INTO clientes (nome, telefone, endereco, id_empresa) VALUES (?, ?, ?, ?)");
    $insCliente->execute([$dadosTemporarios['nome_cliente'], $telefoneCliente, $dadosTemporarios['endereco'], $idEmpresa]);
    $idCliente = $pdo->lastInsertId(); 

    // 2. Cria o pedido principal (REMOVIDO O CAMPO 'PAGAMENTO' QUE NÃO EXISTE NO SEU BANCO)
    $insPedido = $pdo->prepare("
        INSERT INTO pedidos (id_empresa, id_cliente, valor_total, status, data_pedido) 
        VALUES (?, ?, ?, 'Pendente', NOW())
    ");
    $insPedido->execute([$idEmpresa, $idCliente, $dadosTemporarios['valor_total']]);
    $idPedidoGerado = $pdo->lastInsertId();

    // 3. Roda o loop para gravar todos os itens do carrinho na tabela pedido_itens
    $insItem = $pdo->prepare("
        INSERT INTO pedido_itens (id_pedido, id_produto, quantidade, preco_unitario) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($dadosTemporarios['carrinho'] as $itemDoCarrinho) {
        $insItem->execute([
            $idPedidoGerado, 
            $itemDoCarrinho['id_produto'], 
            $itemDoCarrinho['quantidade'], 
            $itemDoCarrinho['preco_produto']
        ]);
    }

    // 4. Limpa a sessão
    $pdo->prepare("DELETE FROM chatbot_sessoes WHERE id_empresa = ? AND telefone_cliente = ?")
        ->execute([$idEmpresa, $telefoneCliente]);

    // 5. Gera a mensagem resumida e bonita de sucesso para o cliente no WhatsApp
    $respostaBot = "🎉 *PEDIDO CONFIRMADO COM SUCESSO!* 🎉\n\n";
    $respostaBot .= "ID do Pedido: *#" . $idPedidoGerado . "*\n";
    $respostaBot .= "Nome do Cliente: *" . $dadosTemporarios['nome_cliente'] . "*\n\n";
    $respostaBot .= "🛒 *PRODUTOS PEDIDOS:*\n";
    
    foreach ($dadosTemporarios['carrinho'] as $itemDoCarrinho) {
        $respostaBot .= "• " . $itemDoCarrinho['quantidade'] . "x " . $itemDoCarrinho['nome_produto'] . "\n";
    }

    $respostaBot .= "\n💰 Total: *R$ " . number_format($dadosTemporarios['valor_total'], 2, ',', '.') . "*\n";
    $respostaBot .= "Forma de Pagamento: *" . $dadosTemporarios['pagamento'] . "*\n";
    if (!empty($dadosTemporarios['observacao'])) {
        $respostaBot .= "Obs: _" . $dadosTemporarios['observacao'] . "_\n";
    }
    $respostaBot .= "\nO seu pedido completo já está no painel da cozinha! Obrigado. 😋";

    header('Content-Type: text/plain; charset=utf-8');
    echo $respostaBot;
    exit;
}
?>