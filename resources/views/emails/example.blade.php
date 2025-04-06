<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>{{ $title ?? 'Bienvenido' }}</h2>
        
        <div style="margin: 20px 0;">
            {!! $content !!}
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666; font-size: 12px;">
                Este es un correo automático, por favor no responda a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>