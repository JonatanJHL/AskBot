<?php
/**
 * AskBot Configuration Example
 * 
 * Copia este archivo a config.php y configura tus valores
 */

return [
    // ============================================
    // BASE DE DATOS
    // ============================================
    'database' => [
        'host'     => getenv('DB_HOST') ?: 'localhost',
        'port'     => getenv('DB_PORT') ?: 3306,
        'user'     => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'name'     => getenv('DB_NAME') ?: 'askbot',
        'charset'  => 'utf8mb4'
    ],

    // ============================================
    // INTELIGENCIA ARTIFICIAL
    // ============================================
    'ia' => [
        'provider' => getenv('IA_PROVIDER') ?: 'ollama',  // ollama, openrouter, anthropic
        'model'    => getenv('IA_MODEL') ?: 'llama3',
        
        // Ollama (local)
        'ollama' => [
            'endpoint' => getenv('OLLAMA_ENDPOINT') ?: 'http://localhost:11434'
        ],
        
        // OpenRouter (cloud)
        'openrouter' => [
            'api_key' => getenv('OPENROUTER_API_KEY') ?: ''
        ],
        
        // Anthropic (cloud)
        'anthropic' => [
            'api_key' => getenv('ANTHROPIC_API_KEY') ?: ''
        ]
    ],

    // ============================================
    // SEGURIDAD
    // ============================================
    'security' => [
        'license_required' => getenv('LICENSE_REQUIRED') === 'true',
        'allowed_ips'      => explode(',', getenv('ALLOWED_IPS') ?: ''),
        'max_requests_min' => getenv('MAX_REQUESTS_MIN') ?: 60,
        'session_lifetime' => getenv('SESSION_LIFETIME') ?: 3600
    ],

    // ============================================
    // CANALES
    // ============================================
    'canales' => [
        'web' => [
            'enabled' => true,
            'url'     => getenv('WEB_URL') ?: 'https://tu-dominio.com'
        ],
        
        'telegram' => [
            'enabled'   => getenv('TELEGRAM_ENABLED') === 'true',
            'bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: ''
        ],
        
        'whatsapp' => [
            'enabled'   => getenv('WHATSAPP_ENABLED') === 'true',
            'token'     => getenv('WHATSAPP_TOKEN') ?: '',
            'phone_id'  => getenv('WHATSAPP_PHONE_ID') ?: ''
        ]
    ],

    // ============================================
    // EMAIL (SMTP)
    // ============================================
    'mail' => [
        'enabled'  => getenv('MAIL_ENABLED') === 'true',
        'driver'  => getenv('MAIL_DRIVER') ?: 'smtp',
        'host'    => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port'    => getenv('MAIL_PORT') ?: 587,
        'user'    => getenv('MAIL_USER') ?: '',
        'pass'    => getenv('MAIL_PASS') ?: '',
        'from'    => getenv('MAIL_FROM') ?: 'noreply@askbot.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'AskBot'
    ],

    // ============================================
    // APLICACIÓN
    // ============================================
    'app' => [
        'name'    => getenv('APP_NAME') ?: 'AskBot',
        'env'     => getenv('APP_ENV') ?: 'local',
        'debug'   => getenv('APP_DEBUG') === 'true',
        'url'     => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => getenv('APP_TIMEZONE') ?: 'America/Mexico_City'
    ],

    // ============================================
    // CACHE
    // ============================================
    'cache' => [
        'enabled' => getenv('CACHE_ENABLED') === 'true',
        'driver'  => getenv('CACHE_DRIVER') ?: 'file',  // file, redis, memcached
        'prefix'  => 'askbot_',
        
        'redis' => [
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'port' => getenv('REDIS_PORT') ?: 6379,
            'pass' => getenv('REDIS_PASS') ?: ''
        ]
    ],

    // ============================================
    // LOGS
    // ============================================
    'log' => [
        'enabled'   => true,
        'path'      => __DIR__ . '/storage/logs',
        'max_files' => 30
    ]
];
