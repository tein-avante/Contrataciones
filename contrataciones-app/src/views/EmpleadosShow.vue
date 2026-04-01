<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import axios from '../lib/axios'
import { formatDate } from '../lib/utils'
import { toast } from 'vue3-toastify' // <-- IMPORTANTE: Importamos toast

// --- Estados Principales ---
const route = useRoute()
const empleadoId = route.params.id
const empleado = ref(null)
const tiposDocumento = ref([])
const isLoading = ref(true)
const apiUrl = import.meta.env.VITE_API_URL

// --- Estados de Formulario de Subida ---
const form = ref({ tipo_documento_id: '', fecha_vencimiento: '', archivo: null })
const errors = ref({})

// --- Estados para Solicitud de Carga ---
const isModalOpen = ref(false)
const solicitudForm = ref({
  documento_id: '',
  tipo_documento_id: '',
  fecha_expiracion: '',
  observacion: '',
})
const solicitudErrors = ref({})

const isNewDocumentRequest = computed(() => !solicitudForm.value.documento_id)

// --- Estados para Rechazo ---
const isRechazoModalOpen = ref(false)
const documentoARechazar = ref(null)
const rechazoForm = ref({ observacion: '' })
const rechazoErrors = ref({})

// --- Estados para Aceptar ---
const isAceptarModalOpen = ref(false)
const documentoAAceptar = ref(null)
const aceptarForm = ref({ fecha_vencimiento: '' })
const aceptarErrors = ref({})

// --- Estados para Secciones Dinámicas (Oferta de Servicio) ---
const activeSection = ref('datos-personales')

// Datos Familiares
const isFamiliarModalOpen = ref(false)
const familiarForm = ref({ id: null, nombre: '', parentesco: '', edad: '', nacionalidad: '', telefono: '' })
const familiarErrors = ref({})

// Estudios
const isEstudioModalOpen = ref(false)
const estudioForm = ref({ id: null, nivel: '', institucion: '', lugar: '', fecha_inicio: '', fecha_culminacion: '', grado_titulo: '' })
const estudioErrors = ref({})

// Cursos
const isCursoModalOpen = ref(false)
const cursoForm = ref({ id: null, nombre_curso: '', institucion: '', fecha: '', horas: '', certificado: false })
const cursoErrors = ref({})

// Idiomas
const isIdiomaModalOpen = ref(false)
const idiomaForm = ref({ id: null, idioma: '', habla: 'Regular', lee: 'Regular', escribe: 'Regular' })
const idiomaErrors = ref({})

// Experiencia Laboral
const isExperienciaModalOpen = ref(false)
const experienciaForm = ref({ id: null, empresa: '', direccion_telefono: '', fecha_ingreso: '', fecha_retiro: '', sueldo_inicial: '', sueldo_final: '', cargo_inicial: '', cargo_final: '', nombre_supervisor: '', motivo_retiro: '' })
const experienciaErrors = ref({})

// Referencias Personales
const isReferenciaModalOpen = ref(false)
const referenciaForm = ref({ id: null, nombre: '', profesion: '', direccion: '', telefono: '' })
const referenciaErrors = ref({})

// --- Funciones de Carga de Datos ---
const fetchData = async () => {
  isLoading.value = true
  try {
    const [empleadoResponse, tiposResponse] = await Promise.all([
      axios.get(`/empleados/${empleadoId}`),
      axios.get('/tipos-documento'),
    ])
    empleado.value = empleadoResponse.data
    tiposDocumento.value = tiposResponse.data

    // Marcar notificaciones como leídas automáticamente al entrar al expediente
    axios.post(`/notifications/empleados/${empleadoId}/read-all`).catch(err => {
      console.error('Error al marcar notificaciones como leídas:', err)
    })
  } catch (error) {
    console.error('Error al cargar datos:', error)
    toast.error('Error al cargar el expediente')
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchData)

// --- Nueva Función: Cambiar Estado de Embarque ---
const cambiarEstadoEmbarque = async (nuevoEstado) => {
  try {
    // Solo permitimos el cambio si es Personal de Buque
    if (empleado.value.tipo_empleado !== 'Personal de Buque') return

    await axios.put(`/empleados/${empleadoId}`, {
      ...empleado.value, // Enviamos el resto de datos para la validación del controlador
      estado_embarque: nuevoEstado,
    })

    empleado.value.estado_embarque = nuevoEstado
    toast.success(`Estado actualizado: ${nuevoEstado}`)
  } catch (error) {
    console.error('Error al cambiar el estado de embarque:', error)
    toast.error('No se pudo actualizar el estado')
  }
}

// --- Funciones: Subida de Archivos ---
const handleFileChange = (event) => {
  form.value.archivo = event.target.files[0]
}

const handleSubmit = async () => {
  errors.value = {}
  const formData = new FormData()
  formData.append('tipo_documento_id', form.value.tipo_documento_id)
  formData.append('fecha_vencimiento', form.value.fecha_vencimiento)
  formData.append('archivo', form.value.archivo)

  try {
    await axios.post(`/empleados/${empleadoId}/documentos`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    // Limpieza
    form.value.tipo_documento_id = ''
    form.value.fecha_vencimiento = ''
    form.value.archivo = null
    document.getElementById('archivo').value = null

    toast.success('Documento subido correctamente') // <-- TOAST ÉXITO
    await fetchData()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
      toast.warn('Por favor revisa los campos') // <-- TOAST ADVERTENCIA
    } else {
      console.error('Error al subir:', error)
      toast.error('Error inesperado al subir el documento') // <-- TOAST ERROR
    }
  }
}

// --- Funciones: Eliminar ---
const eliminarDocumento = async (documentoId) => {
  if (!confirm('¿Está seguro de que desea eliminar este documento?')) return
  try {
    await axios.delete(`/documentos/${documentoId}`)
    toast.success('Documento eliminado correctamente') // <-- TOAST ÉXITO
    await fetchData()
  } catch (error) {
    console.error('Error al eliminar:', error)
    toast.error('No se pudo eliminar el documento') // <-- TOAST ERROR
  }
}

// --- Funciones: Generar Solicitud ---
const handleSolicitudSubmit = async () => {
  solicitudErrors.value = {}
  try {
    await axios.post(`/empleados/${empleadoId}/solicitudes`, solicitudForm.value)
    isModalOpen.value = false
    solicitudForm.value = {
      documento_id: '',
      tipo_documento_id: '',
      fecha_expiracion: '',
      observacion: '',
    }

    toast.success('Solicitud enviada al empleado') // <-- TOAST ÉXITO
    await fetchData()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      solicitudErrors.value = error.response.data.errors
      toast.warn('Revisa los datos de la solicitud')
    } else {
      console.error('Error al crear solicitud:', error)
      toast.error('Error al crear la solicitud')
    }
  }
}

// --- Funciones: Rechazar Documento ---
const abrirModalRechazo = (documento) => {
  documentoARechazar.value = documento
  rechazoForm.value.observacion = ''
  rechazoErrors.value = {}
  isRechazoModalOpen.value = true
}

const handleRechazoSubmit = async () => {
  if (!documentoARechazar.value) return
  rechazoErrors.value = {}
  try {
    await axios.post(`/documentos/${documentoARechazar.value.id}/rechazar`, rechazoForm.value)
    isRechazoModalOpen.value = false

    toast.info('Documento rechazado y notificado') // <-- TOAST INFO
    await fetchData()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      rechazoErrors.value = error.response.data.errors
    } else {
      console.error('Error al rechazar:', error)
      toast.error('Error al rechazar el documento')
    }
  }
}

// --- Funciones: Aceptar Documento ---
const abrirModalAceptar = (documento) => {
  documentoAAceptar.value = documento
  aceptarForm.value.fecha_vencimiento = documento.fecha_vencimiento || ''
  aceptarErrors.value = {}
  isAceptarModalOpen.value = true
}

const handleAceptarSubmit = async () => {
  if (!documentoAAceptar.value) return
  aceptarErrors.value = {}
  try {
    await axios.post(`/documentos/${documentoAAceptar.value.id}/aceptar`, {
      fecha_vencimiento: aceptarForm.value.fecha_vencimiento,
    })
    isAceptarModalOpen.value = false

    toast.success('Documento aceptado y vigente') // <-- TOAST ÉXITO
    await fetchData()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      aceptarErrors.value = error.response.data.errors
    } else {
      console.error('Error al aceptar:', error)
      toast.error('Error al aceptar el documento')
    }
  }
}

const storageUrl = (path) => {
  let baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  let storageBase = baseUrl
  storageBase = storageBase.endsWith('/') ? storageBase.slice(0, -1) : storageBase
  return `${storageBase}/storage/${path}`
}

// --- Métodos Genéricos para Secciones Dinámicas ---

const openModal = (section, item = null) => {
  if (section === 'familiar') {
    familiarForm.value = item ? { ...item } : { id: null, nombre: '', parentesco: '', edad: '', nacionalidad: '', telefono: '' }
    familiarErrors.value = {}
    isFamiliarModalOpen.value = true
  } else if (section === 'estudio') {
    estudioForm.value = item ? { ...item } : { id: null, nivel: '', institucion: '', lugar: '', fecha_inicio: '', fecha_culminacion: '', grado_titulo: '' }
    estudioErrors.value = {}
    isEstudioModalOpen.value = true
  } else if (section === 'curso') {
    cursoForm.value = item ? { ...item } : { id: null, nombre_curso: '', institucion: '', fecha: '', horas: '', certificado: false }
    cursoErrors.value = {}
    isCursoModalOpen.value = true
  } else if (section === 'idioma') {
    idiomaForm.value = item ? { ...item } : { id: null, idioma: '', habla: 'Regular', lee: 'Regular', escribe: 'Regular' }
    idiomaErrors.value = {}
    isIdiomaModalOpen.value = true
  } else if (section === 'experiencia') {
    experienciaForm.value = item ? { ...item } : { id: null, empresa: '', direccion_telefono: '', fecha_ingreso: '', fecha_retiro: '', sueldo_inicial: '', sueldo_final: '', cargo_inicial: '', cargo_final: '', nombre_supervisor: '', motivo_retiro: '' }
    experienciaErrors.value = {}
    isExperienciaModalOpen.value = true
  } else if (section === 'referencia') {
    referenciaForm.value = item ? { ...item } : { id: null, nombre: '', profesion: '', direccion: '', telefono: '' }
    referenciaErrors.value = {}
    isReferenciaModalOpen.value = true
  }
}

const saveSection = async (section) => {
  let url = ''
  let method = 'post'
  let data = {}
  let errorsRef = null
  let modalRef = null

  if (section === 'familiar') {
    url = familiarForm.value.id ? `/datos-familiares/${familiarForm.value.id}` : `/empleados/${empleadoId}/datos-familiares`
    method = familiarForm.value.id ? 'put' : 'post'
    data = familiarForm.value
    errorsRef = familiarErrors
    modalRef = isFamiliarModalOpen
  } else if (section === 'estudio') {
    url = estudioForm.value.id ? `/estudios/${estudioForm.value.id}` : `/empleados/${empleadoId}/estudios`
    method = estudioForm.value.id ? 'put' : 'post'
    data = estudioForm.value
    errorsRef = estudioErrors
    modalRef = isEstudioModalOpen
  } else if (section === 'curso') {
    url = cursoForm.value.id ? `/cursos-eventos/${cursoForm.value.id}` : `/empleados/${empleadoId}/cursos-eventos`
    method = cursoForm.value.id ? 'put' : 'post'
    data = cursoForm.value
    errorsRef = cursoErrors
    modalRef = isCursoModalOpen
  } else if (section === 'idioma') {
    url = idiomaForm.value.id ? `/idiomas/${idiomaForm.value.id}` : `/empleados/${empleadoId}/idiomas`
    method = idiomaForm.value.id ? 'put' : 'post'
    data = idiomaForm.value
    errorsRef = idiomaErrors
    modalRef = isIdiomaModalOpen
  } else if (section === 'experiencia') {
    url = experienciaForm.value.id ? `/experiencias-laborales/${experienciaForm.value.id}` : `/empleados/${empleadoId}/experiencias-laborales`
    method = experienciaForm.value.id ? 'put' : 'post'
    data = experienciaForm.value
    errorsRef = experienciaErrors
    modalRef = isExperienciaModalOpen
  } else if (section === 'referencia') {
    url = referenciaForm.value.id ? `/referencias-personales/${referenciaForm.value.id}` : `/empleados/${empleadoId}/referencias-personales`
    method = referenciaForm.value.id ? 'put' : 'post'
    data = referenciaForm.value
    errorsRef = referenciaErrors
    modalRef = isReferenciaModalOpen
  }

  try {
    if (method === 'post') {
      await axios.post(url, data)
    } else {
      await axios.put(url, data)
    }
    modalRef.value = false
    toast.success('Información guardada correctamente')
    await fetchData()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errorsRef.value = error.response.data.errors
    } else {
      toast.error('Error al guardar la información')
    }
  }
}

const deleteItem = async (section, id) => {
  if (!confirm('¿Está seguro de eliminar este registro?')) return
  let url = ''
  if (section === 'familiar') url = `/datos-familiares/${id}`
  else if (section === 'estudio') url = `/estudios/${id}`
  else if (section === 'curso') url = `/cursos-eventos/${id}`
  else if (section === 'idioma') url = `/idiomas/${id}`
  else if (section === 'experiencia') url = `/experiencias-laborales/${id}`
  else if (section === 'referencia') url = `/referencias-personales/${id}`

  try {
    await axios.delete(url)
    toast.success('Registro eliminado')
    await fetchData()
  } catch (error) {
    toast.error('No se pudo eliminar el registro')
  }
}
</script>

<template>
  <AppLayout :title="empleado ? 'Expediente de ' + empleado.nombre : 'Cargando...'">
    <!-- CABECERA -->
    <template #header>
      <div v-if="empleado" class="flex flex-col md:flex-row md:justify-between md:items-center">
        <div>
          <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Expediente: <span class="text-indigo-600">{{ empleado.nombre }}</span>
          </h2>
          <p class="text-sm text-gray-500 mt-1">{{ empleado.cargo || 'Sin cargo definido' }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-4">
          <!-- Selector de Estado de Embarque (Solo para Personal de Buque) -->
          <div v-if="empleado.tipo_empleado === 'Personal de Buque'" class="flex items-center bg-white p-1 rounded-lg shadow-sm border border-gray-200">
            <button
              @click="cambiarEstadoEmbarque('En tierra')"
              class="px-3 py-1 text-xs font-bold rounded-md transition duration-200"
              :class="empleado.estado_embarque === 'En tierra' ? 'bg-amber-100 text-amber-800 shadow-inner' : 'text-gray-400 hover:text-gray-600'"
            >
              🏠 En tierra
            </button>
            <button
              @click="cambiarEstadoEmbarque('A bordo')"
              class="px-3 py-1 text-xs font-bold rounded-md transition duration-200"
              :class="empleado.estado_embarque === 'A bordo' ? 'bg-blue-100 text-blue-800 shadow-inner' : 'text-gray-400 hover:text-gray-600'"
            >
              🚢 A bordo
            </button>
          </div>

          <span
            class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-wider"
          >
            {{ empleado.tipo_empleado }}
          </span>

          <!-- Botón Generar PDF -->
          <a
            :href="`${apiUrl}/empleados/${empleado.id}/pdf`"
            target="_blank"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 transition shadow-md whitespace-nowrap"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Generar PDF
          </a>
        </div>
      </div>
      <div v-else class="animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-1/3 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-1/4"></div>
      </div>
    </template>

    <!-- Estado de Carga -->
    <div v-if="isLoading" class="flex justify-center items-center min-h-screen pb-32">
      <div
        class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-indigo-500"
      ></div>
    </div>

    <!-- Contenido Principal -->
    <div v-else-if="empleado" class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- NAVEGACIÓN DE PESTAÑAS -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 overflow-x-auto pb-1" aria-label="Tabs">
            <button 
              v-for="tab in [
                { id: 'datos-personales', name: 'Datos Personales', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
                { id: 'datos-familiares', name: 'Familiares', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
                { id: 'estudios-cursos', name: 'Estudios y Cursos', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 5.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
                { id: 'idiomas', name: 'Idiomas', icon: 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129' },
                { id: 'experiencia', name: 'Experiencia', icon: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
                { id: 'referencias', name: 'Referencias', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
                { id: 'documentos', name: 'Documentos', icon: 'M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13' },
              ]"
              :key="tab.id"
              @click="activeSection = tab.id"
              class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition duration-200"
              :class="activeSection === tab.id 
                ? 'border-indigo-500 text-indigo-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            >
              <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
              </svg>
              {{ tab.name }}
            </button>
          </nav>
        </div>
        <!-- SECCIÓN 0: INFORMACIÓN PERSONAL -->
        <div v-show="activeSection === 'datos-personales'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center border-b border-gray-100 pb-4 mb-6">
              <h3 class="text-lg font-bold text-gray-900">Información Personal Detallada</h3>
              <router-link :to="{ name: 'empleados.edit', params: { id: empleado.id }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar Perfil
              </router-link>
            </div>
            
            <div class="space-y-8">
              <!-- Foto y Identificación -->
              <div class="flex flex-col md:flex-row gap-8 items-start">
                <div v-if="empleado.foto" class="flex-shrink-0">
                  <img :src="storageUrl(empleado.foto)" alt="Foto Perfil" class="w-32 h-40 object-cover rounded-lg shadow-md border-2 border-indigo-100">
                </div>
                <div class="flex-grow w-full">
                  <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4">Identificación y Ubicación</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cédula</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.cedula || 'N/R' }}</p>
                  </div>
                  <div v-if="empleado.tipo_empleado === 'Personal de Buque'" class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cédula Marina</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.cedula_marina || 'N/R' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nacionalidad</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.nacionalidad || 'N/R' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ciudad / Estado</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.ciudad || 'N/R' }}</p>
                  </div>
                  <div class="md:col-span-2 space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Correo Electrónico</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.email || 'N/R' }}</p>
                  </div>
                  <div class="md:col-span-2 space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dirección de Habitación</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.direccion || 'No registrada' }}</p>
                  </div>
                </div>
              </div>
            </div>

              <!-- Bloque: Datos Biográficos y Salud -->
              <div class="border-t border-gray-50 pt-6">
                <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4">Datos Biográficos y Salud</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lugar de Nacimiento</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.lugar_nacimiento || 'N/R' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha de Nacimiento</p>
                    <p class="text-sm font-medium text-gray-900">{{ formatDate(empleado.fecha_nacimiento) || 'N/R' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado Civil</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.estado_civil || 'N/R' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipo de Sangre</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.tipo_sangre || 'N/R' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Estatura / Peso</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.estatura || '-' }} / {{ empleado.peso || '-' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">¿Tiene Hijos?</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.tiene_hijos }}</p>
                  </div>
                </div>
              </div>

              <!-- Bloque: Otros Datos y Tallas -->
              <div class="border-t border-gray-50 pt-6">
                <h4 class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-4">Otros Datos y Tallas</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Colegiación N°</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.colegiacion_nro || 'N/A' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Licencia Conducir</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.licencia_conductor_nro || 'N/A' }}</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Vivienda</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.tipo_habitacion || '-' }} ({{ empleado.caracteristicas_habitacion || '-' }})</p>
                  </div>
                  <div class="space-y-1">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tallas (P/C/Z)</p>
                    <p class="text-sm font-medium text-gray-900">{{ empleado.talla_pantalon || '-' }} / {{ empleado.talla_camisa || '-' }} / {{ empleado.talla_zapato || '-' }}</p>
                  </div>
                </div>
              </div>

              <!-- Bloque: Habilidades -->
              <div v-if="empleado.habilidades_destrezas" class="border-t border-gray-50 pt-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Habilidades y Destrezas</p>
                <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-lg italic">"{{ empleado.habilidades_destrezas }}"</p>
              </div>
            </div>
          </div>
        </div>

        <!-- SECCIÓN: DATOS FAMILIARES -->
        <div v-show="activeSection === 'datos-familiares'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-bold text-gray-900">Datos Familiares</h3>
              <button @click="openModal('familiar')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-indigo-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar Familiar
              </button>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 uppercase text-[10px] tracking-wider font-bold text-gray-500">
                  <tr>
                    <th class="px-6 py-3 text-left">Nombre</th>
                    <th class="px-6 py-3 text-left">Parentesco</th>
                    <th class="px-6 py-3 text-left">Edad</th>
                    <th class="px-6 py-3 text-left">Nacionalidad</th>
                    <th class="px-6 py-3 text-left">Teléfono</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <tr v-for="fam in empleado.datos_familiares" :key="fam.id" class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ fam.nombre }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ fam.parentesco }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ fam.edad }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ fam.nacionalidad }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ fam.telefono }}</td>
                    <td class="px-6 py-4 text-right">
                      <button @click="openModal('familiar', fam)" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                      <button @click="deleteItem('familiar', fam.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                    </td>
                  </tr>
                  <tr v-if="!empleado.datos_familiares?.length">
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">No hay datos registrados</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- SECCIÓN: ESTUDIOS Y CURSOS -->
        <div v-show="activeSection === 'estudios-cursos'" class="space-y-6">
          <!-- Estudios -->
          <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
            <div class="p-6 sm:p-8">
              <div class="flex justify-between items-center mb-6 text-gray-900">
                <h3 class="text-lg font-bold">Estudios Realizados</h3>
                <button @click="openModal('estudio')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-indigo-700 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Agregar Estudio
                </button>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50 uppercase text-[10px] tracking-wider font-bold text-gray-500 text-left">
                    <tr>
                      <th class="px-6 py-3">Nivel</th>
                      <th class="px-6 py-3">Institución</th>
                      <th class="px-6 py-3">Grado/Título</th>
                      <th class="px-6 py-3">Fecha Inicio / Fin</th>
                      <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 text-sm">
                    <tr v-for="est in empleado.estudios" :key="est.id" class="hover:bg-gray-50/50 transition text-gray-600">
                      <td class="px-6 py-4 font-medium text-gray-900">{{ est.nivel }}</td>
                      <td class="px-6 py-4">{{ est.institucion }}</td>
                      <td class="px-6 py-4">{{ est.grado_titulo }}</td>
                      <td class="px-6 py-4">{{ formatDate(est.fecha_inicio) }} - {{ formatDate(est.fecha_culminacion) }}</td>
                      <td class="px-6 py-4 text-right">
                        <button @click="openModal('estudio', est)" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                        <button @click="deleteItem('estudio', est.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                      </td>
                    </tr>
                    <tr v-if="!empleado.estudios?.length">
                      <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">No hay estudios registrados</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Cursos -->
          <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
            <div class="p-6 sm:p-8">
              <div class="flex justify-between items-center mb-6 text-gray-900">
                <h3 class="text-lg font-bold">Cursos y Eventos</h3>
                <button @click="openModal('curso')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-indigo-700 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Agregar Curso
                </button>
              </div>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                  <thead class="bg-gray-50 uppercase text-[10px] tracking-wider font-bold text-gray-500">
                    <tr>
                      <th class="px-6 py-3">Nombre del Curso</th>
                      <th class="px-6 py-3">Institución</th>
                      <th class="px-6 py-3 text-center">Horas</th>
                      <th class="px-6 py-3 text-center">Certificado</th>
                      <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 text-sm">
                    <tr v-for="cur in empleado.cursos_eventos" :key="cur.id" class="hover:bg-gray-50/50 transition text-gray-600">
                      <td class="px-6 py-4 font-medium text-gray-900">{{ cur.nombre_curso }}</td>
                      <td class="px-6 py-4">{{ cur.institucion }}</td>
                      <td class="px-6 py-4 text-center">{{ cur.horas }}</td>
                      <td class="px-6 py-4 text-center">
                        <span v-if="cur.certificado" class="text-green-500 font-bold">Sí</span>
                        <span v-else class="text-gray-400">No</span>
                      </td>
                      <td class="px-6 py-4 text-right">
                        <button @click="openModal('curso', cur)" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                        <button @click="deleteItem('curso', cur.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                      </td>
                    </tr>
                    <tr v-if="!empleado.cursos_eventos?.length">
                      <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">No hay cursos registrados</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- SECCIÓN: IDIOMAS -->
        <div v-show="activeSection === 'idiomas'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-bold text-gray-900">Conocimiento de Idiomas</h3>
              <button @click="openModal('idioma')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-indigo-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar Idioma
              </button>
            </div>
            <div class="overflow-x-auto text-left">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 uppercase text-[10px] tracking-wider font-bold text-gray-500">
                  <tr>
                    <th class="px-6 py-3">Idioma</th>
                    <th class="px-6 py-3 text-center">Habla</th>
                    <th class="px-6 py-3 text-center">Lee</th>
                    <th class="px-6 py-3 text-center">Escribe</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <tr v-for="idi in empleado.idiomas" :key="idi.id" class="hover:bg-gray-50/50 transition text-gray-600">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ idi.idioma }}</td>
                    <td class="px-6 py-4 text-center">{{ idi.habla }}</td>
                    <td class="px-6 py-4 text-center">{{ idi.lee }}</td>
                    <td class="px-6 py-4 text-center">{{ idi.escribe }}</td>
                    <td class="px-6 py-4 text-right">
                      <button @click="openModal('idioma', idi)" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                      <button @click="deleteItem('idioma', idi.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                    </td>
                  </tr>
                  <tr v-if="!empleado.idiomas?.length">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">No hay idiomas registrados</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- SECCIÓN: EXPERIENCIA LABORAL -->
        <div v-show="activeSection === 'experiencia'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-bold text-gray-900">Experiencia Laboral</h3>
              <button @click="openModal('experiencia')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-indigo-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar Experiencia
              </button>
            </div>
            <div class="overflow-x-auto text-left">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 uppercase text-[10px] tracking-wider font-bold text-gray-500">
                  <tr>
                    <th class="px-6 py-3">Empresa</th>
                    <th class="px-6 py-3">Cargos</th>
                    <th class="px-6 py-3">Periodo</th>
                    <th class="px-6 py-3">Motivo Retiro</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <tr v-for="exp in empleado.experiencias_laborales" :key="exp.id" class="hover:bg-gray-50/50 transition text-gray-600">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900">{{ exp.empresa }}</div>
                      <div class="text-xs text-gray-400">{{ exp.direccion_telefono }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-gray-900">{{ exp.cargo_final }}</div>
                      <div v-if="exp.cargo_inicial && exp.cargo_inicial !== exp.cargo_final" class="text-xs text-gray-400">Inició como: {{ exp.cargo_inicial }}</div>
                    </td>
                    <td class="px-6 py-4">{{ formatDate(exp.fecha_ingreso) }} al {{ formatDate(exp.fecha_retiro) }}</td>
                    <td class="px-6 py-4 truncate max-w-xs">{{ exp.motivo_retiro }}</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <button @click="openModal('experiencia', exp)" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                      <button @click="deleteItem('experiencia', exp.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                    </td>
                  </tr>
                  <tr v-if="!empleado.experiencias_laborales?.length">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">No hay registros de experiencia</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- SECCIÓN: REFERENCIAS -->
        <div v-show="activeSection === 'referencias'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-bold text-gray-900">Referencias Personales</h3>
              <button @click="openModal('referencia')" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center hover:bg-indigo-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar Referencia
              </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="ref in empleado.referencias_personales" :key="ref.id" class="bg-gray-50 p-6 rounded-xl border border-gray-100 relative group">
                <div class="flex justify-between items-start mb-4">
                  <div class="h-10 w-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                    {{ ref.nombre.charAt(0) }}
                  </div>
                  <div class="flex opacity-0 group-hover:opacity-100 transition duration-200">
                    <button @click="openModal('referencia', ref)" class="text-indigo-600 mr-2"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></button>
                    <button @click="deleteItem('referencia', ref.id)" class="text-red-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                  </div>
                </div>
                <h4 class="font-bold text-gray-900">{{ ref.nombre }}</h4>
                <p class="text-xs text-indigo-600 mb-4">{{ ref.profesion || 'Profesión no especificada' }}</p>
                <div class="space-y-2 text-sm text-gray-500">
                  <div class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    {{ ref.direccion || 'Sin dirección' }}
                  </div>
                  <div class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    {{ ref.telefono || 'Sin teléfono' }}
                  </div>
                </div>
              </div>
              <div v-if="!empleado.referencias_personales?.length" class="md:col-span-3 text-center py-12 text-gray-400 italic">No hay referencias registradas</div>
            </div>
          </div>
        </div>

        <!-- SECCIÓN 1: FORMULARIO DE SUBIDA -->
        <div v-show="activeSection === 'documentos'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="p-6 sm:p-8">
            <div
              class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-4 mb-6 gap-4"
            >
              <div>
                <h3 class="text-lg font-bold text-gray-900">Subir Nuevo Documento</h3>
                <p class="text-sm text-gray-500">Sube un archivo directamente al expediente.</p>
              </div>

              <button
                @click="isModalOpen = true"
                class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 active:bg-orange-700 focus:outline-none transition shadow-md whitespace-nowrap"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 mr-2"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                  />
                </svg>
                Generar Solicitud
              </button>
            </div>

            <form @submit.prevent="handleSubmit">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label
                    for="tipo_documento_id"
                    class="block text-sm font-medium text-gray-700 mb-1"
                    >Tipo de Documento</label
                  >
                  <select
                    v-model="form.tipo_documento_id"
                    id="tipo_documento_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    required
                  >
                    <option value="" disabled>Seleccione...</option>
                    <option v-for="tipo in tiposDocumento" :key="tipo.id" :value="tipo.id">
                      {{ tipo.nombre }}
                    </option>
                  </select>
                  <p v-if="errors.tipo_documento_id" class="text-sm text-red-600 mt-1">
                    {{ errors.tipo_documento_id[0] }}
                  </p>
                </div>
                <div>
                  <label
                    for="fecha_vencimiento"
                    class="block text-sm font-medium text-gray-700 mb-1"
                    >Vencimiento <span class="text-gray-400">(Opcional)</span></label
                  >
                  <input
                    type="date"
                    v-model="form.fecha_vencimiento"
                    id="fecha_vencimiento"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                <div>
                  <label for="archivo" class="block text-sm font-medium text-gray-700 mb-1"
                    >Archivo</label
                  >
                  <input
                    type="file"
                    @change="handleFileChange"
                    id="archivo"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                    required
                  />
                  <p v-if="errors.archivo" class="text-sm text-red-600 mt-1">
                    {{ errors.archivo[0] }}
                  </p>
                </div>
              </div>
              <div class="mt-6 flex justify-end">
                <button
                  type="submit"
                  class="bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 transition shadow-sm"
                >
                  Subir Documento
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- SECCIÓN 2: TABLA DE DOCUMENTOS -->
        <div v-show="activeSection === 'documentos'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Documentos Adjuntos</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Tipo
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Estado
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Subido
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Vencimiento
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="empleado.documentos.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                    No hay documentos adjuntos.
                  </td>
                </tr>
                <tr
                  v-for="documento in empleado.documentos"
                  :key="documento.id"
                  class="hover:bg-gray-50 transition"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ documento.tipo_documento.nombre }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': documento.estado_visual === 'Vigente',
                        'bg-yellow-100 text-yellow-800':
                          documento.estado_visual === 'En Revisión' ||
                          documento.estado_visual === 'Por Vencer',
                        'bg-red-100 text-red-800':
                          documento.estado_visual === 'Rechazado' ||
                          documento.estado_visual === 'Vencido',
                      }"
                    >
                      {{ documento.estado_visual }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(documento.updated_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(documento.fecha_vencimiento) }}
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div v-if="documento.estado === 'En Revisión'" class="inline-flex mr-2">
                      <button
                        @click="abrirModalAceptar(documento)"
                        class="text-green-600 hover:text-green-900 mr-2 font-semibold"
                      >
                        Aceptar
                      </button>
                      <button
                        @click="abrirModalRechazo(documento)"
                        class="text-red-600 hover:text-red-900 font-semibold"
                      >
                        Rechazar
                      </button>
                      <span class="text-gray-300 mx-2">|</span>
                    </div>
                    <a
                      :href="storageUrl(documento.archivo)"
                      target="_blank"
                      class="text-indigo-600 hover:text-indigo-900 mr-2"
                      >Ver
                    </a>
                    <button
                      @click="eliminarDocumento(documento.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- SECCIÓN 3: HISTORIAL DE SOLICITUDES -->
        <div v-show="activeSection === 'documentos'" class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
          <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
            <h3 class="text-lg font-bold text-gray-900">Historial de Solicitudes</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Ticket
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Estado
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Fecha Solicitud
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Fecha Límite
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-if="empleado.solicitudes_carga.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">
                    No hay solicitudes registradas.
                  </td>
                </tr>
                <tr
                  v-for="solicitud in empleado.solicitudes_carga"
                  :key="solicitud.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 whitespace-nowrap font-mono text-xs text-gray-600">
                    {{ solicitud.ticket }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="{
                        'bg-yellow-100 text-yellow-800': solicitud.estado === 'Pendiente',
                        'bg-green-100 text-green-800': solicitud.estado === 'Completada',
                      }"
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    >
                      {{ solicitud.estado }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(solicitud.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(solicitud.fecha_expiracion) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- MODAL: FAMILIAR -->
      <div v-if="isFamiliarModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity shadow-2xl" @click="isFamiliarModalOpen = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between text-white">
              <h3 class="text-lg font-bold">{{ familiarForm.id ? 'Editar' : 'Agregar' }} Familiar</h3>
              <button @click="isFamiliarModalOpen = false"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form @submit.prevent="saveSection('familiar')" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre Completo</label>
                <input v-model="familiarForm.nombre" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                <p v-if="familiarErrors.nombre" class="text-xs text-red-600 mt-1">{{ familiarErrors.nombre[0] }}</p>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Parentesco</label>
                  <input v-model="familiarForm.parentesco" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Edad</label>
                  <input v-model="familiarForm.edad" type="number" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Nacionalidad</label>
                  <input v-model="familiarForm.nacionalidad" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                  <input v-model="familiarForm.telefono" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
                </div>
              </div>
              <div class="pt-4 flex justify-end space-x-3">
                <button type="button" @click="isFamiliarModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- MODAL: ESTUDIO -->
      <div v-if="isEstudioModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity shadow-2xl" @click="isEstudioModalOpen = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between text-white">
              <h3 class="text-lg font-bold">{{ estudioForm.id ? 'Editar' : 'Agregar' }} Estudio</h3>
              <button @click="isEstudioModalOpen = false"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form @submit.prevent="saveSection('estudio')" class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Nivel</label>
                  <select v-model="estudioForm.nivel" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                    <option value="Primaria">Primaria</option>
                    <option value="Bachillerato">Bachillerato</option>
                    <option value="Técnico">Técnico</option>
                    <option value="Universitario">Universitario</option>
                    <option value="Post-Grado">Post-Grado</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Grado o Título</label>
                  <input v-model="estudioForm.grado_titulo" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Institución</label>
                <input v-model="estudioForm.institucion" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lugar</label>
                <input v-model="estudioForm.lugar" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Inicio</label>
                  <input v-model="estudioForm.fecha_inicio" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Culminación</label>
                  <input v-model="estudioForm.fecha_culminacion" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
              </div>
              <div class="pt-4 flex justify-end space-x-3">
                <button type="button" @click="isEstudioModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- MODAL: IDIOMA -->
      <div v-if="isIdiomaModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity shadow-2xl" @click="isIdiomaModalOpen = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between text-white">
              <h3 class="text-lg font-bold">{{ idiomaForm.id ? 'Editar' : 'Agregar' }} Idioma</h3>
              <button @click="isIdiomaModalOpen = false"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form @submit.prevent="saveSection('idioma')" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Idioma</label>
                <input v-model="idiomaForm.idioma" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
              </div>
              <div class="grid grid-cols-3 gap-2">
                <div v-for="skill in ['habla', 'lee', 'escribe']" :key="skill">
                  <label class="block text-xs font-bold text-gray-400 uppercase mb-1">{{ skill }}</label>
                  <select v-model="idiomaForm[skill]" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 text-sm">
                    <option value="Bien">Bien</option>
                    <option value="Regular">Regular</option>
                    <option value="Poco">Poco</option>
                  </select>
                </div>
              </div>
              <div class="pt-4 flex justify-end space-x-3">
                <button type="button" @click="isIdiomaModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- MODAL: CURSO -->
      <div v-if="isCursoModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity shadow-2xl" @click="isCursoModalOpen = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between text-white">
              <h3 class="text-lg font-bold">{{ cursoForm.id ? 'Editar' : 'Agregar' }} Curso</h3>
              <button @click="isCursoModalOpen = false"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form @submit.prevent="saveSection('curso')" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre del Curso / Evento</label>
                <input v-model="cursoForm.nombre_curso" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Institución</label>
                <input v-model="cursoForm.institucion" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
              </div>
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
                  <input v-model="cursoForm.fecha" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Horas</label>
                  <input v-model="cursoForm.horas" type="number" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div class="flex items-end pb-2">
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input v-model="cursoForm.certificado" type="checkbox" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm font-semibold text-gray-700">Certificado</span>
                  </label>
                </div>
              </div>
              <div class="pt-4 flex justify-end space-x-3">
                <button type="button" @click="isCursoModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- MODAL: EXPERIENCIA -->
      <div v-if="isExperienciaModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity shadow-2xl" @click="isExperienciaModalOpen = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between text-white">
              <h3 class="text-lg font-bold">{{ experienciaForm.id ? 'Editar' : 'Agregar' }} Experiencia Laboral</h3>
              <button @click="isExperienciaModalOpen = false"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form @submit.prevent="saveSection('experiencia')" class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Empresa</label>
                  <input v-model="experienciaForm.empresa" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección / Teléfono</label>
                  <input v-model="experienciaForm.direccion_telefono" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Cargo Inicial</label>
                  <input v-model="experienciaForm.cargo_inicial" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Cargo Final</label>
                  <input v-model="experienciaForm.cargo_final" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Ingreso</label>
                  <input v-model="experienciaForm.fecha_ingreso" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Retiro</label>
                  <input v-model="experienciaForm.fecha_retiro" type="date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Sueldo Inicial</label>
                  <input v-model="experienciaForm.sueldo_inicial" type="number" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
                </div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-1">Sueldo Final</label>
                  <input v-model="experienciaForm.sueldo_final" type="number" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
                </div>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre Supervisor e Inmediato</label>
                <input v-model="experienciaForm.nombre_supervisor" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Motivo de Retiro</label>
                <textarea v-model="experienciaForm.motivo_retiro" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required></textarea>
              </div>
              <div class="pt-4 flex justify-end space-x-3">
                <button type="button" @click="isExperienciaModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- MODAL: REFERENCIA -->
      <div v-if="isReferenciaModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity shadow-2xl" @click="isReferenciaModalOpen = false"></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between text-white">
              <h3 class="text-lg font-bold">{{ referenciaForm.id ? 'Editar' : 'Agregar' }} Referencia Personal</h3>
              <button @click="isReferenciaModalOpen = false"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
            <form @submit.prevent="saveSection('referencia')" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre Completo</label>
                <input v-model="referenciaForm.nombre" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500" required>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Profesión</label>
                <input v-model="referenciaForm.profesion" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección</label>
                <input v-model="referenciaForm.direccion" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                <input v-model="referenciaForm.telefono" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500">
              </div>
              <div class="pt-4 flex justify-end space-x-3">
                <button type="button" @click="isReferenciaModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">Cancelar</button>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-indigo-700 transition">Guardar</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- MODAL 1: GENERAR SOLICITUD -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div
          class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"
            @click="isModalOpen = false"
          ></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

          <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
              <div class="sm:flex sm:items-start">
                <div
                  class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10"
                >
                  <svg
                    class="h-6 w-6 text-indigo-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Generar Solicitud de Carga
                  </h3>

                  <form @submit.prevent="handleSolicitudSubmit" class="mt-6 space-y-5">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1"
                        >¿Qué documento?</label
                      >
                      <select
                        v-model="solicitudForm.documento_id"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      >
                        <option value="">(Solicitar un documento NUEVO)</option>
                        <option v-for="doc in empleado.documentos" :key="doc.id" :value="doc.id">
                          Actualizar: {{ doc.tipo_documento.nombre }}
                        </option>
                      </select>
                    </div>

                    <div
                      v-if="isNewDocumentRequest"
                      class="bg-indigo-50 p-4 rounded-md border border-indigo-100 animate-pulse-once"
                    >
                      <label class="block text-sm font-medium text-indigo-700 mb-1"
                        >Seleccione el Tipo de Documento Nuevo</label
                      >
                      <select
                        v-model="solicitudForm.tipo_documento_id"
                        class="w-full border-indigo-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white"
                        :required="isNewDocumentRequest"
                      >
                        <option value="" disabled>Seleccione el tipo...</option>
                        <option v-for="tipo in tiposDocumento" :key="tipo.id" :value="tipo.id">
                          {{ tipo.nombre }}
                        </option>
                      </select>
                      <p v-if="solicitudErrors.tipo_documento_id" class="text-xs text-red-600 mt-1">
                        {{ solicitudErrors.tipo_documento_id[0] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Fecha Límite</label
                      >
                      <input
                        type="date"
                        v-model="solicitudForm.fecha_expiracion"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required
                      />
                      <p v-if="solicitudErrors.fecha_expiracion" class="text-xs text-red-600 mt-1">
                        {{ solicitudErrors.fecha_expiracion[0] }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Observaciones</label
                      >
                      <textarea
                        v-model="solicitudForm.observacion"
                        rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Instrucciones..."
                      ></textarea>
                    </div>

                    <div
                      class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense"
                    >
                      <button
                        type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm transition"
                      >
                        Generar Solicitud
                      </button>
                      <button
                        type="button"
                        @click="isModalOpen = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm transition"
                      >
                        Cancelar
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL 2: RECHAZAR DOCUMENTO -->
      <div v-if="isRechazoModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div
          class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"
            @click="isRechazoModalOpen = false"
          ></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

          <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div
                  class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                >
                  <svg
                    class="h-6 w-6 text-red-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">Rechazar Documento</h3>
                  <form @submit.prevent="handleRechazoSubmit" class="mt-4">
                    <p class="text-sm text-gray-500 mb-4">
                      Por favor, indica el motivo. Se generará una nueva solicitud.
                    </p>
                    <textarea
                      v-model="rechazoForm.observacion"
                      rows="4"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"
                      required
                      placeholder="Motivo..."
                    ></textarea>
                    <p v-if="rechazoErrors.observacion" class="text-xs text-red-600 mt-1">
                      {{ rechazoErrors.observacion[0] }}
                    </p>

                    <div
                      class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense"
                    >
                      <button
                        type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:col-start-2 sm:text-sm"
                      >
                        Confirmar
                      </button>
                      <button
                        type="button"
                        @click="isRechazoModalOpen = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm"
                      >
                        Cancelar
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL 3: ACEPTAR DOCUMENTO -->
      <div v-if="isAceptarModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
        <div
          class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"
            @click="isAceptarModalOpen = false"
          ></div>
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

          <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div
                  class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10"
                >
                  <svg
                    class="h-6 w-6 text-green-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900">Aceptar Documento</h3>
                  <form @submit.prevent="handleAceptarSubmit" class="mt-4">
                    <p class="text-sm text-gray-500 mb-4">
                      Confirma o actualiza la fecha de vencimiento para que el documento sea
                      <strong>Vigente</strong>.
                    </p>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Fecha de Vencimiento</label
                    >
                    <input
                      type="date"
                      v-model="aceptarForm.fecha_vencimiento"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                      required
                    />
                    <p v-if="aceptarErrors.fecha_vencimiento" class="text-xs text-red-600 mt-1">
                      {{ aceptarErrors.fecha_vencimiento[0] }}
                    </p>

                    <div
                      class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense"
                    >
                      <button
                        type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:col-start-2 sm:text-sm"
                      >
                        Aceptar y Guardar
                      </button>
                      <button
                        type="button"
                        @click="isAceptarModalOpen = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm"
                      >
                        Cancelar
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
