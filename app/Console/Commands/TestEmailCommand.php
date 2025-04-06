<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test
                            {to? : Dirección de correo destino}
                            {--template : Usar plantilla de prueba}
                            {--subject=Test Email : Asunto del correo}
                            {--content=Test content : Contenido del correo}';

    protected $description = 'Envía un correo de prueba para verificar la configuración SMTP';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    public function handle()
    {
        $to = $this->argument('to') ?: $this->ask('Ingrese la dirección de correo destino:');

        // Validar el correo
        $validator = Validator::make(['email' => $to], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $this->error('La dirección de correo no es válida.');
            return 1;
        }

        $subject = $this->option('subject');
        $content = $this->option('content');
        $useTemplate = $this->option('template');

        $this->info('Enviando correo de prueba...');
        $this->newLine();
        $this->table(
            ['Configuración', 'Valor'],
            [
                ['MAIL_MAILER', config('mail.default')],
                ['MAIL_HOST', config('mail.mailers.smtp.host')],
                ['MAIL_PORT', config('mail.mailers.smtp.port')],
                ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
                ['MAIL_FROM_ADDRESS', config('mail.from.address')],
            ]
        );

        try {
            $success = false;
            if ($useTemplate) {
                // Crear una plantilla temporal de prueba si no existe
                $viewPath = resource_path('views/emails/test.blade.php');
                if (!file_exists($viewPath)) {
                    if (!file_exists(dirname($viewPath))) {
                        mkdir(dirname($viewPath), 0755, true);
                    }
                    file_put_contents($viewPath, "<h1>Correo de Prueba</h1>\n<p>{{ $content }}</p>");
                }

                $success = $this->emailService->sendTemplateEmail(
                    $to,
                    $subject,
                    'emails.test',
                    ['content' => $content]
                );
            } else {
                $success = $this->emailService->sendEmail(
                    $to,
                    $subject,
                    $content
                );
            }

            if ($success) {
                $this->info('✓ Correo enviado exitosamente');
                return 0;
            } else {
                $this->error('✗ Error al enviar el correo');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
