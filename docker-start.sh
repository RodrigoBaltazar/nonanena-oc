#!/bin/bash

# Script de inicialização do container com .env
echo "🐳 Iniciando Sistema de Gestão de Produtos..."

# Criar diretórios necessários
mkdir -p /var/www/html/data
mkdir -p /var/www/html/uploads

# Definir permissões
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod 777 /var/www/html/data

# Copiar .env se existir no host
if [ -f "/host/.env" ]; then
    echo "📋 Copiando arquivo .env..."
    cp /host/.env /var/www/html/.env
    chmod 600 /var/www/html/.env
    chown www-data:www-data /var/www/html/.env
else
    echo "⚠️  Arquivo .env não encontrado, usando configurações padrão"
fi

# Iniciar Apache
echo "✅ Sistema iniciado com sucesso!"
echo "📱 Acesse: http://localhost:8080"
echo "🐳 Container rodando..."

exec apache2-foreground
