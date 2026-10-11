# Desbloqueio Pro — Painel de Aluguel de Programas

Sistema em **PHP + MySQL** com site público e painel administrativo para gerenciar o aluguel de programas desbloqueados.

## Estrutura

```
painel-programas/
├── index.php                 → Site público (catálogo de programas)
├── programa.php              → Detalhe do programa + formulário de pedido
├── alugar.php                → Processa o pedido e gera link do WhatsApp
├── instalar.php              → Cria o banco e dados iniciais (rodar 1 vez)
├── banco.sql                 → Schema do banco para importar no phpMyAdmin
├── incluir/                  → Configurações, funções e template do site
├── assets/                   → CSS/JS do site público
└── painel/                   → Painel administrativo
    ├── login.php             → Login
    ├── index.php             → Dashboard com métricas
    ├── programas.php         → CRUD de programas
    ├── alugueis.php          → CRUD + status de aluguéis
    └── categorias.php        → CRUD de categorias
```

## Instalação

1. Instale o **XAMPP** (apache + PHP + MySQL) ou use uma hospedagem com PHP.
2. Copie a pasta `painel-programas` para a raiz do servidor (ex.: `htdocs/painel-programas`).
3. Se necessário, ajuste as credenciais do banco em `incluir/config.php` (usuário/senha do MySQL).
4. Acesse no navegador:
   `http://localhost/painel-programas/instalar.php`
5. Pronto. **Delete o arquivo `instalar.php` em seguida.**

Ou, em vez do passo 4, importe o `banco.sql` no phpMyAdmin.

## Acesso ao painel

`http://localhost/painel-programas/painel/login.php`

| Campo | Valor |
|---|---|
| E-mail | `admin@admin.com` |
| Senha | `admin123` |

> Mude a senha depois de entrar (ou edite diretamente no banco) e altere o número do WhatsApp em `incluir/config.php`.

## Como funciona

- O **site público** lista os programas com preços (diário, semanal, mensal). O cliente escolhe o plano, preenche nome/contato e o pedido é registrado com link do WhatsApp para envio automático.
- O **painel** mostra métricas (aluguéis ativos, receita do mês, pedidos hoje), permite cadastrar/editar/excluir programas e categorias, e gerenciar cada aluguel (ativo, concluído, vencido, cancelado) incluindo o código de acesso entregue ao cliente.
- Aluguéis "ativo" com data final passada são marcados como **vencido** automaticamente ao abrir o painel.

## Personalização

- Nome do site, descrição e WhatsApp: `incluir/config.php`
- Cores e layout: `assets/css/site.css` (site) e `painel/assets/painel.css` (painel)

## Requisitos

- PHP 7.4+ (com PDO MySQL)
- MySQL 5.7+ / MariaDB