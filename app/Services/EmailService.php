<?php

namespace App\Services;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailService
{
    protected $maxRetries = 3;
    protected $retryDelay = 5;  // segundos
    protected $rateLimitKey = 'email_rate_limit';
    protected $rateLimitMax = 100;  // máximo de correos por hora
    protected $rateLimitExpiration = 3600;  // 1 hora

    /**
     * Validar una dirección de correo electrónico
     *
     * @param string $email
     * @return bool
     */
    protected function validateEmail(string $email): bool
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        return !$validator->fails();
    }

    /**
     * Verificar el límite de envío
     *
     * @return bool
     */
    protected function checkRateLimit(): bool
    {
        $count = Cache::get($this->rateLimitKey, 0);
        return $count < $this->rateLimitMax;
    }

    /**
     * Incrementar el contador de límite de envío
     */
    protected function incrementRateLimit(): void
    {
        $count = Cache::get($this->rateLimitKey, 0);
        Cache::put($this->rateLimitKey, $count + 1, $this->rateLimitExpiration);
    }

    /**
     * Enviar un correo electrónico simple con reintentos
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param array $attachments
     * @return bool
     */
    public function sendEmail(string $to, string $subject, string $content, array $attachments = []): bool
    {
        if (!$this->validateEmail($to)) {
            Log::error('Dirección de correo inválida', ['email' => $to]);
            return false;
        }

        if (!$this->checkRateLimit()) {
            Log::warning('Límite de envío alcanzado');
            return false;
        }

        $attempt = 0;
        $success = false;

        while ($attempt < $this->maxRetries && !$success) {
            try {
                Mail::queue(function (Message $message) use ($to, $subject, $content, $attachments) {
                    $message
                        ->to($to)
                        ->subject($subject)
                        ->html($content);

                    foreach ($attachments as $attachment) {
                        if (file_exists($attachment)) {
                            $message->attach($attachment);
                        }
                    }
                });

                $this->incrementRateLimit();
                Event::dispatch('email.sent', [
                    'to' => $to,
                    'subject' => $subject,
                    'attempt' => $attempt + 1
                ]);

                Log::info('Correo enviado exitosamente', [
                    'to' => $to,
                    'subject' => $subject,
                    'attempt' => $attempt + 1
                ]);

                $success = true;
            } catch (\Exception $e) {
                $attempt++;
                Log::error('Error al enviar correo (intento ' . $attempt . '/' . $this->maxRetries . ')', [
                    'error' => $e->getMessage(),
                    'to' => $to,
                    'subject' => $subject
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay);
                }
            }
        }

        return $success;
    }

    /**
     * Enviar un correo electrónico usando una plantilla Blade con reintentos
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @param array $attachments
     * @return bool
     */
    public function sendTemplateEmail(string $to, string $subject, string $view, array $data = [], array $attachments = []): bool
    {
        if (!$this->validateEmail($to)) {
            Log::error('Dirección de correo inválida', ['email' => $to]);
            return false;
        }

        if (!$this->checkRateLimit()) {
            Log::warning('Límite de envío alcanzado');
            return false;
        }

        $attempt = 0;
        $success = false;

        while ($attempt < $this->maxRetries && !$success) {
            try {
                Mail::queue($view, $data, function (Message $message) use ($to, $subject, $attachments) {
                    $message
                        ->to($to)
                        ->subject($subject);

                    foreach ($attachments as $attachment) {
                        if (file_exists($attachment)) {
                            $message->attach($attachment);
                        }
                    }
                });

                $this->incrementRateLimit();
                Event::dispatch('email.template.sent', [
                    'to' => $to,
                    'subject' => $subject,
                    'template' => $view,
                    'attempt' => $attempt + 1
                ]);

                Log::info('Correo con plantilla enviado exitosamente', [
                    'to' => $to,
                    'subject' => $subject,
                    'template' => $view,
                    'attempt' => $attempt + 1
                ]);

                $success = true;
            } catch (\Exception $e) {
                $attempt++;
                Log::error('Error al enviar correo con plantilla (intento ' . $attempt . '/' . $this->maxRetries . ')', [
                    'error' => $e->getMessage(),
                    'to' => $to,
                    'subject' => $subject,
                    'template' => $view
                ]);

                if ($attempt < $this->maxRetries) {
                    sleep($this->retryDelay);
                }
            }
        }

        return $success;
    }

    /**
     * Obtener estadísticas de envío
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            'rate_limit' => [
                'current' => Cache::get($this->rateLimitKey, 0),
                'max' => $this->rateLimitMax,
                'reset_in' => Cache::get($this->rateLimitKey) ? Cache::ttl($this->rateLimitKey) : 0
            ]
        ];
    }
}
