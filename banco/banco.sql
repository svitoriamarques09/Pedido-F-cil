-- ===========================================
-- BANCO DE DADOS - LOJA DE SALGADOS
-- ===========================================

DROP DATABASE IF EXISTS loja_salgados;
CREATE DATABASE loja_salgados
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE loja_salgados;

-- ===========================================
-- TABELA CATEGORIAS
-- ===========================================

CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- ===========================================
-- TABELA PRODUTOS
-- ===========================================

CREATE TABLE produtos (
    id_produto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco_unitario DECIMAL(10,2) NOT NULL,
    estoque_atual INT NOT NULL DEFAULT 0,
    id_categoria INT,

    CONSTRAINT fk_produto_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
        ON DELETE SET NULL
);

-- ===========================================
-- TABELA CLIENTES
-- ===========================================

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14),
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(200)
);

-- ===========================================
-- TABELA FUNCIONÁRIOS
-- ===========================================

CREATE TABLE funcionarios (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14),
    telefone VARCHAR(20),
    email VARCHAR(100),
    cargo VARCHAR(50),
    login VARCHAR(50) UNIQUE,
    senha VARCHAR(255)
);

-- ===========================================
-- TABELA PEDIDOS
-- ===========================================

CREATE TABLE pedidos (

    id_pedido INT AUTO_INCREMENT PRIMARY KEY,

    id_cliente INT,
    id_funcionario INT,

    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    forma_pagamento ENUM(
        'Dinheiro',
        'Cartao_Credito',
        'Cartao_Debito',
        'Pix'
    ) NOT NULL,

    status_pedido ENUM(
        'Pendente',
        'Pago',
        'Cancelado'
    ) DEFAULT 'Pago',

    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente),

    CONSTRAINT fk_pedido_funcionario
        FOREIGN KEY (id_funcionario)
        REFERENCES funcionarios(id_funcionario)
);

-- ===========================================
-- TABELA ITENS DO PEDIDO
-- ===========================================

CREATE TABLE itens_pedido (

    id_item INT AUTO_INCREMENT PRIMARY KEY,

    id_pedido INT NOT NULL,

    id_produto INT NOT NULL,

    quantidade INT NOT NULL,

    preco_praticado DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_item_pedido
        FOREIGN KEY (id_pedido)
        REFERENCES pedidos(id_pedido)
        ON DELETE CASCADE,

    CONSTRAINT fk_item_produto
        FOREIGN KEY (id_produto)
        REFERENCES produtos(id_produto)
);

-- ===========================================
-- DADOS INICIAIS
-- ===========================================

INSERT INTO categorias (nome) VALUES
('Fritos'),
('Assados'),
('Bebidas'),
('Doces');

INSERT INTO produtos (nome, descricao, preco_unitario, estoque_atual, id_categoria) VALUES
('Coxinha de Frango','Frango com catupiry',6.00,100,1),
('Pastel de Carne','Pastel tradicional',7.00,60,1),
('Esfiha de Carne','Esfiha aberta',5.50,80,2),
('Enroladinho de Salsicha','Massa assada',5.00,70,2),
('Suco de Laranja','500ml',7.00,50,3),
('Refrigerante Lata','350ml',6.50,90,3),
('Brigadeiro','Doce tradicional',3.50,40,4);

INSERT INTO clientes (nome, cpf, telefone, email, endereco) VALUES
('João Silva','111.111.111-11','31999999999','joao@email.com','Rua A'),
('Mariana Costa','222.222.222-22','31988888888','mariana@email.com','Rua B');

INSERT INTO funcionarios (nome, cpf, telefone, email, cargo, login, senha) VALUES
('Administrador','000.000.000-00','31999990000','admin@loja.com','Administrador','admin','123456'),
('Maria Souza','333.333.333-33','31977777777','maria@email.com','Atendente','maria','123456'),
('Carlos Lima','444.444.444-44','31966666666','carlos@email.com','Atendente','carlos','123456');

INSERT INTO pedidos
(id_cliente,id_funcionario,forma_pagamento,status_pedido)
VALUES
(1,2,'Pix','Pago'),
(2,3,'Cartao_Debito','Pago');

INSERT INTO itens_pedido
(id_pedido,id_produto,quantidade,preco_praticado)
VALUES
(1,1,2,6.00),
(1,5,1,7.00),
(2,3,1,5.50);