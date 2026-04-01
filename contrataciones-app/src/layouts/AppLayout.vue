<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router' // Usamos Vue Router
import { useAuthStore } from '../stores/auth' // Usamos el Store de Auth
import ApplicationMark from '@/components/ApplicationMark.vue'
import NotificationBell from '@/components/NotificationBell.vue'

defineProps({
  title: String,
})

const authStore = useAuthStore()
const router = useRouter()
const showingNavigationDropdown = ref(false)

// Lógica de Logout para SPA (Sin Inertia)
const handleLogout = async () => {
  await authStore.logout()
  router.push('/') // Redirigir al login
}
</script>

<template>
  <div>
    <!-- Eliminamos <Head> y <Banner> porque son de Inertia/Jetstream -->

    <div class="min-h-screen bg-gray-50">
      <!-- BARRA DE NAVEGACIÓN -->
      <nav class="bg-avante-primary border-b border-avante-dark shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <!-- LADO IZQUIERDO -->
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <router-link to="/empleados">
                  <ApplicationMark class="block h-10 w-auto" />
                </router-link>
              </div>

              <!-- Enlaces de Navegación -->
              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <!-- Enlace: Empleados -->
                <router-link
                  to="/empleados"
                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out"
                  :class="[
                    $route.path.startsWith('/empleados')
                      ? 'border-white text-white font-bold'
                      : 'border-transparent text-blue-100 hover:text-white hover:border-blue-200',
                  ]"
                >
                  Empleados
                </router-link>

                <!-- Enlace: Configuración -->
                <router-link
                  to="/configuracion/documentos"
                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out"
                  :class="[
                    $route.path.startsWith('/configuracion')
                      ? 'border-white text-white font-bold'
                      : 'border-transparent text-blue-100 hover:text-white hover:border-blue-200',
                  ]"
                >
                  Configuración
                </router-link>

                <!-- Enlace: Usuarios (Solo Admin) -->
                <router-link
                  v-if="authStore.user && authStore.user.role === 'admin'"
                  to="/usuarios"
                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition duration-150 ease-in-out"
                  :class="[
                    $route.path.startsWith('/usuarios')
                      ? 'border-white text-white font-bold'
                      : 'border-transparent text-blue-100 hover:text-white hover:border-blue-200',
                  ]"
                >
                  Usuarios
                </router-link>
              </div>
            </div>

            <!-- LADO DERECHO -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
              <!-- Campana -->
              <div class="mr-4 relative">
                <NotificationBell />
              </div>

              <!-- Nombre de Usuario -->
              <div class="ml-3 relative text-white text-sm mr-4">
                Hola, <span class="font-bold" v-if="authStore.user">{{ authStore.user.name }}</span>
              </div>

              <!-- Botón Cerrar Sesión -->
              <button
                @click="handleLogout"
                class="inline-flex items-center px-4 py-2 bg-avante-red border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-avante-primary transition ease-in-out duration-150 shadow-sm"
              >
                Cerrar Sesión
              </button>
            </div>

            <!-- Hamburger (Móvil) -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-blue-100 hover:text-white hover:bg-avante-dark focus:outline-none transition duration-150 ease-in-out"
              >
                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Menú Responsivo (Móvil) -->
        <div
          :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
          class="sm:hidden bg-avante-dark border-t border-gray-700"
        >
          <div class="pt-2 pb-3 space-y-1">
            <router-link
              to="/empleados"
              class="block ps-3 pe-4 py-2 border-l-4 text-base font-medium text-white bg-avante-primary border-white transition duration-150 ease-in-out"
            >
              Empleados
            </router-link>
            <router-link
              to="/configuracion/documentos"
              class="block ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 hover:border-gray-300 transition duration-150 ease-in-out"
            >
              Configuración
            </router-link>
            <router-link
              v-if="authStore.user && authStore.user.role === 'admin'"
              to="/usuarios"
              class="block ps-3 pe-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 hover:border-gray-300 transition duration-150 ease-in-out"
            >
              Usuarios
            </router-link>
          </div>

          <!-- Opciones de Usuario Móvil -->
          <div class="pt-4 pb-1 border-t border-gray-700">
            <div class="flex items-center px-4">
              <div class="font-medium text-base text-white" v-if="authStore.user">
                {{ authStore.user.name }}
              </div>
            </div>
            <div class="mt-3 space-y-1">
              <button
                @click="handleLogout"
                class="w-full text-left block px-4 py-2 text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition duration-150 ease-in-out"
              >
                Cerrar Sesión
              </button>
            </div>
          </div>
        </div>
      </nav>

      <!-- Cabecera de página -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Contenido -->
      <main>
        <div class="py-12">
          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <slot />
          </div>
        </div>
      </main>
    </div>
  </div>
</template>
