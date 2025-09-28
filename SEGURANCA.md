# 🔐 Sistema de Segurança

## 🚨 **IMPORTANTE: Altere as Credenciais Padrão!**

### Credenciais Padrão:
- **Usuário:** `admin`
- **Senha:** `admin123`

### Como Alterar:

1. **Edite o arquivo `config.php`:**
```php
// Linha 6-7
define('LOGIN_USERNAME', 'seu_usuario');
define('LOGIN_PASSWORD', 'sua_senha_forte');
```

2. **Recomendações de Senha:**
   - Mínimo 8 caracteres
   - Use letras, números e símbolos
   - Exemplo: `MinhaSenh@123!`

## 🛡️ **Recursos de Segurança Implementados:**

### 1. **Autenticação por Sessão**
- Login obrigatório para acessar o sistema
- Sessão expira em 1 hora de inatividade
- Logout automático por timeout

### 2. **Proteção contra Ataques de Força Bruta**
- Máximo 5 tentativas de login por IP
- Bloqueio de 15 minutos após tentativas falhadas
- Registro de tentativas de login

### 3. **Proteção de Arquivos Sensíveis**
- Arquivo `config.php` não acessível via web
- Banco de dados protegido
- Diretório `data/` protegido

### 4. **Headers de Segurança**
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin

### 5. **Proteção .htaccess**
- Bloqueio de acesso a arquivos sensíveis
- Redirecionamento para login
- Cache otimizado
- Compressão GZIP

## 🔧 **Configurações Avançadas**

### Alterar Timeout da Sessão:
```php
// Em config.php, linha 10
define('SESSION_TIMEOUT', 7200); // 2 horas
```

### Alterar Limite de Tentativas:
```php
// Em config.php, linhas 13-14
define('MAX_LOGIN_ATTEMPTS', 3); // 3 tentativas
define('LOCKOUT_TIME', 1800); // 30 minutos
```

## 📱 **Funcionamento do Login**

### 1. **Acesso Inicial:**
- Usuário acessa qualquer URL do sistema
- É redirecionado para `login.php`
- Deve inserir credenciais válidas

### 2. **Após Login:**
- Sessão é criada
- Usuário pode acessar todas as funcionalidades
- Nome do usuário aparece no cabeçalho
- Botão "Sair" disponível

### 3. **Timeout de Sessão:**
- Sessão expira após 1 hora de inatividade
- Usuário é redirecionado para login
- Dados não são perdidos

## 🚨 **Monitoramento de Segurança**

### Verificar Tentativas de Login:
```bash
# Ver arquivo de tentativas
cat data/login_attempts.json
```

### Limpar Tentativas Bloqueadas:
```bash
# Remover arquivo para resetar bloqueios
rm data/login_attempts.json
```

### Verificar Logs do Servidor:
```bash
# Logs do Apache/Nginx
tail -f /var/log/apache2/access.log
tail -f /var/log/nginx/access.log
```

## 🔄 **Fluxo de Autenticação**

```
1. Usuário acessa sistema
   ↓
2. Sistema verifica se está logado
   ↓
3. Se não estiver → Redireciona para login.php
   ↓
4. Usuário insere credenciais
   ↓
5. Sistema valida credenciais
   ↓
6. Se válidas → Cria sessão e redireciona
   ↓
7. Se inválidas → Mostra erro e conta tentativas
   ↓
8. Após 5 tentativas → Bloqueia IP por 15 min
```

## ⚠️ **Recomendações de Segurança**

1. **Altere as credenciais padrão imediatamente**
2. **Use senhas fortes e únicas**
3. **Monitore tentativas de login suspeitas**
4. **Mantenha o sistema atualizado**
5. **Faça backups regulares do banco de dados**
6. **Use HTTPS em produção**

## 🆘 **Em Caso de Problemas**

### Esqueceu a Senha:
1. Edite `config.php`
2. Altere `LOGIN_PASSWORD`
3. Salve o arquivo

### Usuário Bloqueado:
1. Acesse a VPS
2. Execute: `rm data/login_attempts.json`
3. Tente fazer login novamente

### Sessão Não Funciona:
1. Verifique se `data/` tem permissão de escrita
2. Verifique se PHP sessions estão habilitadas
3. Limpe cache do navegador
