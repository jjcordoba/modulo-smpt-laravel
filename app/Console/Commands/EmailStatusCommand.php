<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EmailStatusCommand extends Command
{
    protected $signature = 'email:status
                            {--reset : Reiniciar los contadores de límite de envío}
                            {--watch : Monitorear el estado en tiempo real}';

    protected $description = 'Muestra el estado y las estadísticas del servicio de correo';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    public function handle()
    {
        if ($this->option('reset')) {
            Cache::forget('email_rate_limit');
            $this->info('✓ Contadores de límite de envío reiniciados');
            return 0;
        }

        if ($this->option('watch')) {
            $this->watchStatus();
            return 0;
        }

        $this->showStatus();
        return 0;
    }

    protected function showStatus()
    {
        $stats = $this->emailService->getStats();
        $rateLimit = $stats['rate_limit'];

        $this->info('Estado del Servicio de Correo');
        $this->newLine();

        // Mostrar configuración SMTP
        $this->table(
            ['Configuración SMTP', 'Valor'],
            [
                ['Mailer', config('mail.default')],
                ['Host', config('mail.mailers.smtp.host')],
                ['Puerto', config('mail.mailers.smtp.port')],
                ['Encriptación', config('mail.mailers.smtp.encryption')],
                ['From Address', config('mail.from.address')],
            ]
        );

        $this->newLine();
        $this->info('Límites de Envío');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Correos enviados (última hora)', $rateLimit['current']],
                ['Límite máximo por hora', $rateLimit['max']],
                ['Tiempo para reinicio', $this->formatSeconds($rateLimit['reset_in'])],
                ['Porcentaje utilizado', round(($rateLimit['current'] / $rateLimit['max']) * 100, 2) . '%'],
            ]
        );
    }

    protected function watchStatus()
    {
        $this->info('Monitoreando el estado del servicio de correo (Ctrl+C para salir)');
        $this->newLine();

        while (true) {
            $this->output->write("\e[H\e[2J");  // Limpiar pantalla
            $this->showStatus();
            sleep(5);  // Actualizar cada 5 segundos
        }
    }

    protected function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' segundos';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return $minutes . ' minutos ' . $remainingSeconds . ' segundos';
    }
}
