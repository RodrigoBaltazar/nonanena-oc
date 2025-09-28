#!/bin/bash

# Script de Deploy para oc.nonanena.com.br
# Execute como root ou com sudo

echo "🚀 Iniciando deploy do Sistema de Gestão de Produtos..."

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
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

# 1. Parar containers existentes
print_status "Parando containers existentes..."
docker-compose down 2>/dev/null || true

# 2. Atualizar código (se for um repositório Git)
if [ -d ".git" ]; then
    print_status "Atualizando código do repositório..."
    git pull
fi

# 3. Construir e iniciar containers
print_status "Construindo e iniciando containers..."
docker-compose up --build -d

# 4. Aguardar containers iniciarem
print_status "Aguardando containers iniciarem..."
sleep 10

# 5. Verificar se containers estão rodando
if ! docker ps | grep -q "produtos-app"; then
    print_error "Container não está rodando!"
    exit 1
fi

# 6. Configurar Nginx
print_status "Configurando Nginx..."

# Escolher configuração (HTTP ou HTTPS)
echo "Escolha a configuração do Nginx:"
echo "1) HTTP apenas (para testes)"
echo "2) HTTPS com SSL (produção)"
read -p "Digite sua escolha (1 ou 2): " choice

case $choice in
    1)
        CONFIG_FILE="oc.nonanena.com.br-http.conf"
        print_warning "Usando configuração HTTP (sem SSL)"
        ;;
    2)
        CONFIG_FILE="oc.nonanena.com.br.conf"
        print_warning "Usando configuração HTTPS (com SSL)"
        print_warning "Certifique-se de que os certificados SSL estão configurados!"
        ;;
    *)
        print_error "Escolha inválida!"
        exit 1
        ;;
esac

# Copiar configuração do Nginx
cp "$CONFIG_FILE" /etc/nginx/sites-available/oc.nonanena.com.br

# Criar link simbólico
ln -sf /etc/nginx/sites-available/oc.nonanena.com.br /etc/nginx/sites-enabled/

# Testar configuração do Nginx
print_status "Testando configuração do Nginx..."
if nginx -t; then
    print_status "Configuração do Nginx OK!"
    
    # Recarregar Nginx
    print_status "Recarregando Nginx..."
    systemctl reload nginx
    
    print_status "Deploy concluído com sucesso!"
    echo ""
    echo "🌐 Acesse: http://oc.nonanena.com.br"
    echo "📱 PWA instalável no celular"
    echo "📄 Geração de PDF funcionando"
    echo ""
    echo "📋 Comandos úteis:"
    echo "  - Ver logs: docker-compose logs -f"
    echo "  - Parar: docker-compose down"
    echo "  - Reiniciar: docker-compose restart"
    
else
    print_error "Erro na configuração do Nginx!"
    print_error "Verifique os logs: tail -f /var/log/nginx/error.log"
    exit 1
fi
