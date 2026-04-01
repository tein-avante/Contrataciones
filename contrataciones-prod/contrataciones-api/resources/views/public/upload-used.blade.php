<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace Utilizado</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <!-- Borde rojo corporativo para indicar estado "inactivo" -->
    <div class="bg-white p-10 rounded-lg shadow-lg w-full max-w-lg text-center border-t-4 border-[#BB0808]">

        <!-- ▼▼▼ LOGO CORPORATIVO ▼▼▼ -->
        <div class="flex justify-center mb-8">
            <img src="{{ asset('logo.png') }}" alt="Logo Corporativo" class="h-20 w-auto object-contain">
        </div>

        <!-- Icono de Alerta/Candado -->
        <div class="flex justify-center mb-6">
            <div class="rounded-full bg-red-50 p-3">
                <svg class="w-16 h-16 text-[#BB0808]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
        </div>

        <!-- Títulos -->
        <h1 class="text-2xl font-bold mb-3 text-gray-800">Este enlace ya ha sido utilizado</h1>

        <p class="text-gray-600 mb-6">
            La solicitud de carga asociada a este enlace ya fue completada anteriormente. Por razones de seguridad, los enlaces son de un único uso.
        </p>

        <!-- Ticket -->
        <div class="bg-gray-50 p-4 rounded-md mb-8">
            <p class="text-sm text-gray-500 mb-1">Ticket asociado:</p>
            <span class="font-mono text-lg font-bold text-gray-800">{{ $ticket }}</span>
        </div>

        <p class="text-sm text-gray-400">
            Si crees que esto es un error, por favor contacta a la Gerencia de Contrataciones.
        </p>
    </div>
</body>
</html>
