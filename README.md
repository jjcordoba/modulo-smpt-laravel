# Módulo de Correo Electrónico para Laravel

Este módulo proporciona una implementación robusta para el envío de correos electrónicos en Laravel, utilizando SMTP y sistema de colas para un manejo eficiente de los correos.

## Características

- Configuración SMTP simplificada
- Sistema de límites de envío por hora
- Monitoreo en tiempo real del estado del servicio
- Comandos de consola para pruebas y diagnóstico
- Integración con el sistema de colas de Laravel
- Manejo de plantillas de correo

## Instalación

1. Copie los archivos del módulo a su proyecto Laravel
2. Configure las variables de entorno SMTP en su archivo `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
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

2. **Límite de envío alcanzado**

   - Use el comando `email:status` para verificar el estado
   - Reinicie los contadores si es necesario
   - Considere aumentar el límite en la configuración

3. **Errores en las plantillas**
   - Verifique la existencia de la plantilla en `resources/views/emails`
   - Valide la sintaxis de Blade en la plantilla
   - Confirme que todas las variables requeridas estén definidas

## Contribución

Si encuentra algún problema o tiene sugerencias de mejora, por favor abra un issue o envíe un pull request.

## Licencia

Este módulo está licenciado bajo la Licencia MIT. Consulte el archivo LICENSE para más detalles.
