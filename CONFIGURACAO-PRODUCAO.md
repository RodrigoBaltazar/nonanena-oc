# 🔐 Configuração de Produção - Sistema de Gestão de Produtos

## 🚨 **IMPORTANTE: Repositório Público**

Este repositório é **público no GitHub**. As credenciais sensíveis devem ser configuradas via arquivo `.env` que **NÃO é commitado**.

## 📋 **Sistema de Configuração**

### **Arquivos de Configuração:**
- `env.example` - Template com configurações padrão
- `.env` - Arquivo de configuração real (NÃO commitado)
- `config.php` - Carrega variáveis do .env
- `load_env.php` - Função para carregar .env

## 🚀 **Configuração Rápida na VPS**

### **Método 1: Deploy Docker Completo (Recomendado)**
```bash
# Na VPS
git clone https://github.com/SEU_USUARIO/sistema-gestao-produtos.git
cd sistema-gestao-produtos

# Deploy completo com Docker
chmod +x docker-deploy.sh
./docker-deploy.sh
```

### **Método 2: Configuração Manual + Docker**
```bash
# 1. Copiar template
cp env.example .env

# 2. Editar credenciais
nano .env

# 3. Iniciar Docker
docker-compose up -d
```

### **Método 3: Script de Configuração + Docker**
```bash
# 1. Configurar credenciais
sudo chmod +x setup-producao.sh
sudo ./setup-producao.sh

# 2. Iniciar Docker
docker-compose up -d
```

## ⚙️ **Configurações Disponíveis**

### **Credenciais de Acesso:**
```env
LOGIN_USERNAME=seu_usuario
LOGIN_PASSWORD=sua_senha_forte
```

### **Configurações de Sessão:**
```env
SESSION_TIMEOUT=3600  # 1 hora em segundos
```

### **Configurações de Segurança:**
```env
MAX_LOGIN_ATTEMPTS=5    # Tentativas máximas por IP
LOCKOUT_TIME=900        # Bloqueio por 15 minutos
```

### **Configurações da Aplicação:**
```env
APP_NAME=Sistema de Gestão de Produtos
APP_URL=http://oc.nonanena.com.br
APP_ENV=production
```

## 🔧 **Exemplo de .env para Produção**

```env
# Configurações do Sistema de Gestão de Produtos
# Gerado para produção

# ===========================================
# CONFIGURAÇÕES DE AUTENTICAÇÃO
# ===========================================
LOGIN_USERNAME=admin_producao
LOGIN_PASSWORD=MinhaSenh@123!Forte

# ===========================================
# CONFIGURAÇÕES DE SESSÃO
# ===========================================
SESSION_TIMEOUT=7200

# ===========================================
# CONFIGURAÇÕES DE SEGURANÇA
# ===========================================
MAX_LOGIN_ATTEMPTS=3
LOCKOUT_TIME=1800

# ===========================================
# CONFIGURAÇÕES DO BANCO DE DADOS
# ===========================================
DB_PATH=data/produtos.db

# ===========================================
# CONFIGURAÇÕES DA APLICAÇÃO
# ===========================================
APP_NAME=Sistema de Gestão de Produtos - Nonanena
APP_VERSION=1.0.0

# ===========================================
# CONFIGURAÇÕES DE PRODUÇÃO
# ===========================================
APP_ENV=production
APP_URL=https://oc.nonanena.com.br

# ===========================================
# CONFIGURAÇÕES DE LOG
# ===========================================
DEBUG=false
LOG_LEVEL=info
```

## 🛡️ **Segurança**

### **Permissões do Arquivo .env:**
```bash
# Arquivo .env deve ter permissões restritivas
chmod 600 .env
chown www-data:www-data .env
```

### **Verificar se .env está protegido:**
```bash
# Verificar permissões
ls -la .env
# Deve mostrar: -rw------- 1 www-data www-data

# Verificar se não está no Git
git status
# .env não deve aparecer na lista
```

## 🐳 **Docker e Sistema .env**

### **Como o Docker funciona com .env:**
- O arquivo `.env` é montado como volume no container
- Permissões são mantidas (600) para segurança
- Container acessa as configurações via `load_env.php`
- Não há interferência no funcionamento do Docker

### **Volume do .env no Docker:**
```yaml
volumes:
  - ./.env:/var/www/html/.env:ro  # Somente leitura
```

## 🔄 **Fluxo de Deploy**

### **1. Desenvolvimento Local:**
- Use `env.example` como base
- Crie `.env` local com suas credenciais
- `.env` é ignorado pelo Git

### **2. Deploy na VPS:**
```bash
# 1. Clonar repositório
git clone https://github.com/SEU_USUARIO/sistema-gestao-produtos.git
cd sistema-gestao-produtos

# 2. Deploy completo com Docker
chmod +x docker-deploy.sh
./docker-deploy.sh

# 3. Configurar Nginx (opcional)
sudo cp oc.nonanena.com.br-http.conf /etc/nginx/sites-available/oc.nonanena.com.br
sudo ln -s /etc/nginx/sites-available/oc.nonanena.com.br /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### **3. Atualizações:**
```bash
# 1. Atualizar código
git pull

# 2. Reconstruir container
docker-compose up --build -d

# 3. Recarregar Nginx (se configurado)
sudo systemctl reload nginx
```

## 🧪 **Teste de Configuração**

### **Verificar se .env está sendo carregado:**
```bash
# Acessar aplicação
curl -I http://seu-ip:8080

# Deve redirecionar para login
# Login com credenciais do .env
```

### **Verificar credenciais:**
```bash
# Verificar se arquivo existe
ls -la .env

# Verificar permissões
stat .env

# Testar login no navegador
```

## 🚨 **Troubleshooting**

### **Problema: Credenciais não funcionam**
```bash
# Verificar se .env existe
ls -la .env

# Verificar permissões
chmod 600 .env

# Verificar conteúdo (sem mostrar senha)
grep LOGIN_USERNAME .env
```

### **Problema: Arquivo .env não é carregado**
```bash
# Verificar se load_env.php existe
ls -la load_env.php

# Verificar se config.php está correto
head -5 config.php
```

### **Problema: Permissões incorretas**
```bash
# Corrigir permissões
chmod 600 .env
chown www-data:www-data .env
chmod 755 data/
chown www-data:www-data data/
```

## 📝 **Checklist de Produção**

- [ ] Arquivo `.env` criado com credenciais seguras
- [ ] Permissões do `.env` definidas como 600
- [ ] Credenciais padrão alteradas
- [ ] Senha forte configurada
- [ ] Timeout de sessão adequado
- [ ] URL da aplicação configurada
- [ ] Container Docker rodando
- [ ] Nginx configurado
- [ ] Teste de login funcionando
- [ ] Backup do diretório `data/` configurado

## 🔐 **Recomendações de Segurança**

1. **Use senhas fortes** (mínimo 12 caracteres)
2. **Altere credenciais padrão** imediatamente
3. **Monitore tentativas de login** suspeitas
4. **Faça backup regular** do diretório `data/`
5. **Use HTTPS** em produção
6. **Mantenha o sistema atualizado**
7. **Nunca commite** o arquivo `.env`

## 📞 **Suporte**

Se encontrar problemas:
1. Verifique os logs: `docker-compose logs -f`
2. Verifique permissões: `ls -la .env`
3. Teste configuração: `php -r "require 'config.php'; echo LOGIN_USERNAME;"`
4. Consulte a documentação de segurança: `SEGURANCA.md`
