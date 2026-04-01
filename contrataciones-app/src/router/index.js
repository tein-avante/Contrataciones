import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'
import LoginView from '../views/LoginView.vue'
import EmpleadosIndex from '../views/EmpleadosIndex.vue'
import EmpleadosCreate from '../views/EmpleadosCreate.vue'
import EmpleadosEdit from '../views/EmpleadosEdit.vue'
import EmpleadosShow from '../views/EmpleadosShow.vue'
import TiposDocumentoIndex from '../views/TiposDocumentoIndex.vue'
import UsuariosIndex from '../views/UsuariosIndex.vue'

const router = createRouter({
  history: createWebHistory('/'),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
      // ▼▼▼ LÓGICA NUEVA: Si ya está logueado, no mostrar login ▼▼▼
      beforeEnter: (to, from, next) => {
        const authStore = useAuthStore()
        if (authStore.isLoggedIn) {
          next({ name: 'empleados.index' })
        } else {
          next()
        }
      },
    },
    {
      path: '/empleados',
      name: 'empleados.index',
      component: EmpleadosIndex,
      meta: { requiresAuth: true },
    },
    {
      path: '/empleados/crear',
      name: 'empleados.create',
      component: EmpleadosCreate,
      meta: { requiresAuth: true },
    },
    {
      path: '/empleados/:id/editar',
      name: 'empleados.edit',
      component: EmpleadosEdit,
      meta: { requiresAuth: true },
    },
    {
      path: '/empleados/:id',
      name: 'empleados.show',
      component: EmpleadosShow,
      meta: { requiresAuth: true },
    },
    {
      path: '/configuracion/documentos',
      name: 'tipos-documento.index',
      component: TiposDocumentoIndex,
      meta: { requiresAuth: true },
    },
    {
      path: '/usuarios',
      name: 'usuarios.index',
      component: UsuariosIndex,
      meta: { requiresAuth: true, role: 'admin' },
    },
  ],
})

// --- GUARDAS GLOBALES ---
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // 1. Verificar Autenticación
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    return next({ name: 'login' })
  }

  // 2. Verificar Rol (Solo si está logueado)
  // Si intenta entrar a una ruta de admin sin ser admin, lo devolvemos a empleados
  if (to.meta.role && authStore.user && authStore.user.role !== to.meta.role) {
    return next({ name: 'empleados.index' })
  }

  next()
})

export default router
