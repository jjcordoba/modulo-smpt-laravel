# Módulo de Correo Electrónico para Laravel

Este módulo proporciona una implementación robusta para el envío de correos electrónicos en Laravel, utilizando SMTP y sistema de colas para un manejo eficiente de los correos.

## Requisitos Previos

- PHP >= 7.4
- Laravel >= 8.0
- Composer instalado
- Acceso a un servidor SMTP

## Características

- Configuración SMTP simplificada
- Sistema de límites de envío por hora
- Monitoreo en tiempo real del estado del servicio
- Comandos de consola para pruebas y diagnóstico
- Integración con el sistema de colas de Laravel
- Manejo de plantillas de correo

## Instalación

### 1. Clonar el Repositorio

```bash
# Clonar el repositorio en la carpeta de tu elección
git clone https://github.com/jjcordoba/modulo-smpt-laravel.git

# Entrar al directorio del módulo
cd modulo-email
```

### 2. Copiar Archivos al Proyecto

1. Copie los archivos del módulo a las carpetas correspondientes de su proyecto Laravel:
   - Copie `/app/Services/EmailService.php` a `app/Services/` de su proyecto
   - Copie `/app/Console/Commands/*` a `app/Console/Commands/` de su proyecto
   - Copie `/config/mail.php` a `config/` de su proyecto (si no existe)
   - Copie `/resources/views/emails/` a `resources/views/` de su proyecto

### 3. Configurar el Entorno

1. Copie las variables de entorno necesarias a su archivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Configuración de límites de envío
MAIL_RATE_LIMIT=100 # correos por hora
```

### 4. Registrar el Servicio

1. Agregue el siguiente proveedor de servicios en `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\EmailServiceProvider::class,
];
```

### 5. Configurar Colas (Opcional pero Recomendado)

1. Configure su sistema de colas en `.env`:

```env
QUEUE_CONNECTION=database
```

2. Ejecute la migración para la tabla de colas:

```bash
php artisan queue:table
php artisan migrate
```

## Comandos de Consola

### Prueba de Envío (email:test)

Permite enviar un correo de prueba para verificar la configuración:

```bash
# Envío básico
php artisan email:test recipient@example.com

# Envío con opciones adicionales
php artisan email:test recipient@example.com --subject="Prueba" --content="Mensaje de prueba"
```

### Estado del Servicio (email:status)

Muestra información sobre el estado del servicio de correo:

```bash
# Ver estado actual
php artisan email:status

# Monitorear en tiempo real
php artisan email:status --watch

# Reiniciar contadores de límite
php artisan email:status --reset
```

## Uso del Servicio de Correo

```php
use App\Services\EmailService;

class ExampleController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function sendEmail()
    {
        $this->emailService->send(
            'recipient@example.com',
            'Asunto del correo',
            'emails.template-name',
            ['key' => 'value']
        );
    }
}
```

## Sistema de Límites

El módulo incluye un sistema de límites de envío por hora para prevenir el abuso:

- Monitoreo de correos enviados por hora
- Límite configurable de envíos por hora
- Reinicio automático de contadores
- Comando para reinicio manual de contadores

## Solución de Problemas

### Problemas Comunes

1. **Error de conexión SMTP**

   - Verifique las credenciales en el archivo `.env`
   - Confirme que el puerto SMTP no esté bloqueado
   - Valide la configuración de encriptación (TLS/SSL)
   - Asegúrese de que su servidor SMTP esté activo

2. **Límite de envío alcanzado**

   - Use el comando `email:status` para verificar el estado
   - Reinicie los contadores si es necesario
   - Considere aumentar el límite en la configuración
   - Verifique los logs para detectar posibles abusos

3. **Errores en las plantillas**

   - Verifique la existencia de la plantilla en `resources/views/emails`
   - Valide la sintaxis de Blade en la plantilla
   - Confirme que todas las variables requeridas estén definidas
   - Pruebe la plantilla con datos de ejemplo

4. **Problemas con las colas**
   - Verifique que el worker de colas esté ejecutándose
   - Revise los logs de Laravel para errores
   - Confirme la configuración de la conexión de cola
   - Ejecute `php artisan queue:retry all` para reintentar trabajos fallidos

## Contribución

Si encuentra algún problema o tiene sugerencias de mejora, por favor:

1. Revise los issues existentes o cree uno nuevo
2. Fork el repositorio
3. Cree una rama para su feature (`git checkout -b feature/AmazingFeature`)
4. Commit sus cambios (`git commit -m 'Add some AmazingFeature'`)
5. Push a la rama (`git push origin feature/AmazingFeature`)
6. Abra un Pull Request

## Licencia

Este módulo está licenciado bajo la Licencia MIT. Consulte el archivo LICENSE para más detalles.
