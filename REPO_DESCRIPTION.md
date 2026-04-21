# Descripción del Repositorio - AskBot Open Source

## AskBot - Asistente de IA para tu Base de Datos

🤖 **AskBot** es un asistente de inteligencia artificial de código abierto (Open Source) que permite consultar tu base de datos MySQL usando lenguaje natural en español. Sin necesidad de conocer SQL, tus usuarios pueden hacer preguntas como "¿Cuántos clientes tenemos?" y el bot genera automáticamente la consulta.

### Características Principales

- **NLP a SQL**: Convierte preguntas en español a consultas SQL automáticamente
- **Multi-Canal**: Web, WhatsApp y Telegram en un solo bot
- **Escalamiento**: Cuando el bot no puede responder, crea tickets para atención humana
- **Auto-Adaptable**: Se adapta a tu estructura de base de datos
- **Open Source**: MIT License - Totalmente libre y gratuito

### Tecnologías

- PHP 8.0+
- MySQL 5.7+
- Ollama (IA localgratis)
- OpenRouter / Anthropic (opcionales)

### Instalación

```bash
git clone https://github.com/JonatanJHL/askbot.git
cd askbot
composer install
mysql -u root -p askbot < core/database.sql
php -S localhost:8000 -t public
```

### Licencia

MIT License - Código libre para usar, modificar y distribuir.

---

**Creador**: Jonatan Hidalgo
- GitHub: github.com/JonatanJHL
- Email: jonatanhidalgoledesma@gmail.com