<?php
/**
 * AskBot - Web Widget Entry Point
 * 
 * Punto de entrada para el widget web
 */

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config.php';

define('APP_NAME', $config['app']['name']);
define('APP_ENV', $config['app']['env']);
define('APP_DEBUG', $config['app']['debug']);

// Cargar funciones helpers
require_once __DIR__ . '/../core/helpers.php';

$path = $_SERVER['REQUEST_URI'] ?? '/';

if ($path === '/' || $path === '/index.php') {
    header('Content-Type: text/html; charset=utf-8');
    echo renderWidget($config);
} elseif ($path === '/api' || strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/../core/api.php';
} elseif ($path === '/admin' || strpos($path, '/admin') === 0) {
    header('Location: /admin/');
} else {
    http_response_code(404);
    echo 'Not Found';
}

function renderWidget($config) {
    $color = $config['canales']['web']['color'] ?? '#3b82f6';
    $company = $config['app']['name'] ?? 'AskBot';
    
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$company} - Asistente IA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chat-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .chat-header {
            background: {$color};
            color: white;
            padding: 20px;
            text-align: center;
        }
        .chat-header h1 { font-size: 1.2rem; }
        .chat-messages {
            height: 450px;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .message {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .message.bot {
            background: #f0f0f0;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .message.user {
            background: {$color};
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .chat-input {
            display: flex;
            padding: 16px;
            border-top: 1px solid #eee;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #eee;
            border-radius: 25px;
            outline: none;
        }
        .chat-input input:focus { border-color: {$color}; }
        .chat-input button {
            background: {$color};
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
        }
        .typing {
            display: none;
            padding: 12px 16px;
            background: #f0f0f0;
            border-radius: 16px;
            width: 50px;
        }
        .typing span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            margin: 2px;
            animation: typing 1.4s infinite;
        }
        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-6px); }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>🤖 {$company}</h1>
        </div>
        <div class="chat-messages" id="messages">
            <div class="message bot">
                ¡Hola! Soy el asistente de {$company}. 
                ¿En qué puedo ayudarte hoy?
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="input" placeholder="Escribe tu mensaje...">
            <button onclick="send()">Enviar</button>
        </div>
    </div>

    <script>
        const messages = document.getElementById('messages');
        const input = document.getElementById('input');
        const API_URL = '/api/chat';

        input.addEventListener('keypress', e => {
            if (e.key === 'Enter') send();
        });

        async function send() {
            const text = input.value.trim();
            if (!text) return;

            addMessage(text, 'user');
            input.value = '';
            
            showTyping();
            
            try {
                const resp = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        message: text,
                        canal: 'web'
                    })
                });
                const data = await resp.json();
                hideTyping();
                addMessage(data.response || 'Lo siento, hubo un error.', 'bot');
            } catch (e) {
                hideTyping();
                addMessage('Error de conexión', 'bot');
            }
        }

        function addMessage(text, sender) {
            const div = document.createElement('div');
            div.className = 'message ' + sender;
            div.textContent = text;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        function showTyping() {
            const div = document.createElement('div');
            div.className = 'typing';
            div.id = 'typing';
            div.innerHTML = '<span></span><span></span><span></span>';
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        function hideTyping() {
            const el = document.getElementById('typing');
            if (el) el.remove();
        }
    </script>
</body>
</html>
HTML;
}