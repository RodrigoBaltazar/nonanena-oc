#!/bin/bash

# Script de Configuração para Produção
# Execute na VPS para configurar o sistema com credenciais seguras

echo "🔐 Configurando Sistema de Gestão de Produtos para Produção..."

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    print_error "Execute este script como root ou com sudo"
    exit 1
fi

# 1. Verificar se arquivo .env já existe
if [ -f ".env" ]; then
    print_warning "Arquivo .env já existe!"
    read -p "Deseja sobrescrever? (y/N): " overwrite
    if [[ ! $overwrite =~ ^[Yy]$ ]]; then
        print_status "Configuração cancelada."
        exit 0
    fi
fi

# 2. Solicitar credenciais
echo ""
print_warning "Configuração de Credenciais de Acesso"
echo "============================================="

read -p "Digite o nome de usuário: " username
read -s -p "Digite a senha: " password
echo ""

# 3. Solicitar configurações opcionais
echo ""
print_warning "Configurações Opcionais"
echo "============================="

read -p "Nome da aplicação [Sistema de Gestão de Produtos]: " app_name
read -p "URL da aplicação [http://oc.nonanena.com.br]: " app_url
read -p "Timeout da sessão em minutos [60]: " session_timeout

# Valores padrão
app_name=${app_name:-"Sistema de Gestão de Produtos"}
app_url=${app_url:-"http://oc.nonanena.com.br"}
session_timeout=${session_timeout:-60}

# Converter minutos para segundos
session_timeout=$((session_timeout * 60))

# 4. Criar arquivo .env
print_status "Criando arquivo .env..."

cat > .env << EOF
# Configurações do Sistema de Gestão de Produtos
# Gerado automaticamente em $(date)

# ===========================================
# CONFIGURAÇÕES DE AUTENTICAÇÃO
# ===========================================
LOGIN_USERNAME=$username
LOGIN_PASSWORD=$password

# ===========================================
# CONFIGURAÇÕES DE SESSÃO
# ===========================================
SESSION_TIMEOUT=$session_timeout

# ===========================================
# CONFIGURAÇÕES DE SEGURANÇA
# ===========================================
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_TIME=900

# ===========================================
# CONFIGURAÇÕES DO BANCO DE DADOS
# ===========================================
DB_PATH=data/produtos.db

# ===========================================
# CONFIGURAÇÕES DA APLICAÇÃO
# ===========================================
APP_NAME=$app_name
APP_VERSION=1.0.0

# ===========================================
# CONFIGURAÇÕES DE PRODUÇÃO
# ===========================================
APP_ENV=production
APP_URL=$app_url

# ===========================================
# CONFIGURAÇÕES DE LOG
# ===========================================
DEBUG=false
LOG_LEVEL=info
EOF

# 5. Definir permissões seguras
print_status "Definindo permissões seguras..."
chmod 600 .env
chown www-data:www-data .env 2>/dev/null || true

# 6. Criar diretório data se não existir
print_status "Criando diretórios necessários..."
mkdir -p data
chmod 755 data
chown www-data:www-data data 2>/dev/null || true

# 7. Verificar configuração
print_status "Verificando configuração..."
if [ -f ".env" ]; then
    print_status "Arquivo .env criado com sucesso!"
    echo ""
    print_warning "Credenciais configuradas:"
    echo "  Usuário: $username"
    echo "  Senha: [OCULTA]"
    echo "  Aplicação: $app_name"
    echo "  URL: $app_url"
    echo "  Timeout: $((session_timeout / 60)) minutos"
    echo ""
    print_status "Configuração concluída com sucesso!"
    echo ""
    print_warning "Próximos passos:"
    echo "1. Execute: docker-compose up -d"
    echo "2. Configure o Nginx"
    echo "3. Teste o acesso ao sistema"
    echo ""
    print_warning "IMPORTANTE:"
    echo "- O arquivo .env contém credenciais sensíveis"
    echo "- Não compartilhe este arquivo"
    echo "- Faça backup regular do diretório data/"
else
    print_error "Erro ao criar arquivo .env!"
    exit 1
fi
