import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { setupAxiosInterceptors } from './lib/axios.js'

// --- 1. IMPORTACIONES DE TOASTIFY ---
import Vue3Toastify, { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css' // <--- ¡ESTA LÍNEA ES OBLIGATORIA!

const app = createApp(App)

app.use(createPinia())
app.use(router)

// --- 2. CONFIGURACIÓN (Con arreglo de Z-Index) ---
app.use(Vue3Toastify, {
  autoClose: 3000,
  position: 'top-right',
  theme: 'colored',
  // Forzamos un z-index altísimo para que salga encima de los Modales
  style: {
    zIndex: '99999',
  },
  clearOnUrlChange: false,
})

setupAxiosInterceptors()

app.mount('#app')
