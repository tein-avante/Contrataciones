<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import axios from '../lib/axios.js'
import { toast } from 'vue3-toastify'

const router = useRouter()

// 1. Estado reactivo
const empleados = ref([])
const busqueda = ref('')
const filtroSector = ref('')
const filtroEstado = ref('')
// ▼▼▼ NUEVO FILTRO: Estado de Embarque ▼▼▼
const filtroEmbarque = ref('')

// 2. Cargar datos
onMounted(async () => {
  try {
    const response = await axios.get('/empleados')
    empleados.value = response.data
  } catch (error) {
    console.error('Error al obtener los empleados:', error)
  }
})

// 3. Filtrado (Lógica Mejorada)
const empleadosFiltrados = computed(() => {
  return empleados.value.filter((empleado) => {
    // A. Filtro por Texto
    const texto = busqueda.value.toLowerCase()
    const coincideTexto =
      empleado.nombre.toLowerCase().includes(texto) ||
      empleado.email.toLowerCase().includes(texto) ||
      (empleado.cargo && empleado.cargo.toLowerCase().includes(texto))

    // B. Filtro por Sector
    const coincideSector =
      filtroSector.value === '' || empleado.tipo_empleado === filtroSector.value

    // C. Filtro por Estado (Semáforo)
    // El backend nos manda: 'critical' (Rojo), 'warning' (Amarillo), 'ok' (Verde)
    const coincideEstado = filtroEstado.value === '' || empleado.semaforo === filtroEstado.value

    // D. ▼▼▼ NUEVO: Filtro por Estado de Embarque ▼▼▼
    const coincideEmbarque = filtroEmbarque.value === '' || empleado.estado_embarque === filtroEmbarque.value

    return coincideTexto && coincideSector && coincideEstado && coincideEmbarque
  })
})

// 4. Eliminar empleado
const eliminarEmpleado = async (id) => {
  if (!confirm('¿Está seguro de que desea eliminar a este empleado?')) return
  try {
    await axios.delete(`/empleados/${id}`)
    empleados.value = empleados.value.filter((empleado) => empleado.id !== id)
    toast.success('Empleado eliminado correctamente') // <--- ÉXITO
  } catch (error) {
    console.error(error)
    toast.error('No se pudo eliminar el empleado') // <--- ERROR
  }
}

// 5. Navegación
const irAlExpediente = (id) => {
  router.push({ name: 'empleados.show', params: { id } })
}

// 6. Exportación a Excel (CSV)
const exportarExcel = async (tipo = '') => {
  try {
    const response = await axios.get('/empleados/export/excel', {
      params: { tipo },
      responseType: 'blob',
    })

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    const fileName = `Reporte_Personal_${
      tipo ? tipo.replaceAll(' ', '_') : 'General'
    }_${new Date().toISOString().slice(0, 10)}.csv`
    link.setAttribute('download', fileName)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    toast.success('Reporte generado correctamente')
    showExportMenu.value = false
  } catch (error) {
    console.error('Error al exportar:', error)
    toast.error('No se pudo generar el reporte')
  }
}

// Estado para el dropdown de exportación
const showExportMenu = ref(false)
</script>

<template>
  <AppLayout title="Lista de Empleados">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
      <!-- CABECERA -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-semibold text-gray-800">Lista General de Empleados</h1>
        <div class="flex items-center gap-2">
          <!-- Botón Exportar con Dropdown -->
          <div class="relative">
            <button
              @click="showExportMenu = !showExportMenu"
              class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition shadow-sm"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Exportar Excel
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Menú Dropdown -->
            <div
              v-if="showExportMenu"
              class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-100"
            >
              <button
                @click="exportarExcel('Personal de Buque')"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700"
              >
                🚢 Personal de Buque
              </button>
              <button
                @click="exportarExcel('Personal Administrativo-Operativo')"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700"
              >
                💼 Personal Administrativo-Operativo
              </button>
              <div class="border-t border-gray-100"></div>
              <button
                @click="exportarExcel()"
                class="block w-full text-left px-4 py-2 text-sm font-bold text-gray-800 hover:bg-gray-100"
              >
                📊 Todo el Personal
              </button>
            </div>
          </div>

          <router-link
            to="/empleados/crear"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg whitespace-nowrap transition shadow-sm"
          >
            Añadir Nuevo Empleado
          </router-link>
        </div>
      </div>

      <!-- BARRA DE FILTROS -->
      <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6 pt-4 border-t">
        <!-- 1. Buscador -->
        <div class="relative md:col-span-2">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg
              aria-hidden="true"
              class="w-5 h-5 text-gray-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              ></path>
            </svg>
          </div>
          <input
            type="text"
            v-model="busqueda"
            class="block w-full p-2.5 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
            placeholder="Buscar por nombre, email o cargo..."
          />
        </div>

        <!-- 2. Filtro por Sector -->
        <div>
          <select
            v-model="filtroSector"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 focus:outline-none cursor-pointer"
          >
            <option value="">Todos los Sectores</option>
            <option value="Personal Administrativo-Operativo">Personal Administrativo-Operativo</option>
            <option value="Personal de Buque">Personal de Buque</option>
          </select>
        </div>

        <!-- 3. Filtro por Estado (Documentación) -->
        <div>
          <select
            v-model="filtroEstado"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 focus:outline-none cursor-pointer"
          >
            <option value="">Documentación: Todos</option>
            <option value="critical" class="text-red-600 font-bold">⚠ Vencidos</option>
            <option value="warning" class="text-yellow-600 font-bold">⚡ Próximos</option>
            <option value="ok" class="text-green-600 font-bold">✓ Al Día</option>
          </select>
        </div>

        <!-- 4. Filtro por Estado de Embarque -->
        <div>
          <select
            v-model="filtroEmbarque"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 focus:outline-none cursor-pointer"
          >
            <option value="">Embarque: Todos</option>
            <option value="A bordo" class="text-blue-600 font-bold">🚢 A bordo</option>
            <option value="En tierra" class="text-amber-600 font-bold">🏠 En tierra</option>
          </select>
        </div>
      </div>

      <!-- TABLA -->
      <div class="w-full overflow-x-auto xl:overflow-visible">
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12"
              >
                #
              </th>
              <th
                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Nombre
              </th>
              <th
                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell"
              >
                Email
              </th>
              <th
                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Sector
              </th>
              <th
                class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Cargo
              </th>
              <th
                class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-48"
              >
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="(empleado, index) in empleadosFiltrados"
              :key="empleado.id"
              class="hover:bg-indigo-50 cursor-pointer transition duration-150 ease-in-out group"
              @click="irAlExpediente(empleado.id)"
            >
              <!-- Índice -->
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ index + 1 }}
              </td>

              <!-- Nombre + Semáforo -->
              <td
                class="px-2 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 group-hover:text-indigo-600 flex items-center gap-2"
              >
                <!-- ROJO -->
                <span
                  v-if="empleado.semaforo === 'critical'"
                  class="relative flex h-3 w-3"
                  title="Tiene documentos vencidos"
                >
                  <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
                  ></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
                <!-- AMARILLO -->
                <span
                  v-else-if="empleado.semaforo === 'warning'"
                  class="relative flex h-3 w-3"
                  title="Documentos próximos a vencer"
                >
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-400"></span>
                </span>
                <!-- VERDE -->
                <span v-else class="relative flex h-3 w-3" title="Documentación al día">
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>

                {{ empleado.nombre }}
              </td>

              <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                {{ empleado.email }}
              </td>

              <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex flex-col">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full w-fit"
                    :class="
                      empleado.tipo_empleado === 'Personal de Buque'
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-green-100 text-green-800'
                    "
                  >
                    {{ empleado.tipo_empleado }}
                  </span>
                  <!-- Indicador de Embarque -->
                  <span
                    v-if="empleado.tipo_empleado === 'Personal de Buque'"
                    class="mt-1 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1"
                    :class="empleado.estado_embarque === 'A bordo' ? 'text-blue-600' : 'text-amber-600'"
                  >
                    <span v-if="empleado.estado_embarque === 'A bordo'">🚢 A bordo</span>
                    <span v-else>🏠 En tierra</span>
                  </span>
                </div>
              </td>

              <td class="px-2 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ empleado.cargo }}
              </td>

              <!-- Acciones -->
              <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium" @click.stop>
                <div class="flex justify-end items-center space-x-4">
                  <!-- Ver Expediente -->
                  <router-link
                    :to="{ name: 'empleados.show', params: { id: empleado.id } }"
                    class="text-gray-400 hover:text-indigo-600 transition"
                    title="Ver Expediente"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                  </router-link>

                  <!-- Editar -->
                  <router-link
                    :to="{ name: 'empleados.edit', params: { id: empleado.id } }"
                    class="text-indigo-600 hover:text-indigo-900 transition"
                    title="Editar"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      />
                    </svg>
                  </router-link>

                  <!-- Eliminar -->
                  <button
                    @click="eliminarEmpleado(empleado.id)"
                    class="text-red-600 hover:text-red-900 transition"
                    title="Eliminar"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Mensaje Vacío -->
            <tr v-if="empleadosFiltrados.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <svg
                    class="w-12 h-12 text-gray-300 mb-3"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                    ></path>
                  </svg>
                  <p>No se encontraron empleados con esos criterios.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
