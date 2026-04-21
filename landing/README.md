# Bot IA - Landing Page de Ventas

## Archivos

- `index.html` - Página de ventas
- `send-email.php` - Backend para enviar emails
- `composer.json` - Dependencias (PHPMailer)

## Instalación

```bash
# Instalar dependencias
composer install
```

## Configuración SMTP

Edita `send-email.php` con tus credenciales:

```php
$mail->Host       = 'smtp.gmail.com';        // Tu servidor SMTP
$mail->Username   = 'tu-email@gmail.com';     // Tu email
$mail->Password   = 'xxxx xxxx xxxx xxxx';   // App password de Gmail
```

### Gmail App Password

1. Ve a [myaccount.google.com](https://myaccount.google.com)
2. Seguridad → Verificación en 2 pasos → Activala
3. Seguridad → Contraseñas de aplicaciones → Genera una
4. Usa esa contraseña de 16 caracteres

### Otros SMTP

Puedes usar Mailtrap, SendGrid, Amazon SES, etc.

## Uso LOCAL (XAMPP/MAMP)

```bash
# En tu servidor local
http://localhost/bot-ia-ventas/
```

## Subir a producción

1. Sube todos los archivos al hosting
2. Configura el SMTP en send-email.php
3. Listo → Los formularios enviarán emails