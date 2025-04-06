<?php

namespace App\Services;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Enviar un correo electrónico simple
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param array $attachments
     * @return bool
     */
    public function sendEmail(string $to, string $subject, string $content, array $attachments = [])
    {
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

            Log::info('Correo enviado exitosamente', [
                'to' => $to,
                'subject' => $subject
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo', [
                'error' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject
            ]);

            return false;
        }
    }

    /**
     * Enviar un correo electrónico usando una plantilla Blade
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @param array $attachments
     * @return bool
     */
    public function sendTemplateEmail(string $to, string $subject, string $view, array $data = [], array $attachments = [])
    {
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

            Log::info('Correo con plantilla enviado exitosamente', [
                'to' => $to,
                'subject' => $subject,
                'template' => $view
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo con plantilla', [
                'error' => $e->getMessage(),
                'to' => $to,
                'subject' => $subject,
                'template' => $view
            ]);

            return false;
        }
    }
}
