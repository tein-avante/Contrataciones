<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import axios from '../lib/axios'
import { toast } from 'vue3-toastify' // <-- IMPORTAR TOAST

// --- Estados ---
const tipos = ref([])
const isLoading = ref(true)
const isModalOpen = ref(false)
const isEditing = ref(false) // Para saber si estamos editando o creando

// Formulario
const form = ref({
  id: null,
  nombre: '',
  periodo_alerta: 60, // Valor por defecto sugerido
})
const errors = ref({})

// --- Cargar Datos ---
const fetchTipos = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/tipos-documento')
    tipos.value = response.data
  } catch (error) {
    console.error('Error:', error)
    toast.error('Error al cargar los tipos de documento') // <-- TOAST ERROR
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchTipos)

// --- Abrir Modal (Crear o Editar) ---
const openModal = (tipo = null) => {
  errors.value = {}
  if (tipo) {
    // Modo Edición
    isEditing.value = true
    form.value = { ...tipo } // Copiamos los datos
  } else {
    // Modo Creación
    isEditing.value = false
    form.value = { id: null, nombre: '', periodo_alerta: 60 }
  }
  isModalOpen.value = true
}

// --- Guardar (Create / Update) ---
const saveTipo = async () => {
  errors.value = {}
  try {
    if (isEditing.value) {
      await axios.put(`/tipos-documento/${form.value.id}`, form.value)
      toast.success('Tipo de documento actualizado correctamente') // <-- TOAST ÉXITO
    } else {
      await axios.post('/tipos-documento', form.value)
      toast.success('Tipo de documento creado exitosamente') // <-- TOAST ÉXITO
    }
    isModalOpen.value = false
    await fetchTipos() // Recargar lista
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
      toast.warn('Por favor, revisa los campos del formulario') // <-- TOAST AVISO
    } else {
      toast.error('Ocurrió un error inesperado al guardar') // <-- TOAST ERROR
    }
  }
}

// --- Eliminar ---
const deleteTipo = async (id) => {
  if (
    !confirm(
      '¿Seguro que deseas eliminar este tipo de documento? Esto podría afectar a empleados que ya lo tengan.'
    )
  )
    return
  try {
    await axios.delete(`/tipos-documento/${id}`)
    toast.success('Tipo de documento eliminado') // <-- TOAST ÉXITO
    await fetchTipos()
  } catch (error) {
    console.error(error)
    toast.error('No se puede eliminar (probablemente esté en uso por algún empleado)') // <-- TOAST ERROR
  }
}
</script>

<template>
  <AppLayout title="Configuración de Documentos">
    <template #header>
      <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Configuración de Documentos
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100 p-6">
          <!-- Cabecera de la Sección -->
          <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
              <h3 class="text-lg font-bold text-gray-900">Tipos de Documento y Alertas</h3>
              <p class="text-sm text-gray-500">
                Define qué documentos se piden y cuándo el sistema debe enviar alertas de
                vencimiento.
              </p>
            </div>
            <button
              @click="openModal()"
              class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition shadow-md"
            >
              + Nuevo Tipo
            </button>
          </div>

          <!-- Tabla -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Nombre del Documento
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Alerta de Vencimiento
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="tipos.length === 0 && !isLoading">
                  <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                    No hay tipos de documento registrados.
                  </td>
                </tr>
                <tr v-for="tipo in tipos" :key="tipo.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ tipo.nombre }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                    >
                      {{ tipo.periodo_alerta }} días antes
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="openModal(tipo)"
                      class="text-indigo-600 hover:text-indigo-900 mr-4 font-semibold"
                    >
                      Editar
                    </button>
                    <button @click="deleteTipo(tipo.id)" class="text-red-600 hover:text-red-900">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREAR / EDITAR -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div
        class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity"
          @click="isModalOpen = false"
        ></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <!-- Caja Modal -->
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                  {{ isEditing ? 'Editar Tipo de Documento' : 'Crear Nuevo Tipo de Documento' }}
                </h3>

                <form @submit.prevent="saveTipo">
                  <!-- Nombre -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Nombre del Documento</label
                    >
                    <input
                      type="text"
                      v-model="form.nombre"
                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      placeholder="Ej: Licencia de Conducir"
                    />
                    <p v-if="errors.nombre" class="text-xs text-red-600 mt-1">
                      {{ errors.nombre[0] }}
                    </p>
                  </div>

                  <!-- Periodo Alerta -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Días de Alerta (Semáforo Amarillo)</label
                    >
                    <div class="flex items-center">
                      <input
                        type="number"
                        v-model="form.periodo_alerta"
                        class="w-24 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center"
                        min="1"
                      />
                      <span class="ml-3 text-sm text-gray-500">días antes del vencimiento.</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                      Cuando falten estos días, el sistema enviará notificaciones y marcará el
                      documento en amarillo.
                    </p>
                    <p v-if="errors.periodo_alerta" class="text-xs text-red-600 mt-1">
                      {{ errors.periodo_alerta[0] }}
                    </p>
                  </div>

                  <!-- Botones -->
                  <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button
                      type="submit"
                      class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:col-start-2 sm:text-sm"
                    >
                      Guardar
                    </button>
                    <button
                      type="button"
                      @click="isModalOpen = false"
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
  </AppLayout>
</template>
