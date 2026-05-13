/**
 * Utilitário centralizado para manipulação de datas.
 * Formato padrão interno: YYYY-MM-DD (ISO)
 * Formato de exibição: DD/MM/YYYY (BR)
 */

/**
 * Converte YYYY-MM-DD → DD/MM/YYYY
 */
export const toDisplay = (val) => {
  if (!val) return ''
  if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
    const [y, m, d] = val.split('-')
    return `${d}/${m}/${y}`
  }
  return val
}

/**
 * Converte DD/MM/YYYY → YYYY-MM-DD
 */
export const toIso = (val) => {
  if (!val) return ''
  if (/^\d{2}\/\d{2}\/\d{4}$/.test(val)) {
    const [d, m, y] = val.split('/')
    return `${y}-${m}-${d}`
  }
  return val
}

/**
 * Valida se a string DD/MM/YYYY é uma data real
 */
export const isValidDate = (val) => {
  if (!/^\d{2}\/\d{2}\/\d{4}$/.test(val)) return false
  const [d, m, y] = val.split('/').map(Number)
  const date = new Date(y, m - 1, d)
  return date.getFullYear() === y && date.getMonth() === m - 1 && date.getDate() === d
}

/**
 * Retorna a data atual em YYYY-MM-DD
 */
export const today = () => {
  const d = new Date()
  const dd = String(d.getDate()).padStart(2, '0')
  const mm = String(d.getMonth() + 1).padStart(2, '0')
  return `${d.getFullYear()}-${mm}-${dd}`
}

/**
 * Retorna a data atual em DD/MM/YYYY
 */
export const todayDisplay = () => toDisplay(today())

/**
 * Formata uma data ISO para exibição com hora
 * YYYY-MM-DDTHH:mm:ss → DD/MM/YYYY HH:mm
 */
export const toDisplayDateTime = (val) => {
  if (!val) return ''
  const date = new Date(val)
  if (isNaN(date.getTime())) return val
  const dd = String(date.getDate()).padStart(2, '0')
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const yyyy = date.getFullYear()
  const hh = String(date.getHours()).padStart(2, '0')
  const min = String(date.getMinutes()).padStart(2, '0')
  return `${dd}/${mm}/${yyyy} ${hh}:${min}`
}

/**
 * Diferença em dias entre duas datas ISO (YYYY-MM-DD)
 */
export const diffDays = (dateA, dateB) => {
  const a = new Date(dateA)
  const b = new Date(dateB)
  return Math.round((a - b) / (1000 * 60 * 60 * 24))
}

/**
 * Adiciona dias a uma data ISO, retorna ISO
 */
export const addDays = (val, days) => {
  const date = new Date(val)
  date.setDate(date.getDate() + days)
  const dd = String(date.getDate()).padStart(2, '0')
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  return `${date.getFullYear()}-${mm}-${dd}`
}
