# Sistema de Gestão de Produtos

Sistema web simples para cadastrar produtos e gerar relatórios em PDF, com suporte a PWA para instalação no celular.

## Funcionalidades

- ✅ Cadastrar produtos (por kg ou unidade)
- ✅ Definir preços
- ✅ Gerar PDF com listagem
- ✅ PWA (instalável no celular)
- ✅ Interface responsiva
- ✅ Sistema de login seguro
- ✅ Proteção contra ataques de força bruta

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

1. **Configurar Credenciais (Primeira vez):**
   - Copie `env.example` para `.env`
   - Edite as credenciais no arquivo `.env`
   - Ou execute: `sudo ./setup-producao.sh`

2. **Fazer Login:**
   - Acesse o sistema
   - Use as credenciais configuradas no `.env`

3. **Cadastrar Produto:**
   - Preencha nome, tipo (kg/unidade) e preço
   - Clique em "Adicionar Produto"

4. **Gerar PDF:**
   - Clique em "Gerar PDF" para baixar relatório

5. **Instalar no Celular:**
   - Acesse pelo navegador
   - Aparecerá opção "Instalar App"

6. **Fazer Logout:**
   - Clique em "Sair" no canto superior direito

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

## 🔐 Configuração de Produção

### **Configuração Rápida:**
```bash
# 1. Clonar repositório
git clone https://github.com/SEU_USUARIO/sistema-gestao-produtos.git
cd sistema-gestao-produtos

# 2. Configurar credenciais
sudo ./setup-producao.sh

# 3. Iniciar aplicação
docker-compose up -d
```

### **Configuração Manual:**
```bash
# 1. Copiar template de configuração
cp env.example .env

# 2. Editar credenciais
nano .env

# 3. Definir permissões
chmod 600 .env
```

📖 **Documentação completa:** [CONFIGURACAO-PRODUCAO.md](CONFIGURACAO-PRODUCAO.md)

## Tecnologias

- **PHP 8.4** com Apache
- **SQLite** para banco de dados
- **TCPDF** para geração de PDF
- **PWA** (Service Worker + Manifest)
- **Docker** para containerização
- **Sistema de configuração .env** para credenciais seguras
- **HTML/CSS/JavaScript** vanilla

## Banco de Dados

Tabela `produtos`:
- `id` - Chave primária
- `nome` - Nome do produto
- `tipo` - 'kg' ou 'unidade'
- `preco` - Preço (decimal)
- `created_at` - Data de criação
