# 🚀 Deploy para oc.nonanena.com.br

## 📋 Pré-requisitos na VPS

1. **Docker e Docker Compose instalados**
2. **Nginx instalado e rodando**
3. **Domínio `oc.nonanena.com.br` apontando para o IP da VPS**
4. **Porta 80 e 443 liberadas no firewall**

## 🔧 Configuração Passo a Passo

### 1. **Clonar o Repositório**
```bash
git clone https://github.com/SEU_USUARIO/sistema-gestao-produtos.git
cd sistema-gestao-produtos
```

### 2. **Executar Deploy Automático**
```bash
sudo chmod +x deploy.sh
sudo ./deploy.sh
```

### 3. **Ou Configuração Manual**

#### A) Iniciar Container
```bash
docker-compose up -d
```

#### B) Configurar Nginx
```bash
# Para HTTP (testes)
sudo cp oc.nonanena.com.br-http.conf /etc/nginx/sites-available/oc.nonanena.com.br

# Para HTTPS (produção)
sudo cp oc.nonanena.com.br.conf /etc/nginx/sites-available/oc.nonanena.com.br

# Ativar site
sudo ln -s /etc/nginx/sites-available/oc.nonanena.com.br /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Recarregar Nginx
sudo systemctl reload nginx
```

## 🔒 Configuração SSL (HTTPS)

### Opção 1: Let's Encrypt (Recomendado)
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Gerar certificado
sudo certbot --nginx -d oc.nonanena.com.br

# Testar renovação automática
sudo certbot renew --dry-run
```

### Opção 2: Certificado Próprio
1. Coloque os certificados em:
   - `/etc/letsencrypt/live/oc.nonanena.com.br/fullchain.pem`
   - `/etc/letsencrypt/live/oc.nonanena.com.br/privkey.pem`

## 🌐 Verificação

### Testar Aplicativo
```bash
# Verificar container
docker ps

# Ver logs
docker-compose logs -f

# Testar acesso
curl -I http://oc.nonanena.com.br
```

### Testar PWA
1. Acesse `http://oc.nonanena.com.br` no celular
2. Deve aparecer opção "Instalar App"
3. Teste geração de PDF

## 🔧 Comandos de Manutenção

### Atualizar Aplicativo
```bash
git pull
docker-compose up --build -d
sudo systemctl reload nginx
```

### Ver Logs
```bash
# Logs do container
docker-compose logs -f

# Logs do Nginx
sudo tail -f /var/log/nginx/oc.nonanena.com.br.access.log
sudo tail -f /var/log/nginx/oc.nonanena.com.br.error.log
```

### Parar Aplicativo
```bash
docker-compose down
```

## 🚨 Troubleshooting

### Container não inicia
```bash
# Ver logs detalhados
docker-compose logs

# Verificar portas
netstat -tlnp | grep :8080
```

### Nginx não funciona
```bash
# Testar configuração
sudo nginx -t

# Ver logs de erro
sudo tail -f /var/log/nginx/error.log
```

### PWA não instala
- Verificar se `manifest.json` está acessível
- Verificar se `sw.js` está acessível
- Testar em navegador que suporta PWA (Chrome, Edge)

## 📱 URLs Importantes

- **Aplicativo:** http://oc.nonanena.com.br
- **Manifest PWA:** http://oc.nonanena.com.br/manifest.json
- **Service Worker:** http://oc.nonanena.com.br/sw.js
- **Geração PDF:** http://oc.nonanena.com.br/gerar_pdf.php
