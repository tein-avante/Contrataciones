/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      // 1. CONFIGURACIÓN DE FUENTES
      fontFamily: {
        sans: ['"Open Sans"', 'sans-serif'], // Esto hace que Open Sans sea la fuente por defecto
      },
      // 2. PALETA DE COLORES DE AVANTE
      colors: {
        avante: {
          primary: '#077BA1', // El Azul Cian (Botones principales, enlaces activos)
          dark: '#446078', // El Azul Oscuro/Grisáceo (Textos, encabezados)
          gray: '#7E8080', // El Gris (Textos secundarios, bordes)
          red: '#BB0808', // El Rojo (Alertas, botones de peligro)
          white: '#FFFFFF', // Blanco puro
        },
      },
    },
  },
  plugins: [],
}
