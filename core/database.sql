-- AskBot Database Schema
-- Open Source v1.0
-- Sin licencias, 100% libre

-- ═══════════════════════════════════════════════════════════════════
-- EMPRESA (Tu instalación)
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_empresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    dominio VARCHAR(255),
    idioma VARCHAR(10) DEFAULT 'es',
    timezone VARCHAR(50) DEFAULT 'America/Mexico_City',
    config JSON,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- TABLAS PERMITIDAS
-- Configura qué tablas de tu BD puede consultar el bot
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_tablas_permitidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tabla_nombre VARCHAR(64) NOT NULL,
    tabla_alias VARCHAR(64),
    tabla_descripcion TEXT,
    columna_id VARCHAR(64),
    columna_nombre VARCHAR(64),
    columna_email VARCHAR(64),
    permit_lectura BOOLEAN DEFAULT TRUE,
    permit_escritura BOOLEAN DEFAULT FALSE,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tablas de ejemplo (emplesdos de tu empresa)
INSERT INTO ask_tablas_permitidas (tabla_nombre, tabla_alias, tabla_descripcion) VALUES
('empleados', 'Empleados', 'Lista de empleados de la empresa'),
('clientes', 'Clientes', 'Base de clientes'),
('facturas', 'Facturas', 'Facturas emitidas'),
('servicios', 'Servicios', 'Servicios activos');

-- ═══════════════════════════════════════════════════════════════════
-- CONVERSACIONES
-- Historial de mensajes entre usuarios y el bot
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_conversaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    canal ENUM('web','whatsapp','telegram','api') DEFAULT 'web',
    usuario_id VARCHAR(64),
    usuario_nombre VARCHAR(255),
    usuario_email VARCHAR(255),
    mensaje TEXT NOT NULL,
    respuesta TEXT,
    tokens INT,
    consulta_sql TEXT,
    escalated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_canal (canal),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- SESIONES
-- Control de sesiones de usuarios
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) UNIQUE NOT NULL,
    canal ENUM('web','whatsapp','telegram','api') DEFAULT 'web',
    usuario_id VARCHAR(64),
    usuario_nombre VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    ultima_conversacion DATETIME,
    mensajes_count INT DEFAULT 0,
    estado ENUM('activa','cerrada','escalada') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- TICKETS (ESCALAMIENTOS)
-- Cuando el bot no puede responder, se crea un ticket
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64),
    pregunta TEXT NOT NULL,
    respuesta_bot TEXT,
    estado ENUM('pendiente','viendo','atendido','resuelto') DEFAULT 'pendiente',
    asignado_a VARCHAR(255),
    nota TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- CONFIGURACIÓN
-- Almacena la configuración del bot
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(64) UNIQUE NOT NULL,
    valor TEXT,
    tipo ENUM('string','number','boolean','json') DEFAULT 'string',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- ESTADÍSTICAS
-- Métricas de uso del bot
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_estadisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    conversaciones_total INT DEFAULT 0,
    mensajes_total INT DEFAULT 0,
    tickets INT DEFAULT 0,
    tokens_usados INT DEFAULT 0,
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- CANALES
-- Configuración de WhatsApp, Telegram, etc
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_canales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    canal ENUM('web','whatsapp','telegram') NOT NULL,
    config JSON,
    webhook_url VARCHAR(500),
    webhook_secret VARCHAR(128),
    bot_token VARCHAR(255),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_canal (canal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- USUARIOS ADMIN
-- Usuarios que pueden acceder al panel de admin
-- ═══════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS ask_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(255),
    rol ENUM('admin','agente','user') DEFAULT 'user',
    activo BOOLEAN DEFAULT TRUE,
    ultimo_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- DATOS DE LA EMPRESA (se crea automáticamente)
-- ═══════════════════════════════════════════════════════════════════

INSERT INTO ask_empresa (nombre, email) VALUES ('Mi Empresa', 'admin@empresa.com');

-- ═══════════════════════════════════════════════════════════════════
-- USUARIO ADMIN POR DEFECTO
-- Username: admin
-- Password: admin123 (cámbialo en producción!)
-- ═══════════════════════════════════════════════════════════════════

INSERT INTO ask_usuarios (username, email, password_hash, nombre, rol) 
VALUES ('admin', 'admin@empresa.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin');

-- ═══════════════════════════════════════════════════════════════════
-- CONFIGURACIÓN INICIAL
-- ═══════════════════════════════════════════════════════════════════

INSERT INTO ask_config (clave, valor, tipo) VALUES 
('ia_provider', 'ollama', 'string'),
('ia_model', 'llama3', 'string'),
('ollama_endpoint', 'http://localhost:11434', 'string'),
('empresa_nombre', 'Mi Empresa', 'string'),
('empresa_email', 'admin@empresa.com', 'string');