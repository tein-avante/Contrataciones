import axios from 'axios'
import { useAuthStore } from '../stores/auth.js'

/**
 * Configuración Global del Cliente HTTP (Axios).
 *
 * Establece la base para todas las comunicaciones con la API de Laravel.
 * Define las cabeceras estándar para asegurar respuestas en formato JSON.
 */
const axiosClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

/**
 * Inicializa los interceptores de Axios para la gestión de seguridad.
 *
 * Esta función debe llamarse al arrancar la aplicación (en main.js) para
 * inyectar la lógica de autenticación en todas las peticiones salientes.
 */
export function setupAxiosInterceptors() {
  /**
   * INTERCEPTOR DE PETICIONES (REQUEST)
   *
   * Propósito: Inyección de Token de Seguridad.
   * Antes de enviar cualquier petición al backend, este interceptor verifica
   * si existe un token de sesión en el store de Pinia. Si existe, lo adjunta
   * en la cabecera 'Authorization' usando el esquema 'Bearer'.
   * Esto autentica al usuario ante la API de Laravel Sanctum.
   */
  axiosClient.interceptors.request.use(
    (config) => {
      const authStore = useAuthStore()
      const token = authStore.authToken

      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }

      return config
    },
    (error) => {
      return Promise.reject(error)
    }
  )

  /**
   * INTERCEPTOR DE RESPUESTAS (RESPONSE)
   *
   * Propósito: Gestión de Caducidad de Sesión y Seguridad.
   * Monitorea todas las respuestas que regresan de la API.
   *
   * Lógica Crítica:
   * Si la API devuelve un error 401 (Unauthorized), significa que el token
   * es inválido (ej: expiró, o el usuario inició sesión en otro dispositivo
   * activando la política de "Sesión Única").
   * En este caso, forzamos el cierre de sesión local y redirigimos al login
   * para proteger la aplicación.
   */
  axiosClient.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response && error.response.status === 401) {
        const authStore = useAuthStore()
        authStore.logout() // Limpia el estado local (Pinia/LocalStorage)
        window.location.href = '/' // Redirección forzada al inicio
      }
      return Promise.reject(error)
    }
  )
}

export default axiosClient
