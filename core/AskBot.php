<?php
/**
 * AskBot Core - Motor del Bot con IA
 * 
 * Procesa mensajes, genera SQL dinámica, y gestiona conversaciones
 */

require_once 'Database.php';

class AskBot {
    private $db;
    private $empresa_id;
    private $config;
    private $tablas_permitidas = [];
    private $ia_provider;
    private $ia_model;

    public function __construct($empresa_id = 1, $ia_provider = 'ollama', $ia_model = 'llama3') {
        $this->db = new Database();
        $this->empresa_id = $empresa_id;
        $this->ia_provider = $ia_provider;
        $this->ia_model = $ia_model;
        $this->cargarConfig();
        $this->cargarTablasPermitidas();
    }

    private function cargarConfig() {
        $stmt = $this->db->query(
            "SELECT clave, valor FROM ask_config WHERE empresa_id = ?",
            [$this->empresa_id]
        );
        $this->config = [];
        while ($row = $stmt->fetch()) {
            $this->config[$row['clave']] = $row['valor'];
        }
    }

    private function cargarTablasPermitidas() {
        $stmt = $this->db->query(
            "SELECT * FROM ask_tablas_permitidas WHERE empresa_id = ? AND activa = 1",
            [$this->empresa_id]
        );
        $this->tablas_permitidas = [];
        while ($row = $stmt->fetch()) {
            $this->tablas_permitidas[$row['tabla_nombre']] = $row;
        }
    }

    public function procesarMensaje($mensaje, $session_id, $canal = 'web', $usuario = null) {
        $mensaje = trim($mensaje);
        if (empty($mensaje)) {
            return ['respuesta' => 'Por favor, ingresa un mensaje.'];
        }

        $this->guardarConversacion($mensaje, null, $session_id, $canal, $usuario);

        $intencion = $this->detectarIntencion($mensaje);
        
        switch ($intencion) {
            case 'consulta_datos':
                return $this->procesarConsultaDatos($mensaje);
            case 'escalamiento':
                return $this->procesarEscalamiento($mensaje);
            case 'charla':
                return $this->procesarCharla($mensaje);
            default:
                return $this->procesarCharla($mensaje);
        }
    }

    private function detectarIntencion($mensaje) {
        $mensaje_lower = mb_strtolower($mensaje, 'UTF-8');
        $palabras_consulta = ['cuántos', 'cuántas', 'cuánto', 'cuál', 'dame', 'muéstrame', 'lista', 'sacar', 'total', 'hay'];
        $palabras_escalar = ['hablar', ' humano', 'atención', 'gerente', 'jefe', 'no puedo', 'ayuda'];
        
        foreach ($palabras_consulta as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return 'consulta_datos';
            }
        }
        
        foreach ($palabras_escalar as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return 'escalamiento';
            }
        }
        
        return 'charla';
    }

    private function procesarConsultaDatos($mensaje) {
        $schema = $this->generarSchema();
        $prompt = $this->generarPromptConsulta($mensaje, $schema);
        
        $respuesta_ia = $this->llamarIA($prompt);
        
        if (isset($respuesta_ia['sql']) && !empty($respuesta_ia['sql'])) {
            $sql = $respuesta_ia['sql'];
            
            if (!$this->validarSQL($sql)) {
                return [
                    'respuesta' => 'No tengo permisos para realizar esa consulta.',
                    'tipo' => 'error'
                ];
            }
            
            try {
                $resultados = $this->db->query($sql)->fetchAll();
                $respuesta_texto = $this->formatearResultados($respuesta_ia['respuesta'], $resultados);
                
                $this->guardarConversacion($mensaje, $respuesta_texto, null, null, null, $sql);
                
                return [
                    'respuesta' => $respuesta_texto,
                    'tipo' => 'consulta',
                    'sql' => $sql
                ];
            } catch (Exception $e) {
                return [
                    'respuesta' => 'Hubo un error al ejecutar la consulta: ' . $e->getMessage(),
                    'tipo' => 'error'
                ];
            }
        }
        
        return [
            'respuesta' => $respuesta_ia['respuesta'] ?? 'No pude entender tu pregunta. ¿Podrías reformularla?',
            'tipo' => ' error'
        ];
    }

    private function generarSchema() {
        $schema = [];
        
        foreach ($this->tablas_permitidas as $tabla => $info) {
            $columnas = $this->db->query("DESCRIBE {$info['tabla_nombre']}")->fetchAll();
            
            $columns = [];
            foreach ($columnas as $col) {
                $columns[] = $col['Field'] . ' (' . $col['Type'] . ')';
            }
            
            $tabla_info = [
                'nombre' => $info['tabla_nombre'],
                'alias' => $info['tabla_alias'] ?: $info['tabla_nombre'],
                'descripcion' => $info['tabla_descripcion'],
                'columnas' => implode(', ', $columns)
            ];
            
            if ($info['columna_id']) $tabla_info['pk'] = $info['columna_id'];
            if ($info['columna_nombre']) $tabla_info['nombre_col'] = $info['columna_nombre'];
            if ($info['columna_email']) $tabla_info['email_col'] = $info['columna_email'];
            
            $schema[] = $tabla_info;
        }
        
        return $schema;
    }

    private function generarPromptConsulta($mensaje, $schema) {
        $schema_json = json_encode($schema, JSON_PRETTY_PRINT);
        
        return <<<PROMPT
Eres un asistente que genera consultas SQL para una base de datos MySQL.
Tu tarea es entender la pregunta del usuario y generar una consulta SQL válida.

Pregunta del usuario: {$mensaje}

Schema de la base de datos:
{$schema_json}

INSTRUCCIONES:
1. Analiza la pregunta del usuario
2. Genera una consulta SQL válida para responder
3. Solo puedes usar SELECT (nunca INSERT, UPDATE, DELETE, DROP, etc.)
4. Usa los alias de las tablas para referenciarlas
5. Limita los resultados a los primeros 50 si no se especifica número

Responde en JSON con este formato:
{
    "sql": "SELECT ... FROM ... WHERE ...",
    "respuesta": "Texto explicativo de los resultados"
}

Si no puedes generar una consulta válida, responde:
{
    "sql": null,
    "respuesta": "No puedo responder esa pregunta con los datos disponibles."
}
PROMPT;
    }

    private function validarSQL($sql) {
        $sql_upper = strtoupper(trim($sql));
        
        $operadoresPeligrosos = ['INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE', 'ALTER', 'CREATE', 'GRANT', 'REVOKE'];
        foreach ($operadoresPeligrosos as $op) {
            if (strpos($sql_upper, $op) === 0) {
                return false;
            }
        }
        
        if (preg_match('/;\s*[a-z]+/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }
        
        return true;
    }

    private function formatearResultados($mensaje, $resultados) {
        if (empty($resultados)) {
            return $mensaje . " No encontré registros.";
        }
        
        $total = count($resultados);
        $mensaje .= " Encontré {$total} resultado" . ($total > 1 ? 's' : '') . ".\n\n";
        
        if ($total <= 5) {
            foreach ($resultados as $i => $row) {
                $mensaje .= ($i + 1) . ". " . $this->formatearFila($row) . "\n";
            }
        } else {
            $mensaje .= "Mostrando los primeros 5:\n";
            for ($i = 0; $i < 5; $i++) {
                $mensaje .= ($i + 1) . ". " . $this->formatearFila($resultados[$i]) . "\n";
            }
            $mensaje .= "\n* Hay " . ($total - 5) . " más. Solicita más detalles.";
        }
        
        return $mensaje;
    }

    private function formatearFila($row) {
        $values = [];
        foreach ($row as $key => $value) {
            if (!is_numeric($key)) {
                $values[] = "{$key}: {$value}";
            }
        }
        return implode(', ', $values);
    }

    private function procesarEscalamiento($mensaje) {
        $sql = "INSERT INTO ask_escalamientos 
               (empresa_id, pregunta, estado) 
               VALUES (?, ?, 'pendiente')";
        
        $this->db->query($sql, [$this->empresa_id, $mensaje]);
        
        $this->actualizarConversacion(null, $mensaje, true);
        
        $notificar = $this->config['notificar_escalamiento'] ?? true;
        
        return [
            'respuesta' => 'Tu solicitud ha sido transferida a nuestro equipo. Te contactaremos pronto. ¿Hay algo más en lo que pueda ayudarte?',
            'tipo' => 'escalamiento',
            'ticket' => true
        ];
    }

    private function procesarCharla($mensaje) {
        $prompt = <<<PROMPT
Eres un asistente amigable de una empresa. 
Responde de manera breve y servicial.

Mensaje del usuario: {$mensaje}
PROMPT;

        $respuesta = $this->llamarIA($prompt);
        
        return [
            'respuesta' => $respuesta['respuesta'] ?? 'Entiendo. ¿En qué más puedo ayudarte?',
            'tipo' => 'charla'
        ];
    }

    private function llamarIA($prompt) {
        switch ($this->ia_provider) {
            case 'ollama':
                return $this->llamarOllama($prompt);
            case 'openrouter':
                return $this->llamarOpenRouter($prompt);
            case 'anthropic':
                return $this->llamarAnthropic($prompt);
            default:
                return ['respuesta' => 'IA no configurada.'];
        }
    }

    private function llamarOllama($prompt) {
        $endpoint = $this->config['ollama_endpoint'] ?? 'http://localhost:11434';
        $model = $this->ia_model;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$endpoint}/api/generate");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $model,
            'prompt' => $prompt,
            'stream' => false
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['response'])) {
            return $this->procesarRespuestaIA($data['response']);
        }
        
        return ['respuesta' => 'Error de conexión con IA local.'];
    }

    private function llamarOpenRouter($prompt) {
        $api_key = getenv('OPENROUTER_API_KEY') ?: ($this->config['openrouter_api_key'] ?? '');
        $model = $this->ia_model;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://openrouter.ai/api/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer {$api_key}"
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $this->procesarRespuestaIA($data['choices'][0]['message']['content']);
        }
        
        return ['respuesta' => 'Error de API.'];
    }

    private function llamarAnthropic($prompt) {
        $api_key = getenv('ANTHROPIC_API_KEY') ?: ($this->config['anthropic_api_key'] ?? '');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.anthropic.com/v1/messages');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => $this->ia_model,
            'max_tokens' => 1024,
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if (isset($data['content'][0]['text'])) {
            return $this->procesarRespuestaIA($data['content'][0]['text']);
        }
        
        return ['respuesta' => 'Error de API.'];
    }

    private function procesarRespuestaIA($texto) {
        if (preg_match('/\{.*\}/s', $texto, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['sql'])) {
                return $json;
            }
        }
        
        return ['respuesta' => $texto];
    }

    private function guardarConversacion($mensaje, $respuesta, $session_id, $canal, $usuario = null, $sql = null) {
        $sql_insert = "INSERT INTO ask_conversaciones 
                     (empresa_id, session_id, canal, mensaje, respuesta, consulta_sql) 
                     VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql_insert, [
            $this->empresa_id,
            $session_id,
            $canal,
            $mensaje,
            $respuesta,
            $sql
        ]);
        
        $sql_update = "INSERT INTO ask_sessions (session_id, empresa_id, canal, usuario_nombre, ultimo_login, mensajes_count) 
                    VALUES (?, ?, ?, ?, NOW(), 1)
                    ON DUPLICATE KEY UPDATE ultimo_login = NOW(), mensajes_count = mensajes_count + 1";
        
        $this->db->query($sql_update, [
            $session_id,
            $this->empresa_id,
            $canal,
            $usuario['nombre'] ?? null
        ]);
    }

    private function actualizarConversacion($mensaje, $respuesta, $escalated = false) {
        $sql = "UPDATE ask_sessions 
               SET estado = 'escalada' 
               WHERE empresa_id = ? AND estado = 'activa' 
               ORDER BY created_at DESC LIMIT 1";
        
        $this->db->query($sql, [$this->empresa_id]);
    }

    public function obtenerEstadisticas($fecha_desde = null, $fecha_hasta = null) {
        $sql = "SELECT 
                    DATE(created_at) as fecha,
                    COUNT(*) as mensajes,
                    COUNT(DISTINCT session_id) as conversaciones,
                    SUM(escalated) as escalamientos
                FROM ask_conversaciones 
                WHERE empresa_id = ?";
        
        $params = [$this->empresa_id];
        
        if ($fecha_desde) {
            $sql .= " AND created_at >= ?";
            $params[] = $fecha_desde;
        }
        
        if ($fecha_hasta) {
            $sql .= " AND created_at <= ?";
            $params[] = $fecha_hasta;
        }
        
        $sql .= " GROUP BY DATE(created_at) ORDER BY fecha DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    public function obtenerEscalamientos($estado = null) {
        $sql = "SELECT * FROM ask_escalamientos WHERE empresa_id = ?";
        $params = [$this->empresa_id];
        
        if ($estado) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->query($sql, $params)->fetchAll();
    }
}