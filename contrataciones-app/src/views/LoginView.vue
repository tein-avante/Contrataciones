<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import ApplicationMark from '../components/ApplicationMark.vue'
import { toast } from 'vue3-toastify'

const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  isLoading.value = true

  try {
    const success = await authStore.login(email.value, password.value)

    if (success) {
      toast.success('¡Bienvenido de nuevo!')
      router.push({ name: 'empleados.index' })
    } else {
      toast.error('Credenciales incorrectas. Por favor verifique.')
    }
  } catch (error) {
    toast.error('Ocurrió un error de conexión.')
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <!-- 1. LOGO -->
    <div class="mb-4">
      <ApplicationMark mode="login" />
    </div>

    <!-- ▼▼▼ 2. TÍTULO DEL PROYECTO (NUEVO) ▼▼▼ -->
    <h1
      class="text-center text-xl md:text-2xl font-bold text-avante-dark mb-8 max-w-lg leading-tight px-4"
    >
      Gerencia de Contrataciones
    </h1>
    <!-- ▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲ -->

    <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
      <h2 class="text-center text-xl font-semibold text-gray-700 mb-6">Iniciar Sesión</h2>

      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium text-gray-700"
            >Correo Electrónico</label
          >
          <input
            type="email"
            id="email"
            v-model="email"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-avante-primary focus:ring-avante-primary sm:text-sm py-2 px-3 border"
            required
            autofocus
            placeholder="admin@sgrh.com"
          />
        </div>

        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
          <input
            type="password"
            id="password"
            v-model="password"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-avante-primary focus:ring-avante-primary sm:text-sm py-2 px-3 border"
            required
            placeholder="••••••••"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full inline-flex justify-center items-center px-4 py-2 bg-avante-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-avante-dark active:bg-avante-dark focus:outline-none focus:border-avante-dark focus:ring focus:ring-avante-primary disabled:opacity-25 transition"
          >
            <span v-if="isLoading">Ingresando...</span>
            <span v-else>ACCEDER AL SISTEMA</span>
          </button>
        </div>
      </form>
    </div>

    <p class="mt-8 text-center text-xs text-gray-400">
      &copy; {{ new Date().getFullYear() }} Gerencia de Contrataciones. Todos los derechos reservados.
    </p>
  </div>
</template>
