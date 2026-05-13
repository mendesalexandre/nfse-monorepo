import { ref } from 'vue'
import { useNotify } from './useNotify'

export function useApi() {
  const loading = ref(false)
  const error = ref(null)
  const { error: notifyError } = useNotify()

  const request = async (fn, opts = {}) => {
    const { silent = false, onError } = opts

    loading.value = true
    error.value = null

    try {
      const result = await fn()
      return result
    } catch (err) {
      error.value = err

      if (onError) {
        onError(err)
      } else if (!silent) {
        const message =
          err?.response?.data?.message ||
          err?.message ||
          'Erro inesperado. Tente novamente.'
        notifyError(message)
      }

      return null
    } finally {
      loading.value = false
    }
  }

  return { loading, error, request }
}
