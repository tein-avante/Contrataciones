# Plan del Proyecto - Gestión de Contrataciones

## Tecnologías Usadas
- **Backend**: Laravel (PHP), Blade, MySQL.
- **Frontend**: Vue.js 3 con Vite, Pinia, Vue Router, Tailwind CSS.
- **Servidor**: Apache/Nginx (vía .htaccess).

## Estructura del Proyecto
-   `contrataciones-prod/contrataciones-api`: Proyecto Backend de Laravel. Contiene la lógica de API, controladores de empleados y rutas.
-   `contrataciones-app`: Proyecto Frontend en Vue 3. Interfaz de usuario para la gestión de personal.
-   `Raíz`: Archivos de oferta de servicio y documentos auxiliares.

## Resumen del Proyecto
Sistema integral para la gestión de contrataciones y personal. Permite:
- Registro y edición de expedientes de empleados.
- Carga de documentos y ofertas de servicio.
- Gestión de estados de contratación.
- Notificaciones de expiración de documentos.
- Panel administrativo con filtros por departamento.

## Repositorio y Seguridad
- **GitHub**: `tein-avante/Contrataciones`
- **Seguridad**: Archivos sensibles (`.env`, `node_modules`, `vendor`, `storage`) excluidos vía `.gitignore`. Se incluyen archivos `xlsx` y `xls` de ofertas de servicio en la raíz por solicitud del usuario.
