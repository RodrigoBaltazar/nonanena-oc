#!/bin/bash

# Script de inicialização do container
echo "🚀 Iniciando Sistema de Gestão de Produtos..."

# Criar diretórios necessários
mkdir -p /var/www/html/data
mkdir -p /var/www/html/uploads

# Definir permissões
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod 777 /var/www/html/data

# Iniciar Apache
echo "✅ Sistema iniciado com sucesso!"
echo "📱 Acesse: http://localhost:8000"
echo "🐳 Container rodando..."

exec apache2-foreground
