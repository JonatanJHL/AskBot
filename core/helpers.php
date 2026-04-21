<?php
/**
 * AskBot - Helper Functions
 * 
 * Funciones utilitarias para el sistema
 */

if (!function_exists('config')) {
    /**
     * Obtiene un valor de configuración
     */
    function config(string $key, $default = null) {
        static $config = null;
        
        if ($config === null) {
            $configFile = dirname(__DIR__) . '/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
            } else {
                $config = [];
            }
        }
        
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
}

if (!function_exists('db')) {
    /**
     * Obtiene una instancia de la base de datos
     */
    function db(): Database {
        static $db = null;
        
        if ($db === null) {
            $dbConfig = config('database');
            $db = new Database($dbConfig);
        }
        
        return $db;
    }
}

if (!function_exists('env')) {
    /**
     * Obtiene una variable de entorno
     */
    function env(string $key, $default = null) {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        return $value;
    }
}

if (!function_exists('response')) {
    /**
     * Retorna una respuesta JSON
     */
    function response(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('abort')) {
    /**
     * Genera un error HTTP
     */
    function abort(int $code, string $message = ''): void {
        http_response_code($code);
        
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error'
        ];
        
        $message = $message ?: ($messages[$code] ?? 'Error');
        echo $message;
        exit;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirecciona a otra URL
     */
    function redirect(string $url, int $code = 302): void {
        header("Location: {$url}", true, $code);
        exit;
    }
}

if (!function_exists('logger')) {
    /**
     * Registra un mensaje en el log
     */
    function logger(string $message, string $level = 'info'): void {
        $logPath = config('log.path', dirname(__DIR__) . '/storage/logs');
        $logFile = $logPath . '/' . date('Y-m-d') . '.log';
        
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

if (!function_exists('cache')) {
    /**
     * Cache simple en archivo
     */
    function cache(string $key, $value = null, int $ttl = 3600) {
        $cachePath = config('cache.path', dirname(__DIR__) . '/storage/cache');
        $cacheFile = $cachePath . '/' . md5($key) . '.cache';
        
        if ($value === null) {
            // Get
            if (!file_exists($cacheFile)) {
                return null;
            }
            
            $data = unserialize(file_get_contents($cacheFile));
            
            if ($data['expires'] < time()) {
                unlink($cacheFile);
                return null;
            }
            
            return $data['value'];
        }
        
        // Set
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }
        
        file_put_contents($cacheFile, serialize($data));
        
        return $value;
    }
}

if (!function_exists(' sanitize')) {
    /**
     * Sanitiza entrada del usuario
     */
    function sanitize(string $value): string {
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        return $value;
    }
}

if (!function_exists('generateToken')) {
    /**
     * Genera un token aleatorio
     */
    function generateToken(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
}

if (!function_exists('validateEmail')) {
    /**
     * Valida un email
     */
    function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('formatDate')) {
    /**
     * Formatea una fecha
     */
    function formatDate(string $date, string $format = 'd/m/Y H:i'): string {
        return date($format, strtotime($date));
    }
}

if (!function_exists('timeAgo')) {
    /**
     * Retorna tiempo relativo ("hace 5 minutos")
     */
    function timeAgo(string $date): string {
        $timestamp = strtotime($date);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'hace un momento';
        }
        
        $diff = round($diff / 60);
        
        if ($diff < 60) {
            return "hace {$diff} minuto" . ($diff > 1 ? 's' : '');
        }
        
        $diff = round($diff / 60);
        
        if ($diff < 24) {
            return "hace {$diff} hora" . ($diff > 1 ? 's' : '');
        }
        
        $diff = round($diff / 24);
        
        if ($diff < 30) {
            return "hace {$diff} día" . ($diff > 1 ? 's' : '');
        }
        
        return date('d/m/Y', $timestamp);
    }
}