<?php
/**
 * Carregador de variáveis de ambiente (.env)
 * Carrega variáveis do arquivo .env para o sistema
 */

function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Pular comentários
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Verificar se linha contém =
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            
            $name = trim($name);
            $value = trim($value);
            
            // Remover aspas se existirem
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            
            // Definir variável se não existir
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    
    return true;
}

/**
 * Obter variável de ambiente com valor padrão
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Converter strings booleanas
    if (in_array(strtolower($value), ['true', 'false'])) {
        return strtolower($value) === 'true';
    }
    
    // Converter números
    if (is_numeric($value)) {
        return strpos($value, '.') !== false ? (float) $value : (int) $value;
    }
    
    return $value;
}

// Carregar arquivo .env se existir
loadEnv(__DIR__ . '/.env');
?>
