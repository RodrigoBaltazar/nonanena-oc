#!/bin/bash

# Script de Deploy Docker com Configuração .env
# Execute na VPS para deploy completo

echo "🐳 Deploy Docker - Sistema de Gestão de Produtos..."

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

# 1. Verificar se Docker está instalado
print_status "Verificando Docker..."
if ! command -v docker &> /dev/null; then
    print_error "Docker não está instalado!"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose não está instalado!"
    exit 1
fi

# 2. Verificar se arquivo .env existe
if [ ! -f ".env" ]; then
    print_warning "Arquivo .env não encontrado!"
    
    if [ -f "env.example" ]; then
        print_status "Copiando template de configuração..."
        cp env.example .env
        print_warning "Arquivo .env criado com configurações padrão!"
        print_warning "Edite o arquivo .env com suas credenciais antes de continuar."
        print_warning "Execute: nano .env"
        exit 1
    else
        print_error "Template env.example não encontrado!"
        exit 1
    fi
fi

# 3. Verificar configurações básicas no .env
print_status "Verificando configurações..."
if ! grep -q "LOGIN_USERNAME=" .env; then
    print_error "Configuração LOGIN_USERNAME não encontrada no .env!"
    exit 1
fi

if ! grep -q "LOGIN_PASSWORD=" .env; then
    print_error "Configuração LOGIN_PASSWORD não encontrada no .env!"
    exit 1
fi

# 4. Parar containers existentes
print_status "Parando containers existentes..."
docker-compose down 2>/dev/null || true

# 5. Atualizar código (se for um repositório Git)
if [ -d ".git" ]; then
    print_status "Atualizando código do repositório..."
    git pull
fi

# 6. Construir e iniciar containers
print_status "Construindo e iniciando containers..."
docker-compose up --build -d

# 7. Aguardar containers iniciarem
print_status "Aguardando containers iniciarem..."
sleep 15

# 8. Verificar se containers estão rodando
if ! docker ps | grep -q "produtos-app"; then
    print_error "Container não está rodando!"
    print_status "Logs do container:"
    docker-compose logs
    exit 1
fi

# 9. Testar aplicativo
print_status "Testando aplicativo..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8080 | grep -q "200\|302"; then
    print_status "Aplicativo funcionando!"
else
    print_error "Aplicativo não está respondendo!"
    print_status "Logs do container:"
    docker-compose logs
    exit 1
fi

# 10. Obter IP da VPS
VPS_IP=$(curl -s ifconfig.me 2>/dev/null || curl -s ipinfo.io/ip 2>/dev/null || hostname -I | awk '{print $1}')
if [ -z "$VPS_IP" ]; then
    VPS_IP="SEU_IP_AQUI"
    print_warning "Não foi possível obter IP automaticamente"
fi

# 11. Mostrar informações de acesso
echo ""
print_status "🎉 Deploy concluído com sucesso!"
echo ""
echo "🌐 Acesse o aplicativo em:"
echo "   http://$VPS_IP:8080"
echo "   http://localhost:8080 (local na VPS)"
echo ""
echo "🔐 Credenciais de acesso:"
echo "   Usuário: $(grep LOGIN_USERNAME .env | cut -d'=' -f2)"
echo "   Senha: [configurada no .env]"
echo ""
echo "📱 Para testar PWA no celular:"
echo "   1. Acesse http://$VPS_IP:8080 no celular"
echo "   2. Faça login com suas credenciais"
echo "   3. Deve aparecer opção 'Instalar App'"
echo ""
echo "🔧 Comandos úteis:"
echo "   - Ver logs: docker-compose logs -f"
echo "   - Parar: docker-compose down"
echo "   - Reiniciar: docker-compose restart"
echo "   - Status: docker ps"
echo ""
print_warning "Próximo passo: Configure o Nginx para domínio personalizado"
echo "   sudo cp oc.nonanena.com.br-http.conf /etc/nginx/sites-available/oc.nonanena.com.br"
echo "   sudo ln -s /etc/nginx/sites-available/oc.nonanena.com.br /etc/nginx/sites-enabled/"
echo "   sudo nginx -t && sudo systemctl reload nginx"
