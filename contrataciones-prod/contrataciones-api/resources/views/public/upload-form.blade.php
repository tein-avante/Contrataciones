<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Carga de Documento - Ticket {{ $solicitudCarga->ticket }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <!-- Tarjeta Principal con borde superior corporativo -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl border-t-4 border-[#077BA1]">

        <!-- LOGO SIN FONDO -->
        <div class="flex justify-center mb-6">
            <img src="{{ asset('logo.png') }}" alt="Avante Bureau Shipping" class="h-20 w-auto object-contain">
        </div>

        <!-- Cabecera -->
        <h1 class="text-2xl font-bold mb-2 text-gray-800 text-center">Solicitud de Carga de Documento</h1>
        <p class="text-gray-600 mb-8 text-center">Ticket de seguimiento: <span class="font-mono bg-gray-200 px-2 py-1 rounded-md text-sm font-bold">{{ $solicitudCarga->ticket }}</span></p>

        <!-- Caja de Detalles -->
        <div class="bg-gray-50 border border-gray-200 p-6 rounded-md mb-8 space-y-3">

            <!-- Empleado -->
            <p class="text-gray-700 border-b border-gray-200 pb-2">
                <strong class="w-32 inline-block">Empleado:</strong>
                {{ $solicitudCarga->empleado->nombre }}
            </p>

            <!-- ▼▼▼ NUEVA SECCIÓN: DOCUMENTO SOLICITADO ▼▼▼ -->
            <p class="text-gray-700 border-b border-gray-200 pb-2">
                <strong class="w-32 inline-block">Documento:</strong>

                @if($solicitudCarga->documento_id && $solicitudCarga->documento && $solicitudCarga->documento->tipoDocumento)
                    <!-- Caso: Actualización -->
                    <span class="font-medium text-[#077BA1]">
                        Actualización de {{ $solicitudCarga->documento->tipoDocumento->nombre }}
                    </span>
                @elseif($solicitudCarga->tipo_documento_id && $solicitudCarga->tipoDocumento)
                    <!-- Caso: Nuevo Documento -->
                    <span class="font-medium text-[#077BA1]">
                        Nuevo: {{ $solicitudCarga->tipoDocumento->nombre }}
                    </span>
                @else
                    <span class="text-gray-500 italic">Documento requerido</span>
                @endif
            </p>
            <!-- ▲▲▲ FIN DE LA NUEVA SECCIÓN ▲▲▲ -->

            <!-- Fecha Límite -->
            <p class="text-gray-700">
                <strong class="w-32 inline-block">Fecha Límite:</strong>
                <span class="text-[#BB0808] font-bold">{{ $solicitudCarga->fecha_expiracion->format('d/m/Y') }}</span>
            </p>

            <!-- Observaciones -->
            @if($solicitudCarga->observacion)
                <div class="flex pt-2 border-t border-gray-200 mt-2">
                    <strong class="w-32 flex-shrink-0 text-gray-700">Observaciones:</strong>
                    <span class="text-gray-600 italic">{{ $solicitudCarga->observacion }}</span>
                </div>
            @endif
        </div>

        <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Subir el archivo requerido</h2>

        <!-- Formulario -->
        <form action="{{ request()->fullUrl() }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Errores -->
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-[#BB0808] text-[#BB0808] p-4 mb-6 rounded-r" role="alert">
                    <p class="font-bold">Atención:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Input Archivo -->
            <div class="mb-8">
                <label for="archivo" class="block text-sm font-medium text-gray-700 mb-2">Seleccione el archivo</label>
                <input type="file" name="archivo" id="archivo"
                    class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2.5 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-[#077BA1] file:text-white
                    hover:file:bg-[#06607D]
                    cursor-pointer border border-gray-300 rounded-md bg-white transition"
                    required>
                <p class="mt-2 text-xs text-gray-500">Formatos permitidos: PDF, JPG, PNG. Tamaño máximo: 5MB.</p>
            </div>

            <!-- Botón de Envío -->
            <div class="text-center">
                <button type="submit" class="w-full bg-[#077BA1] hover:bg-[#06607D] text-white font-bold py-3 px-6 rounded-lg text-lg transition duration-300 shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#077BA1]">
                    Enviar Documento
                </button>
            </div>
        </form>

    </div>
</body>
</html>
