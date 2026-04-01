<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import axios from '../lib/axios'
import { toast } from 'vue3-toastify'

// Estados
const users = ref([])
const isModalOpen = ref(false)
const isLoading = ref(false)
const isEditing = ref(false) // Para saber si es Crear o Editar
const editingUserId = ref(null) // ID del usuario que estamos editando

// Formulario
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'analyst',
})
const errors = ref({})

// Cargar Usuarios
const fetchUsers = async () => {
  try {
    const response = await axios.get('/usuarios')
    users.value = response.data
  } catch (error) {
    console.error('Error cargando usuarios:', error)
  }
}

onMounted(fetchUsers)

// --- Lógica del Modal ---

const openCreateModal = () => {
  isEditing.value = false
  editingUserId.value = null
  form.value = { name: '', email: '', password: '', password_confirmation: '', role: 'analyst' }
  errors.value = {}
  isModalOpen.value = true
}

const openEditModal = (user) => {
  isEditing.value = true
  editingUserId.value = user.id
  // Copiamos los datos al formulario (menos la contraseña)
  form.value = {
    name: user.name,
    email: user.email,
    role: user.role,
    password: '', // La contraseña se deja vacía
    password_confirmation: '',
  }
  errors.value = {}
  isModalOpen.value = true
}

// Enviar Formulario (Crear o Editar)
const handleSubmit = async () => {
  errors.value = {}
  isLoading.value = true
  try {
    if (isEditing.value) {
      // Lógica de ACTUALIZAR (PUT)
      await axios.put(`/usuarios/${editingUserId.value}`, form.value)
      toast.success('Usuario actualizado correctamente')
    } else {
      // Lógica de CREAR (POST)
      await axios.post('/usuarios', form.value)
      toast.success('Usuario creado correctamente')
    }

    isModalOpen.value = false
    await fetchUsers()
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
    } else {
      alert('Ocurrió un error inesperado')
    }
  } finally {
    isLoading.value = false
  }
}

// Eliminar Usuario
const deleteUser = async (id) => {
  if (!confirm('¿Estás seguro? Esta acción eliminará al usuario permanentemente.')) return
  try {
    await axios.delete(`/usuarios/${id}`)
    await fetchUsers()
  } catch (error) {
    console.error(error)
    alert('No se pudo eliminar el usuario.')
  }
}
</script>

<template>
  <AppLayout title="Gestión de Usuarios">
    <template #header>
      <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
        Gestión de Usuarios del Sistema
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100 p-6">
          <!-- Cabecera -->
          <div class="flex justify-between items-center mb-6">
            <div>
              <h3 class="text-lg font-bold text-gray-900">Usuarios Registrados</h3>
              <p class="text-sm text-gray-500">Administra quién tiene acceso al sistema.</p>
            </div>
            <button
              @click="openCreateModal"
              class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow transition"
            >
              + Nuevo Usuario
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
                    Nombre
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Email
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Rol
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition">
                  <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                    {{ user.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ user.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      v-if="user.role === 'admin'"
                      class="px-2 py-1 text-xs font-bold text-purple-700 bg-purple-100 rounded-full"
                      >Administrador</span
                    >
                    <span
                      v-else
                      class="px-2 py-1 text-xs font-bold text-blue-700 bg-blue-100 rounded-full"
                      >Analista</span
                    >
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                    <button
                      @click="openEditModal(user)"
                      class="text-indigo-600 hover:text-indigo-900 font-semibold"
                    >
                      Editar
                    </button>
                    <button @click="deleteUser(user.id)" class="text-red-600 hover:text-red-900">
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

    <!-- Modal Crear/Editar Usuario -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          @click="isModalOpen = false"
        ></div>

        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
              {{ isEditing ? 'Editar Usuario' : 'Registrar Nuevo Usuario' }}
            </h3>

            <form @submit.prevent="handleSubmit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  required
                />
                <p v-if="errors.email" class="text-xs text-red-600 mt-1">{{ errors.email[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Rol</label>
                <select
                  v-model="form.role"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="analyst">Analista</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>

              <div class="border-t pt-4 mt-4">
                <p class="text-xs text-gray-500 mb-2" v-if="isEditing">
                  Dejar en blanco si no desea cambiar la contraseña.
                </p>
                <div>
                  <label class="block text-sm font-medium text-gray-700"
                    >Contraseña {{ isEditing ? '(Opcional)' : '' }}</label
                  >
                  <input
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :required="!isEditing"
                  />
                  <p v-if="errors.password" class="text-xs text-red-600 mt-1">
                    {{ errors.password[0] }}
                  </p>
                </div>

                <div class="mt-4">
                  <label class="block text-sm font-medium text-gray-700"
                    >Confirmar Contraseña</label
                  >
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    :required="!isEditing"
                  />
                </div>
              </div>

              <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button
                  type="submit"
                  class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:col-start-2"
                >
                  {{ isEditing ? 'Actualizar' : 'Guardar' }}
                </button>
                <button
                  type="button"
                  @click="isModalOpen = false"
                  class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1"
                >
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
