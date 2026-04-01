<script setup>
defineProps({
  // Definimos una propiedad llamada 'mode'.
  // Si no le decimos nada, asumirá que es para la 'navbar'.
  mode: {
    type: String,
    default: 'navbar',
  },
})
</script>

<template>
  <!-- CASO 1: Modo Login (Imagen Grande) -->
  <img
    v-if="mode === 'login'"
    src="/logo-login.png"
    alt="Avante Bureau Shipping"
    class="h-24 w-auto object-contain"
  />

  <!-- CASO 2: Modo Navbar (Imagen Pequeña) -->
  <img v-else src="/logo-nav.png" alt="Gerencia de Contrataciones" class="h-10 w-auto object-contain" />
</template>
