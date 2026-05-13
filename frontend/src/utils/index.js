/**
 * Utilitários globais
 */

/**
 * Define o título da página no document.title
 */
export const setTitulo = (titulo = 'Página') => {
  document.title = `${titulo} - Quasar Starter`
}

/**
 * Busca endereço pelo CEP via ViaCEP
 */
export const getCep = async (cep) => {
  if (!cep) return {}
  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep.replace(/\D/g, '')}/json/`)
    return await response.json()
  } catch {
    return {}
  }
}

/**
 * Formata CPF (000.000.000-00) ou CNPJ (00.000.000/0000-00)
 */
export const formatarCpfCnpj = (valor) => {
  if (!valor) return ''
  const num = String(valor).replace(/\D/g, '')
  if (num.length <= 11) {
    return num.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
  }
  return num.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
}

/**
 * Formata valor para moeda brasileira (R$ 1.234,56)
 */
export const formatarDinheiro = (value) => {
  if (value === undefined || value === null) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

/**
 * Formata número com separadores brasileiros (1.234,56)
 */
export const formatarNumero = (value, decimals = 2) => {
  if (value === undefined || value === null) return '0'
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(value)
}

/**
 * Formata telefone: (00) 0000-0000 ou (00) 00000-0000
 */
export const formatarTelefone = (valor) => {
  if (!valor) return ''
  const num = String(valor).replace(/\D/g, '')
  if (num.length === 11) {
    return num.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
  }
  if (num.length === 10) {
    return num.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3')
  }
  return valor
}

/**
 * Remove tags HTML, retorna texto puro
 */
export const removeHTML = (html) => {
  if (!html) return ''
  const doc = new DOMParser().parseFromString(html, 'text/html')
  return doc.body.textContent || ''
}

/**
 * Trunca texto com reticências
 */
export const truncar = (texto, max = 100) => {
  if (!texto || texto.length <= max) return texto || ''
  return texto.substring(0, max) + '...'
}

/**
 * Copia texto para a área de transferência
 */
export const copiar = async (texto) => {
  try {
    await navigator.clipboard.writeText(texto)
    return true
  } catch {
    return false
  }
}

/**
 * Debounce simples
 */
export const debounce = (fn, delay = 300) => {
  let timer
  return (...args) => {
    clearTimeout(timer)
    timer = setTimeout(() => fn(...args), delay)
  }
}
