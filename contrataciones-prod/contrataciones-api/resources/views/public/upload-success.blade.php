<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Completada</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-10 rounded-lg shadow-lg w-full max-w-lg text-center border-t-4 border-green-500">

        <!-- ▼▼▼ LOGO CORPORATIVO ▼▼▼ -->
        <div class="flex justify-center mb-8">
            <img src="{{ asset('logo.png') }}" alt="Logo Corporativo" class="h-20 w-auto object-contain">
        </div>

        <!-- Icono de Éxito -->
        <div class="flex justify-center mb-6">
            <div class="rounded-full bg-green-100 p-3">
                <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <!-- Mensajes -->
        <h1 class="text-2xl font-bold mb-3 text-gray-800">¡Documento Subido con Éxito!</h1>
        <p class="text-gray-600 mb-6">Hemos recibido tu documento correctamente. La Gerencia de Contrataciones revisará la información a la brevedad.</p>

        <div class="bg-gray-50 p-4 rounded-md mb-8">
            <p class="text-sm text-gray-500 mb-1">Tu número de seguimiento es:</p>
            <span class="font-mono text-lg font-bold text-gray-800">{{ $ticket }}</span>
        </div>

        <p class="text-sm text-gray-400">Ya puedes cerrar esta ventana de forma segura.</p>
    </div>
</body>
</html>
