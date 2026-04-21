<?php
/**
 * AskBot - Canales (Web, WhatsApp, Telegram)
 */

class Canales {
    private $db;
    private $empresa_id;

    public function __construct($empresa_id) {
        $this->db = new Database();
        $this->empresa_id = $empresa_id;
    }

    public function configurarCanal($canal, $config) {
        $webhook_url = $config['webhook_url'] ?? null;
        $webhook_secret = $config['webhook_secret'] ?? bin2hex(random_bytes(16));
        $bot_token = $config['bot_token'] ?? null;

        $config_json = json_encode($config);

        $this->db->query(
            "INSERT INTO ask_canales (empresa_id, canal, config, webhook_url, webhook_secret, bot_token)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE config = ?, webhook_url = ?, webhook_secret = ?, bot_token = ?",
            [
                $this->empresa_id, $canal, $config_json, $webhook_url, $webhook_secret, $bot_token,
                $config_json, $webhook_url, $webhook_secret, $bot_token
            ]
        );

        return [
            'canal' => $canal,
            'webhook_url' => $webhook_url,
            'webhook_secret' => $webhook_secret
        ];
    }

    public function obtenerCanal($canal) {
        $result = $this->db->query(
            "SELECT * FROM ask_canales WHERE empresa_id = ? AND canal = ?",
            [$this->empresa_id, $canal]
        );

        $canal = $result->fetch();

        if ($canal && $canal['config']) {
            $canal['config'] = json_decode($canal['config'], true);
        }

        return $canal;
    }

    public function listarCanales() {
        $result = $this->db->query(
            "SELECT canal, activo, webhook_url FROM ask_canales WHERE empresa_id = ?",
            [$this->empresa_id]
        );

        return $result->fetchAll();
    }

    public function activarCanal($canal, $activo = true) {
        $this->db->query(
            "UPDATE ask_canales SET activo = ? WHERE empresa_id = ? AND canal = ?",
            [$activo ? 1 : 0, $this->empresa_id, $canal]
        );

        return ['canal' => $canal, 'activo' => $activo];
    }

    public function verificarWebhook($canal, $secret) {
        $result = $this->db->query(
            "SELECT COUNT(*) as total FROM ask_canales 
             WHERE empresa_id = ? AND canal = ? AND webhook_secret = ? AND activo = 1",
            [$this->empresa_id, $canal, $secret]
        );

        $row = $result->fetch();
        return $row['total'] > 0;
    }
}

function configurarTelegramWebhook($bot_token, $webhook_url) {
    $url = "https://api.telegram.org/bot{$bot_token}/setWebhook";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'url' => $webhook_url
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function configurarWhatsAppWebhook($phone_id, $access_token, $webhook_url) {
    $url = "https://graph.facebook.com/v17.0/{$phone_id}/webhooks";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'webhook_url' => $webhook_url,
        'webhook_fields' => 'messages'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function verificarTelegramAuth($bot_token, $user_id) {
    $url = "https://api.telegram.org/bot{$bot_token}/getChat?chat_id={$user_id}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function obtenerUpdatesTelegram($bot_token, $offset = null) {
    $url = "https://api.telegram.org/bot{$bot_token}/getUpdates";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($offset) {
        $url .= "?offset={$offset}";
        curl_setopt($ch, CURLOPT_URL, $url);
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}