<?php
// Carregar variáveis de ambiente
require_once 'load_env.php';

// Configurações do sistema
define('APP_NAME', env('APP_NAME', 'Sistema de Gestão de Produtos'));
define('APP_VERSION', env('APP_VERSION', '1.0.0'));

// Configurações de autenticação
define('LOGIN_USERNAME', env('LOGIN_USERNAME', 'admin'));
define('LOGIN_PASSWORD', env('LOGIN_PASSWORD', 'admin123'));

// Configurações de sessão
define('SESSION_TIMEOUT', env('SESSION_TIMEOUT', 3600)); // 1 hora em segundos

// Configurações do banco
define('DB_PATH', env('DB_PATH', 'data/produtos.db'));

// Configurações de segurança
define('MAX_LOGIN_ATTEMPTS', env('MAX_LOGIN_ATTEMPTS', 5));
define('LOCKOUT_TIME', env('LOCKOUT_TIME', 900)); // 15 minutos em segundos

// Configurações adicionais
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_URL', env('APP_URL', 'http://localhost'));
define('DEBUG', env('DEBUG', false));
define('LOG_LEVEL', env('LOG_LEVEL', 'info'));

// Função para verificar se usuário está logado
function isLoggedIn() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Verificar timeout da sessão
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        return false;
    }
    
    // Atualizar última atividade
    $_SESSION['last_activity'] = time();
    return true;
}

// Função para fazer logout
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Função para redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Função para verificar tentativas de login
function checkLoginAttempts() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $attempts_file = 'data/login_attempts.json';
    
    if (!file_exists($attempts_file)) {
        return true;
    }
    
    $attempts = json_decode(file_get_contents($attempts_file), true);
    
    if (!isset($attempts[$ip])) {
        return true;
    }
    
    $user_attempts = $attempts[$ip];
    
    // Verificar se ainda está bloqueado
    if ($user_attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
        $time_since_last = time() - $user_attempts['last_attempt'];
        if ($time_since_last < LOCKOUT_TIME) {
            return false;
        } else {
            // Resetar tentativas após o tempo de bloqueio
            unset($attempts[$ip]);
            file_put_contents($attempts_file, json_encode($attempts));
            return true;
        }
    }
    
    return true;
}

// Função para registrar tentativa de login
function recordLoginAttempt($success) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $attempts_file = 'data/login_attempts.json';
    
    if (!file_exists($attempts_file)) {
        $attempts = [];
    } else {
        $attempts = json_decode(file_get_contents($attempts_file), true);
    }
    
    if ($success) {
        // Login bem-sucedido, remover tentativas
        unset($attempts[$ip]);
    } else {
        // Login falhou, incrementar tentativas
        if (!isset($attempts[$ip])) {
            $attempts[$ip] = ['count' => 0, 'last_attempt' => 0];
        }
        
        $attempts[$ip]['count']++;
        $attempts[$ip]['last_attempt'] = time();
    }
    
    file_put_contents($attempts_file, json_encode($attempts));
}
?>
