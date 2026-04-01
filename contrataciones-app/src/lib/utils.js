// src/lib/utils.js

/**
 * Formatea una cadena de fecha estándar (YYYY-MM-DD) a formato local visual (DD/MM/YYYY)
 * evitando el desplazamiento automático de zona horaria del navegador.
 *
 * PROBLEMA TÉCNICO RESUELTO:
 * El objeto `Date` nativo de JavaScript interpreta las fechas "YYYY-MM-DD" como UTC.
 * Al mostrarlas en una zona horaria occidental (como América/Caracas UTC-4),
 * el navegador resta 4 horas, causando que la fecha visual se muestre como "el día anterior".
 * (Ej: "2025-11-25" se convierte en "24 Nov, 8:00 PM").
 *
 * SOLUCIÓN:
 * Tratamos la fecha como una cadena de texto pura, dividiéndola y reordenándola
 * manualmente, sin permitir que el motor de JavaScript realice conversiones de tiempo.
 *
 * @param {string} dateString - La fecha proveniente de la BD (puede incluir hora).
 * @returns {string} La fecha formateada DD/MM/YYYY o 'N/A' si es nula.
 */
export function formatDate(dateString) {
  if (!dateString) return 'N/A'

  // Limpieza: Si viene con hora (2025-11-25T00:00:00...), nos quedamos solo con la fecha
  const cleanDate = dateString.split('T')[0]

  // Parsing manual: Dividimos por el guion [YYYY, MM, DD]
  const [year, month, day] = cleanDate.split('-')

  // Reconstrucción: Retornamos en formato local visual
  return `${day}/${month}/${year}`
}
