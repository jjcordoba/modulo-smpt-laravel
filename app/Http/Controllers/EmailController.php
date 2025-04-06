<?php

namespace App\Http\Controllers;

use App\Services\EmailService;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Enviar un correo electrónico simple
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'content' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file'
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->getPathname();
            }
        }

        $success = $this->emailService->sendEmail(
            $request->input('to'),
            $request->input('subject'),
            $request->input('content'),
            $attachments
        );

        if ($success) {
            return response()->json([
                'message' => 'Correo enviado exitosamente'
            ]);
        }

        return response()->json([
            'message' => 'Error al enviar el correo'
        ], 500);
    }

    /**
     * Enviar un correo electrónico usando una plantilla
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTemplateEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file'
        ]);

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachments[] = $file->getPathname();
            }
        }

        $success = $this->emailService->sendTemplateEmail(
            $request->input('to'),
            $request->input('subject'),
            'emails.example',
            [
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'subject' => $request->input('subject')
            ],
            $attachments
        );

        if ($success) {
            return response()->json([
                'message' => 'Correo con plantilla enviado exitosamente'
            ]);
        }

        return response()->json([
            'message' => 'Error al enviar el correo con plantilla'
        ], 500);
    }
}
