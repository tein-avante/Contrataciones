// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '../lib/axios.js'

export const useAuthStore = defineStore('auth', () => {
  // --- ESTADO ---
  // Función auxiliar para obtener valores del localStorage de forma segura
  const getSafeItem = (key) => {
    const item = localStorage.getItem(key)
    return (!item || item === 'undefined') ? null : item
  }

  // Leemos el token y el usuario desde el localStorage para que la sesión persista
  const authToken = ref(getSafeItem('authToken'))
  
  // Función auxiliar para parsear JSON de forma segura
  const safeJsonParse = (key) => {
    const item = getSafeItem(key)
    if (!item) return null
    try {
      return JSON.parse(item)
    } catch (e) {
      console.error(`Error parseando localStorage key "${key}":`, e)
      return null
    }
  }

  const user = ref(safeJsonParse('user'))

  // --- GETTERS ---
  // Propiedad computada para saber si estamos logueados
  const isLoggedIn = computed(() => !!authToken.value && !!user.value)

  // --- ACCIONES ---
  async function login(email, password) {
    try {
      // Hacemos la petición a la API de Laravel
      const response = await axios.post('/login', { email, password })
      console.log('Respuesta cruda del login:', response.data)

      // Si tiene éxito, guardamos los datos
      authToken.value = response.data.access_token || null
      user.value = response.data.user || null

      if (authToken.value && authToken.value !== 'undefined') localStorage.setItem('authToken', authToken.value)
      if (user.value && user.value !== 'undefined') localStorage.setItem('user', JSON.stringify(user.value))

      return true
    } catch (error) {
      // Si falla, nos aseguramos de que todo esté limpio
      logout()
      console.error('Error de login COMPLETO:', error)
      console.error('Respuesta de error de login:', error.response?.data)
      return false
    }
  }

  function logout() {
    authToken.value = null
    user.value = null
    localStorage.removeItem('authToken')
    localStorage.removeItem('user')
    // En una app real, también llamaríamos a /api/logout
  }

  return { authToken, user, isLoggedIn, login, logout }
})
