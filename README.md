<!-- markdownlint-disable MD001 MD002 MD004 MD005 MD006 MD007 MD008 MD009 MD010 MD011 MD012 MD013 MD014 MD018 MD019 MD020 MD021 MD022 MD023 MD024 MD025 MD026 MD027 MD028 MD029 MD030 MD031 MD032 MD033 MD034 MD035 MD036 MD037 MD038 MD039 MD040 MD041 MD042 MD043 MD044 MD045 MD046 MD047 MD048 MD049 MD050 MD051 MD052 MD053 MD054 MD055 MD056 MD057 MD058 MD059 MD060 MD061 MD062 MD063 MD064 MD065 MD066 MD067 MD068 MD069 MD070 MD071 MD072 MD073 MD074 MD075 MD076 MD077 MD078 MD079 MD080 MD081 MD082 MD083 MD084 MD085 MD086 MD087 MD088 MD089 MD090 MD091 MD092 MD093 MD094 MD095 MD096 MD097 MD098 MD099 MD100 MD101 MD102 MD103 MD104 MD105 MD106 MD107 MD108 MD109 MD110 MD111 MD112 MD113 MD114 MD115 MD116 MD117 MD118 MD119 MD120 MD121 MD122 MD123 MD124 MD125 MD126 MD127 MD128 MD129 MD130 MD131 MD132 MD133 MD134 MD135 MD136 -->

<div align="center">

# 🤖 AskBot

### Asistente de IA para consultar tu base de datos en lenguaje natural

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)](https://www.php.net/)
[![MySQL 5.7+](https://img.shields.io/badge/MySQL-5.7+-4479A1.svg)](https://www.mysql.com/)
[![Open Source](https://img.shields.io/badge/Open%20Source-Yes-green.svg)](https://opensource.org/)

*Pregunta a tu base de datos en lenguaje natural. Sin SQL, sin conocimientos técnicos.*

</div>

---

## 📋 Tabla de Contenidos

- [✨ Características](#-características)
- [🚀 Instalación](#-instalación)
- [⚡ Uso Rápido](#-uso-rápido)
- [🔧 Configuración](#-configuración)
- [🌐 Canales](#-canales)
- [📊 API REST](#-api-rest)
- [🖥️ Panel de Administración](#-panel-de-administración)
- [🏗️ Arquitectura](#-arquitectura)
- [🤝 Contribuir](#-contribuir)
- [📄 Licencia](#-licencia)

---

## ✨ Características

| Característica | Descripción |
|----------------|-------------|
| **NLP a SQL** | Convierte preguntas en español a consultas SQL automáticamente |
| **Multi-Canal** | Web, WhatsApp, Telegram en un solo bot |
| **Escalamiento** | Transfiere a humano cuando no puede responder |
| **Auto-Adaptable** | Se adapta automáticamente a tu estructura de BD |
| **Estadísticas** | Dashboard con métricas de uso |
| **Open Source** | Código libre, comunidad activa |

---

## 🚀 Instalación

### Requisitos

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Ollama (opcional, para IA local) o API externa

### Pasos

```bash
# 1. Clonar el proyecto
git clone https://github.com/JonatanJHL/askbot.git
cd askbot

# 2. Instalar dependencias
composer install

# 3. Importar base de datos
mysql -u root -p tu_base_datos < core/database.sql

# 4. Configurar
cp config.example.php config.php
# Edita config.php con tus credenciales

# 5. Ejecutar
php -S localhost:8000 -t public
```

---

## ⚡ Uso Rápido

### Inicializar AskBot

```php
<?php
require_once 'core/AskBot.php';

$bot = new AskBot($empresa_id);
$respuesta = $bot->procesarMensaje(
    "¿Cuántos clientes tenemos?", 
    session_id: "abc123"
);

echo $respuesta['respuesta'];
// Resultado: "Tienes 247 clientes en total."
```

### Ejemplos de Preguntas

| Pregunta | Consulta Generada |
|----------|------------------|
| "¿Cuántos servicios activos tenemos?" | `SELECT COUNT(*) FROM servicios WHERE estado = 'activo'` |
| "¿Quién es el cliente con más ventas?" | `SELECT * FROM clientes ORDER BY ventas DESC LIMIT 1` |
| "¿Tenemos facturas pendientes?" | `SELECT * FROM facturas WHERE estado = 'pendiente'` |

---

## 🔧 Configuración

### config.php

```php
<?php
return [
    'database' => [
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => 'tu_password',
        'name'     => 'askbot'
    ],
    'ia' => [
        'provider' => 'ollama',  // ollama, openrouter, anthropic
        'model'    => 'llama3',
        'endpoint' => 'http://localhost:11434'
    ],
    'security' => [
        'license_required' => false  // true para producción
    ]
];
```

### Variables de Entorno

```bash
# IA
OLLAMA_ENDPOINT=http://localhost:11434
OPENROUTER_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...

# Canales
TELEGRAM_BOT_TOKEN=123456:ABC-DEF
WHATSAPP_TOKEN=EAAC...
WHATSAPP_PHONE_ID=123456789

# Email
SMTP_HOST=smtp.gmail.com
SMTP_USER=tu@email.com
SMTP_PASS=tu_password
```

---

## 🌐 Canales

### Web Widget

```html
<script src="https://tu-dominio.com/bot/widget.js"></script>
<script>
    AskBot.init({
        empresa: 'tu_empresa',
        color: '#3b82f6'
    });
</script>
```

### Telegram

1. Crea un bot con @BotFather
2. Configura el webhook:
```
https://tu-dominio.com/api/webhook/telegram
```

### WhatsApp

1. Crea app en Meta Developers
2. Configura webhooks
3. Agrega token en config

---

## 📊 API REST

### Autenticación

```bash
curl -H "Authorization: Bearer TU_LICENSE_KEY" \
     https://tu-dominio.com/api/chat
```

### Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/chat` | Enviar mensaje |
| GET | `/api/stats` | Estadísticas |
| GET | `/api/escalamientos` | Ver escalamientos |
| POST | `/api/licencia` | Crear licencia |
| GET | `/api/config` | Ver configuración |

### Ejemplo: Chat

```bash
curl -X POST https://tu-dominio.com/api/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer TU_KEY" \
  -d '{
    "message": "¿Cuántos clientes tenemos?",
    "canal": "web"
  }'
```

Respuesta:

```json
{
  "session_id": "abc123",
  "response": "Tienes 247 clientes en total.",
  "tipo": "consulta",
  "timestamp": "2025-01-15T10:30:00Z"
}
```

---

## 🖥️ Panel de Administración

Accede a `/admin/` para:

- 📈 Dashboard con métricas
- 💬 Ver conversaciones
- 🎫 Gestionar tickets de soporte
- 🗄️ Configurar tablas de tu BD
- 👀 Ver datos de tus tablas

---

## 🗄️ Base de Datos

Al instalar, se crean las siguientes tablas:

| Tabla | Descripción |
|-------|-------------|
| `ask_empresa` | Tu empresa (se crea automáticamente) |
| `ask_tablas_permitidas` | Tablas de tu BD que el bot puede consultar |
| `ask_conversaciones` | Historial de mensajes |
| `ask_sessions` | Sesiones de usuarios |
| `ask_tickets` | Tickets cuando el bot no puede responder |
| `ask_config` | Configuración del bot |
| `ask_estadisticas` | Métricas de uso |
| `ask_canales` | Webhooks (WhatsApp, Telegram) |
| `ask_usuarios` | Usuarios del panel admin |

### Credenciales por defecto

- **Usuario**: admin
- **Password**: admin123

> ⚠️ **Cambia la contraseña en producción!**

---

## 🏗️ Arquitectura

```
askbot/
├── core/                  # Motor del sistema
│   ├── AskBot.php        # Clase principal
│   ├── Database.php      # Conexión BD
│   ├── Canales.php      # Gestión de canales
│   ├── helpers.php      # Funciones utilitarias
│   └── api.php          # API REST
├── public/              # Archivos públicos
│   └── index.php       # Web widget
├── admin/              # Panel de administración
│   └── index.html      # Dashboard
├── docs/               # Documentación
└── database.sql        # Schema de BD
```

### Escalabilidad

| Aspecto | Implementación |
|---------|----------------|
| **BD** | Índices, cache, replication |
| **IA** | Ollama local o cloud |
| **API** | Rate limiting, cache |
| **Canales** | Colas asíncronas |
| **Cache** | Redis/Memcached |

---

## 🤝 Contribuir

¡Todas las contribuciones son bienvenidas!

1. Fork el proyecto
2. Crea tu rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

Ver [CONTRIBUTING.md](CONTRIBUTING.md) para más detalles.

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver [LICENSE](LICENSE) para detalles.

---

## ⭐ Creador

<div align="center">

**AskBot** fue creado por **Jonatan Hidalgo**

- 💻 GitHub: [github.com/JonatanJHL](https://github.com/JonatanJHL)
- 📧 Email: jonatanhidalgoledesma@gmail.com
- 🔗 LinkedIn: [linkedin.com/in/jonatan-joaquin-hidalgo-ledesma](https://www.linkedin.com/in/jonatan-joaquin-hidalgo-ledesma/)

¿Te gusta el proyecto? ¡Da una ⭐ y contribuye!

</div>
