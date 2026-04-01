<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-w-600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; }
        .logo-container { text-align: center; margin-bottom: 20px; }
        /* Estilo para el botón */
        .btn { display: inline-block; background-color: #077BA1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
        .doc-box { background-color: #f0fdf4; border-left: 4px solid #077BA1; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">

        <!-- LOGO -->
        <div class="logo-container">
            <img src="{{ $message->embed(public_path('logo.png')) }}"
                 alt="Avante Bureau Shipping"
                 style="max-height: 80px; display: block; margin: 0 auto;">
        </div>

        <h2>¡Hola, {{ $notifiable->nombre }}!</h2>

        <p>La Gerencia de Contrataciones ha generado una nueva solicitud.</p>

        <!-- ▼▼▼ AQUÍ MOSTRAMOS QUÉ DOCUMENTO ES ▼▼▼ -->
        <p>Se requiere que procedas a <strong>{{ $accion }}</strong> el siguiente documento:</p>

        <div class="doc-box">
            <p style="margin: 0; font-size: 16px; color: #1f2937;">
                📄 <strong>{{ $nombreDocumento }}</strong>
            </p>
        </div>
        <!-- ▲▲▲ FIN DEL CAMBIO ▲▲▲ -->

        <p><strong>Fecha Límite:</strong> {{ \Carbon\Carbon::parse($solicitud->fecha_expiracion)->format('d/m/Y') }}</p>

        <p>Esta solicitud es de un único uso.</p>

        <div style="text-align: center;">
            <a href="{{ $url }}" class="btn">Completar Solicitud</a>
        </div>

        <p style="font-size: 12px; color: #666; margin-top: 30px;">Si tienes algún problema, por favor contacta a la Gerencia de Contrataciones.</p>
    </div>
</body>
</html>
