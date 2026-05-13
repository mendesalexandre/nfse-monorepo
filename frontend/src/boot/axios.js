import { defineBoot } from '#q-app/wrappers'
import axios from 'axios'

const api = axios.create({
  baseURL: '/', // Usa o proxy do devServer (mesmo domínio = cookies funcionam)
  timeout: 30000,
  withCredentials: true, // Sanctum session cookies
  headers: {
    Accept: 'application/json',
  },
})

/**
 * Busca o CSRF cookie do Sanctum (chamar antes do login)
 */
export const getCsrfCookie = () => {
  return api.get('/sanctum/csrf-cookie')
}

export default defineBoot(({ app, router }) => {
  // Response interceptor — tratamento de erros
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      const status = error?.response?.status

      // Session expirada ou não autenticado
      if (status === 401) {
        localStorage.removeItem('user')
        router.push({ name: 'login' })
      }

      // CSRF token mismatch — refaz o cookie e retenta
      if (status === 419) {
        return getCsrfCookie().then(() => api.request(error.config))
      }

      if (status === 403) {
        router.push({ name: 'erro-403' })
      }

      if (status >= 500) {
        router.push({ name: 'erro-500' })
      }

      return Promise.reject(error)
    }
  )

  app.config.globalProperties.$axios = axios
  app.config.globalProperties.$api = api
})

export { api }
