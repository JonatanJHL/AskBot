# AskBot - Landing Page

Página web pública para mostrar AskBot.

## Archivos

- `index.html` - Landing page pública
- `send-email.php` - Formulario de contacto (opcional)
- `composer.json` - Dependencias (PHPMailer)

## Uso

Simplemente abre `index.html` en un navegador o súbelo a tu servidor.

## Formulario de Contacto (Opcional)

Si quieres el formulario de contacto funcional:

```bash
composer install
```

Edita `send-email.php` con tu SMTP:

```php
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'app_password';
```