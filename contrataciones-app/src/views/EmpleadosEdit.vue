<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import axios from '../lib/axios'
import { toast } from 'vue3-toastify'

const router = useRouter() // Para redirigir al usuario
const route = useRoute() // Para acceder a los parámetros de la URL, como el ID

// Estado reactivo para el formulario, inicializado con todos los campos
const form = ref({
  nombre: '',
  email: '',
  tipo_empleado: '',
  cargo: '',
  puesto: '',
  cedula: '',
  cedula_marina: '',
  telefono: '',
  codigo_postal: '',
  fecha_nacimiento: '',
  sexo: '',
  tiene_hijos: 'No',
  estado_embarque: '',
  direccion: '',
  ciudad: '',
  lugar_nacimiento: '',
  estado_civil: '',
  nacionalidad: '',
  estatura: '',
  peso: '',
  tipo_sangre: '',
  fecha_disponible: '',
  tipo_habitacion: '',
  caracteristicas_habitacion: '',
  colegiacion_nro: '',
  licencia_conductor_nro: '',
  licencia_conductor_expiracion: '',
  talla_pantalon: '',
  talla_camisa: '',
  talla_zapato: '',
  habilidades_destrezas: '',
  foto: null,
})
const errors = ref({})

// Obtenemos el ID del empleado desde la URL
const empleadoId = route.params.id

// Usamos onMounted para cargar los datos del empleado en cuanto la página esté lista
onMounted(async () => {
  try {
    const response = await axios.get(`/empleados/${empleadoId}`)
    // Rellenamos el formulario con los datos recibidos de la API
    form.value = response.data
  } catch (error) {
    console.error('Error al obtener los datos del empleado:', error)
  }
})

const handleSubmit = async () => {
  errors.value = {}
  try {
    // Si no es Personal de Buque, limpiamos los campos específicos
    // Para subir archivos en un "UPDATE", Laravel requiere usar POST con _method = PUT
    const formData = new FormData()
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null) {
        formData.append(key, form.value[key])
      }
    })
    formData.append('_method', 'PUT')

    // Hacemos una petición POST con spoofing de PUT para actualizar el recurso con archivos
    await axios.post(`/empleados/${empleadoId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    toast.success('Empleado actualizado correctamente')
    // Si tiene éxito, redirigimos a la lista de empleados
    router.push({ name: 'empleados.index' })
  } catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors
    } else {
      toast.error('Error al actualizar')
    }
  }
}

const handleFileChange = (event) => {
  form.value.foto = event.target.files[0]
}
</script>

<template>
  <AppLayout>
    <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 border-b pb-4">Editar Empleado</h1>
      <form @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Datos Básicos -->
          <div>
            <label for="nombre" class="block text-sm font-medium text-gray-700"
              >Nombre Completo</label
            >
            <input
              type="text"
              v-model="form.nombre"
              id="nombre"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              required
            />
            <p v-if="errors.nombre" class="text-sm text-red-600 mt-1">{{ errors.nombre[0] }}</p>
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              type="email"
              v-model="form.email"
              id="email"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              required
            />
            <p v-if="errors.email" class="text-sm text-red-600 mt-1">{{ errors.email[0] }}</p>
          </div>

          <!-- Foto de Perfil -->
          <div class="md:col-span-2 bg-indigo-50 p-4 rounded-lg border border-indigo-100 mb-4">
            <label for="foto" class="block text-sm font-bold text-indigo-700 mb-2">Foto de Perfil (Recuadro del PDF)</label>
            <input
              type="file"
              id="foto"
              @change="handleFileChange"
              accept="image/*"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"
            />
            <p v-if="form.foto && typeof form.foto === 'string'" class="text-xs text-green-600 mt-1">✓ Ya tiene una foto cargada. Sube una nueva para reemplazarla.</p>
            <p v-else class="text-xs text-gray-500 mt-1">Sube una foto de frente para el expediente digital. (JPG, PNG)</p>
            <p v-if="errors.foto" class="text-sm text-red-600 mt-1">{{ errors.foto[0] }}</p>
          </div>

          <div>
            <label for="cedula" class="block text-sm font-medium text-gray-700">Cédula</label>
            <input
              type="text"
              v-model="form.cedula"
              id="cedula"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              required
            />
            <p v-if="errors.cedula" class="text-sm text-red-600 mt-1">{{ errors.cedula[0] }}</p>
          </div>

          <div>
            <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input
              type="text"
              v-model="form.telefono"
              id="telefono"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
            <p v-if="errors.telefono" class="text-sm text-red-600 mt-1">{{ errors.telefono[0] }}</p>
          </div>

          <!-- Clasificación -->
          <div>
            <label for="tipo_empleado" class="block text-sm font-medium text-gray-700"
              >Tipo de Empleado</label
            >
            <select
              v-model="form.tipo_empleado"
              id="tipo_empleado"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
              required
            >
              <option value="Personal Administrativo-Operativo">Personal Administrativo-Operativo</option>
              <option value="Personal de Buque">Personal de Buque</option>
            </select>
            <p v-if="errors.tipo_empleado" class="text-sm text-red-600 mt-1">
              {{ errors.tipo_empleado[0] }}
            </p>
          </div>

          <div v-if="form.tipo_empleado === 'Personal de Buque'">
            <label for="cedula_marina" class="block text-sm font-medium text-gray-700">Cédula Marina</label>
            <input
              type="text"
              v-model="form.cedula_marina"
              id="cedula_marina"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
            <p v-if="errors.cedula_marina" class="text-sm text-red-600 mt-1">{{ errors.cedula_marina[0] }}</p>
          </div>

          <!-- Información Laboral -->
          <div>
            <label for="cargo" class="block text-sm font-medium text-gray-700">Cargo</label>
            <input
              type="text"
              v-model="form.cargo"
              id="cargo"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
            <p v-if="errors.cargo" class="text-sm text-red-600 mt-1">{{ errors.cargo[0] }}</p>
          </div>
          <div>
            <label for="puesto" class="block text-sm font-medium text-gray-700"
              >Puesto (Opcional)</label
            >
            <input
              type="text"
              v-model="form.puesto"
              id="puesto"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
            <p v-if="errors.puesto" class="text-sm text-red-600 mt-1">{{ errors.puesto[0] }}</p>
          </div>

          <!-- Información Personal Adicional -->
          <div>
            <label for="fecha_nacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
            <input
              type="date"
              v-model="form.fecha_nacimiento"
              id="fecha_nacimiento"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
            <p v-if="errors.fecha_nacimiento" class="text-sm text-red-600 mt-1">{{ errors.fecha_nacimiento[0] }}</p>
          </div>

          <div>
            <label for="sexo" class="block text-sm font-medium text-gray-700">Sexo</label>
            <select
              v-model="form.sexo"
              id="sexo"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
              <option value="">Seleccione...</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
            </select>
            <p v-if="errors.sexo" class="text-sm text-red-600 mt-1">{{ errors.sexo[0] }}</p>
          </div>

          <div>
            <label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código Postal</label>
            <input
              type="text"
              v-model="form.codigo_postal"
              id="codigo_postal"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            />
            <p v-if="errors.codigo_postal" class="text-sm text-red-600 mt-1">{{ errors.codigo_postal[0] }}</p>
          </div>

          <div>
            <label for="tiene_hijos" class="block text-sm font-medium text-gray-700">¿Tiene Hijos?</label>
            <select
              v-model="form.tiene_hijos"
              id="tiene_hijos"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            >
              <option value="No">No</option>
              <option value="Si">Sí</option>
            </select>
            <p v-if="errors.tiene_hijos" class="text-sm text-red-600 mt-1">{{ errors.tiene_hijos[0] }}</p>
          </div>

          <div class="md:col-span-2 border-t pt-4 mt-2">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Ubicación y Datos Biográficos</h3>
          </div>

          <div>
            <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección de Habitación</label>
            <input type="text" v-model="form.direccion" id="direccion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="ciudad" class="block text-sm font-medium text-gray-700">Ciudad</label>
            <input type="text" v-model="form.ciudad" id="ciudad" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="lugar_nacimiento" class="block text-sm font-medium text-gray-700">Lugar de Nacimiento</label>
            <input type="text" v-model="form.lugar_nacimiento" id="lugar_nacimiento" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="estado_civil" class="block text-sm font-medium text-gray-700">Estado Civil</label>
            <select v-model="form.estado_civil" id="estado_civil" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Seleccione...</option>
              <option value="Soltero(a)">Soltero(a)</option>
              <option value="Casado(a)">Casado(a)</option>
              <option value="Divorciado(a)">Divorciado(a)</option>
              <option value="Viudo(a)">Viudo(a)</option>
              <option value="Concubinato">Concubinato</option>
            </select>
          </div>
          <div>
            <label for="nacionalidad" class="block text-sm font-medium text-gray-700">Nacionalidad</label>
            <input type="text" v-model="form.nacionalidad" id="nacionalidad" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>

          <div class="md:col-span-2 border-t pt-4 mt-2">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Datos Físicos y Salud</h3>
          </div>
          <div>
            <label for="estatura" class="block text-sm font-medium text-gray-700">Estatura</label>
            <input type="text" v-model="form.estatura" id="estatura" placeholder="Ej: 1.75 m" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="peso" class="block text-sm font-medium text-gray-700">Peso</label>
            <input type="text" v-model="form.peso" id="peso" placeholder="Ej: 80 kg" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="tipo_sangre" class="block text-sm font-medium text-gray-700">Tipo de Sangre</label>
            <select
              v-model="form.tipo_sangre"
              id="tipo_sangre"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Seleccione...</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
              <option value="O+">O+</option>
              <option value="O-">O-</option>
            </select>
          </div>
          <div>
            <label for="fecha_disponible" class="block text-sm font-medium text-gray-700">Fecha Disponible</label>
            <input type="date" v-model="form.fecha_disponible" id="fecha_disponible" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>

          <div class="md:col-span-2 border-t pt-4 mt-2">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Vivienda y Otros</h3>
          </div>
          <div>
            <label for="tipo_habitacion" class="block text-sm font-medium text-gray-700">Tipo de Habitación</label>
            <select v-model="form.tipo_habitacion" id="tipo_habitacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Seleccione...</option>
              <option value="Casa">Casa</option>
              <option value="Apartamento">Apartamento</option>
            </select>
          </div>
          <div>
            <label for="caracteristicas_habitacion" class="block text-sm font-medium text-gray-700">Características</label>
            <select v-model="form.caracteristicas_habitacion" id="caracteristicas_habitacion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Seleccione...</option>
              <option value="Propia">Propia</option>
              <option value="Alquilada">Alquilada</option>
            </select>
          </div>

          <div class="md:col-span-2 border-t pt-4 mt-2">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Licencias y Tallas</h3>
          </div>
          <div>
            <label for="colegiacion_nro" class="block text-sm font-medium text-gray-700">Colegiación N°</label>
            <input type="text" v-model="form.colegiacion_nro" id="colegiacion_nro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="licencia_conductor_nro" class="block text-sm font-medium text-gray-700">Licencia de Conductor</label>
            <input type="text" v-model="form.licencia_conductor_nro" id="licencia_conductor_nro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div>
            <label for="licencia_conductor_expiracion" class="block text-sm font-medium text-gray-700">Expiración Licencia</label>
            <input type="date" v-model="form.licencia_conductor_expiracion" id="licencia_conductor_expiracion" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div>
              <label for="talla_pantalon" class="block text-xs font-medium text-gray-700 text-center">Pantalón</label>
              <input type="text" v-model="form.talla_pantalon" id="talla_pantalon" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm p-1" />
            </div>
            <div>
              <label for="talla_camisa" class="block text-xs font-medium text-gray-700 text-center">Camisa</label>
              <input type="text" v-model="form.talla_camisa" id="talla_camisa" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm p-1" />
            </div>
            <div>
              <label for="talla_zapato" class="block text-xs font-medium text-gray-700 text-center">Zapato</label>
              <input type="text" v-model="form.talla_zapato" id="talla_zapato" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm p-1" />
            </div>
          </div>

          <div class="md:col-span-2 border-t pt-4 mt-2">
            <label for="habilidades_destrezas" class="block text-sm font-medium text-gray-700 mb-2">Habilidades y Destrezas Adicionales</label>
            <textarea v-model="form.habilidades_destrezas" id="habilidades_destrezas" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
          </div>

        </div>
        <div class="mt-8 flex justify-end border-t pt-6">
          <router-link to="/empleados" class="mr-4 text-gray-600 flex items-center">Cancelar</router-link>
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow"
          >
            Actualizar Empleado
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
