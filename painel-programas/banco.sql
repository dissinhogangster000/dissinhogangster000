CREATE DATABASE IF NOT EXISTS painel_programas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE painel_programas;

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE programas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT NULL,
  nome VARCHAR(120) NOT NULL,
  icone VARCHAR(8) NOT NULL DEFAULT '',
  descricao TEXT NOT NULL,
  preco_dia DECIMAL(10,2) NOT NULL DEFAULT 0,
  preco_semana DECIMAL(10,2) NOT NULL DEFAULT 0,
  preco_mes DECIMAL(10,2) NOT NULL DEFAULT 0,
  destaque TINYINT(1) NOT NULL DEFAULT 0,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE alugueis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  programa_id INT NOT NULL,
  cliente_nome VARCHAR(120) NOT NULL,
  cliente_contato VARCHAR(60) NOT NULL,
  plano ENUM('dia','semana','mes') NOT NULL DEFAULT 'dia',
  valor DECIMAL(10,2) NOT NULL DEFAULT 0,
  data_inicio DATE NOT NULL,
  data_fim DATE NOT NULL,
  codigo_acesso VARCHAR(120) NOT NULL DEFAULT '',
  status ENUM('ativo','concluido','vencido','cancelado') NOT NULL DEFAULT 'ativo',
  observacao TEXT NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categorias (nome) VALUES
('Streaming'),
('Aplicativos'),
('Softwares'),
('Jogos');

INSERT INTO programas (categoria_id, nome, icone, descricao, preco_dia, preco_semana, preco_mes, destaque, ativo) VALUES
(1, 'Netflix Premium', '📺', 'Acesso Netflix em 4K, perfil completo. Renove quando quiser.', 2.50, 9.90, 24.90, 1, 1),
(1, 'Spotify Premium', '🎵', 'Spotify sem anúncios com download de músicas.', 1.50, 5.90, 14.90, 1, 1),
(2, 'Canva Pro', '🎨', 'Todos os recursos premium do Canva.', 2.00, 6.90, 17.90, 0, 1),
(3, 'Office 365', '💼', 'Pacote Office completo com 1TB de OneDrive.', 3.00, 9.90, 29.90, 0, 1),
(4, 'Game Pass Ultimate', '🎮', 'Catálogo de jogos no console e PC.', 4.00, 14.90, 39.90, 1, 1);