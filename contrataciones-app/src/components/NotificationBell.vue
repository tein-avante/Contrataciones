<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from '../lib/axios'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// --- Estados ---
const allNotifications = ref([]) // Almacenará todas las notificaciones (leídas y no leídas)
const isOpen = ref(false)

// --- Propiedades Computadas ---
// Un array que solo contiene las notificaciones no leídas
const unreadNotifications = computed(() => {
  return allNotifications.value.filter((n) => n.read_at === null)
})

// --- Funciones ---
const fetchNotifications = async () => {
  try {
    const response = await axios.get('/notifications')
    allNotifications.value = response.data
  } catch (error) {
    console.error('Error al obtener notificaciones:', error)
  }
}

// Se ejecuta cuando el usuario hace clic en una notificación específica
const handleNotificationClick = async (notification) => {
  // 1. Marca la notificación como leída en el backend (si no lo está ya)
  if (!notification.read_at) {
    try {
      await axios.post(`/notifications/${notification.id}/read`)
      // Actualizamos el estado localmente para que se vea el cambio al instante
      const index = allNotifications.value.findIndex((n) => n.id === notification.id)
      if (index !== -1) {
        allNotifications.value[index].read_at = new Date().toISOString()
      }
    } catch (error) {
      console.error('Error al marcar la notificación como leída:', error)
    }
  }

  // 2. Navega al expediente del empleado
  router.push({ name: 'empleados.show', params: { id: notification.empleado_id } })

  // 3. Cierra el menú desplegable
  isOpen.value = false
}

// Carga las notificaciones al iniciar
onMounted(fetchNotifications)

// Recargar notificaciones si la ruta cambia, para reflejar marcados de lectura desde otras vistas (ej. EmpleadosShow)
watch(() => route.path, () => {
  fetchNotifications()
})
</script>

<template>
  <div class="relative">
    <!-- Botón de la Campana -->
    <button @click="isOpen = !isOpen" class="relative p-2 text-gray-600 hover:text-gray-800">
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        />
      </svg>
      <!-- La burbuja roja ahora cuenta solo las 'unreadNotifications' -->
      <span
        v-if="unreadNotifications.length > 0"
        class="absolute top-0 right-0 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
      >
        {{ unreadNotifications.length }}
      </span>
    </button>

    <!-- Menú Desplegable -->
    <div
      v-if="isOpen"
      @click.away="isOpen = false"
      class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-20"
    >
      <div class="p-4 border-b font-semibold">Notificaciones</div>
      <div v-if="allNotifications.length === 0" class="p-4 text-center text-gray-500">
        No hay notificaciones.
      </div>
      <div v-else class="max-h-96 overflow-y-auto">
        <!-- Hacemos el bucle sobre TODAS las notificaciones -->
        <div
          v-for="notification in allNotifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="p-4 border-b hover:bg-gray-100 cursor-pointer"
          :class="{ 'bg-blue-50': !notification.read_at }"
        >
          <p
            class="text-sm"
            :class="{ 'font-bold': !notification.read_at, 'font-normal': notification.read_at }"
          >
            {{ notification.mensaje }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            {{ new Date(notification.created_at).toLocaleString() }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
