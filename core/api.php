<?php
/**
 * AskBot API - REST API
 * Open Source - Sin licencias
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/AskBot.php';
require_once __DIR__ . '/Database.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$path = str_replace('/api/', '', $path);

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function getDb() {
    static $db = null;
    if ($db === null) {
        $config = require dirname(__DIR__) . '/config.php';
        $dbConfig = $config['database'] ?? [];
        $db = new Database($dbConfig);
    }
    return $db;
}

function autenticar() {
    return ['id' => 1, 'empresa_nombre' => 'Mi Empresa'];
}

function crearSession() {
    return strtoupper(bin2hex(random_bytes(16)));
}

$parts = explode('/', $path);
$recurso = $parts[0] ?? '';

switch ($recurso) {
    case 'chat':
        if ($method !== 'POST') {
            jsonResponse(['error' => 'Método no permitido'], 405);
        }

        $empresa = autenticar();
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['message'])) {
            jsonResponse(['error' => 'Mensaje requerido'], 400);
        }

        $session_id = $data['session_id'] ?? crearSession();
        $canal = $data['canal'] ?? 'api';
        $usuario = $data['usuario'] ?? null;

        $bot = new AskBot(1);
        $respuesta = $bot->procesarMensaje($data['message'], $session_id, $canal, $usuario);

        jsonResponse([
            'session_id' => $session_id,
            'response' => $respuesta['respuesta'],
            'tipo' => $respuesta['tipo'] ?? 'chat',
            'timestamp' => date('c')
        ]);
        break;

    case 'conversaciones':
        $db = getDb();
        $resultado = $db->query(
            "SELECT * FROM ask_conversaciones ORDER BY created_at DESC LIMIT 100"
        );
        jsonResponse($resultado->fetchAll());
        break;

    case 'tickets':
        $db = getDb();
        $resultado = $db->query(
            "SELECT * FROM ask_tickets ORDER BY created_at DESC"
        );
        jsonResponse($resultado->fetchAll());
        break;

    case 'tablas':
        $db = getDb();
        $resultado = $db->query("SELECT * FROM ask_tablas_permitidas WHERE activa = 1");
        jsonResponse($resultado->fetchAll());
        break;

    case 'datos-tabla':
        $tabla = $_GET['tabla'] ?? '';
        if (!$tabla) {
            jsonResponse(['error' => 'Tabla requerida'], 400);
        }

        $db = getDb();
        $resultado = $db->query("SELECT * FROM {$tabla} LIMIT 50");
        jsonResponse($resultado->fetchAll());
        break;

    case 'stats':
        $db = getDb();
        $resultado = $db->query(
            "SELECT 
                DATE(created_at) as fecha,
                COUNT(DISTINCT session_id) as conversaciones,
                COUNT(*) as mensajes,
                SUM(CASE WHEN escalated = 1 THEN 1 ELSE 0 END) as escalamientos
             FROM ask_conversaciones
             GROUP BY DATE(created_at)
             ORDER BY fecha DESC
             LIMIT 30"
        );
        jsonResponse($resultado->fetchAll());
        break;

    case 'config':
        $db = getDb();
        switch ($method) {
            case 'GET':
                $resultado = $db->query("SELECT clave, valor FROM ask_config");
                $config = [];
                while ($row = $resultado->fetch()) {
                    $config[$row['clave']] = $row['valor'];
                }
                jsonResponse($config);
                break;
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $db->query(
                    "INSERT INTO ask_config (clave, valor) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE valor = ?",
                    [$data['clave'], $data['valor'], $data['valor']]
                );
                jsonResponse(['ok' => true]);
                break;
        }
        break;

    case 'empresa':
        $db = getDb();
        $resultado = $db->query("SELECT * FROM ask_empresa LIMIT 1");
        jsonResponse($resultado->fetch());
        break;

    default:
        jsonResponse([
            'api' => 'AskBot',
            'version' => '1.0',
            'open_source' => true,
            'endpoints' => [
                'POST /api/chat' => 'Enviar mensaje',
                'GET /api/conversaciones' => 'Ver conversaciones',
                'GET /api/tickets' => 'Ver tickets',
                'GET /api/tablas' => 'Ver tablas permitidas',
                'GET /api/datos-tabla?tabla=nombre' => 'Ver datos de tabla',
                'GET /api/stats' => 'Estadísticas',
                'GET /api/config' => 'Ver configuración',
                'POST /api/config' => 'Guardar configuración',
                'GET /api/empresa' => 'Datos de empresa'
            ]
        ]);
}