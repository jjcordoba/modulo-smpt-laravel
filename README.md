# Módulo de Correo Electrónico para Laravel

Este módulo proporciona una implementación robusta para el envío de correos electrónicos en Laravel, utilizando SMTP y sistema de colas para un manejo eficiente de los correos.

## Requisitos Previos

- Laravel 8.x o superior
- PHP 7.4 o superior
- Configuración de base de datos (para el sistema de colas)
- Servidor SMTP (puede usar servicios como Mailtrap, Gmail, Amazon SES, etc.)

## Instalación

1. Clone este módulo en su proyecto Laravel
2. Copie el archivo `.env.example` a `.env` y configure las variables de entorno

## Configuración

### 1. Variables de Entorno

Configure las siguientes variables en su archivo `.env`:

```env
# Configuración del servidor SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=su_username
MAIL_PASSWORD=su_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="${APP_NAME}"
MAIL_REPLY_TO_ADDRESS=hello@example.com
MAIL_REPLY_TO_NAME="${APP_NAME}"

# Configuración de colas para correos
QUEUE_CONNECTION=database
MAIL_QUEUE_NAME=emails
```

### 2. Configuración del Servicio de Correo

El archivo `config/mail.php` ya está configurado con los ajustes necesarios. Asegúrese de que las variables de entorno estén correctamente establecidas.

### 3. Migración para Colas (opcional)

Si va a utilizar el sistema de colas, ejecute:

```bash
php artisan queue:table
php artisan migrate
```

## Uso del Servicio de Correo

### Ejemplo Básico

```php
use App\Services\EmailService;

public function enviarCorreo(EmailService $emailService)
{
    $emailService->send(
        'correo@destino.com',
        'Asunto del Correo',
        'emails.template',
        ['nombre' => 'Usuario']
    );
}
```

### Envío con Archivos Adjuntos

```php
$emailService->sendWithAttachment(
    'correo@destino.com',
    'Asunto del Correo',
    'emails.template',
    ['nombre' => 'Usuario'],
    '/ruta/al/archivo.pdf'
);
```

## Plantillas de Correo

Las plantillas de correo se encuentran en `resources/views/emails/`. Puede crear nuevas plantillas siguiendo la estructura de Blade.

Ejemplo de plantilla:

```blade
<!-- resources/views/emails/template.blade.php -->
<h1>Hola {{ $nombre }}!</h1>
<p>Este es un correo de ejemplo.</p>
```

## Sistema de Colas

Para procesar los correos en segundo plano:

1. Inicie el worker de colas:

```bash
php artisan queue:work --queue=emails
```

2. Para enviar correos a la cola:

```php
$emailService->queue(
    'correo@destino.com',
    'Asunto del Correo',
    'emails.template',
    ['nombre' => 'Usuario']
);
```

## Pruebas

1. Configure Mailtrap u otro servicio de prueba de correos
2. Envíe un correo de prueba:

```php
php artisan tinker
app(App\Services\EmailService::class)->send('prueba@email.com', 'Prueba', 'emails.template', ['nombre' => 'Test']);
```

## Solución de Problemas

- Verifique la configuración SMTP en el archivo `.env`
- Revise los logs en `storage/logs/laravel.log`
- Asegúrese de que el servicio SMTP esté accesible
- Verifique que las plantillas de correo existan y estén correctamente nombradas

## Contribución

Si encuentra algún problema o tiene sugerencias de mejora, no dude en crear un issue o enviar un pull request.
