# Sistema de Gestão de Produtos

Sistema web simples para cadastrar produtos e gerar relatórios em PDF, com suporte a PWA para instalação no celular.

## Funcionalidades

- ✅ Cadastrar produtos (por kg ou unidade)
- ✅ Definir preços
- ✅ Gerar PDF com listagem
- ✅ PWA (instalável no celular)
- ✅ Interface responsiva

## Instalação Rápida

### 🐳 Com Docker (Recomendado)

1. **Construir e executar:**
```bash
docker-compose up --build
```

2. **Acessar:**
```
http://localhost:8000
```

### 💻 Desenvolvimento Local

1. **Instalar dependências:**
```bash
composer install
```

2. **Executar servidor PHP:**
```bash
php -S localhost:8000
```

3. **Acessar:**
```
http://localhost:8000
```

## Estrutura do Projeto

- `index.php` - Interface principal
- `gerar_pdf.php` - Geração de PDF
- `manifest.json` - Configuração PWA
- `sw.js` - Service Worker
- `Dockerfile` - Configuração do container
- `docker-compose.yml` - Orquestração dos containers
- `data/produtos.db` - Banco SQLite (criado automaticamente)

## Como Usar

1. **Cadastrar Produto:**
   - Preencha nome, tipo (kg/unidade) e preço
   - Clique em "Adicionar Produto"

2. **Gerar PDF:**
   - Clique em "Gerar PDF" para baixar relatório

3. **Instalar no Celular:**
   - Acesse pelo navegador
   - Aparecerá opção "Instalar App"

## Comandos Docker

```bash
# Construir e executar
docker-compose up --build

# Executar em background
docker-compose up -d

# Parar containers
docker-compose down

# Ver logs
docker-compose logs -f

# Acessar container
docker-compose exec app bash

# Reconstruir apenas a aplicação
docker-compose build app
```

## Tecnologias

- **PHP 8.4** com Apache
- **SQLite** para banco de dados
- **TCPDF** para geração de PDF
- **PWA** (Service Worker + Manifest)
- **Docker** para containerização
- **HTML/CSS/JavaScript** vanilla

## Banco de Dados

Tabela `produtos`:
- `id` - Chave primária
- `nome` - Nome do produto
- `tipo` - 'kg' ou 'unidade'
- `preco` - Preço (decimal)
- `created_at` - Data de criação
