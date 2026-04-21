# AskBot - Documentación

## Instalación

```bash
# Clonar el proyecto
git clone https://github.com/JonatanJHL/AskBot.git
cd AskBot

# Instalar dependencias
composer install

# Importar base de datos
mysql -u root -p askbot < core/database.sql

# Configurar
cp config.example.php config.php

# Ejecutar
php -S localhost:8000 -t public
```

## Estructura

| Carpeta | Descripción |
|---------|-------------|
| `core/` | Motor del sistema (AskBot.php, api.php, etc.) |
| `public/` | Widget web |
| `admin/` | Panel de administración |
| `landing/` | Página de ventas |

## Base de Datos

Al importar `core/database.sql` se crean todas las tablas necesarias:

- `ask_empresa` - Tu empresa
- `ask_tablas_permitidas` - Tablas que el bot puede consultar
- `ask_conversaciones` - Historial de mensajes
- `ask_tickets` - Tickets de soporte
- `ask_config` - Configuración
- `ask_usuarios` - Usuarios admin

## API

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/chat` | POST | Enviar mensaje |
| `/api/conversaciones` | GET | Ver conversaciones |
| `/api/tickets` | GET | Ver tickets |
| `/api/tablas` | GET | Tablas permitidas |
| `/api/stats` | GET | Estadísticas |

## Licencia

MIT License - 100% Open Source