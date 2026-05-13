import { computed } from 'vue'
import { useQuasar } from 'quasar'

export function useDarkMode() {
  const $q = useQuasar()

  const isDark = computed({
    get: () => $q.dark.isActive,
    set: (val) => {
      $q.dark.set(val)
      localStorage.setItem('darkMode', val ? '1' : '0')
    },
  })

  const toggle = () => {
    isDark.value = !isDark.value
  }

  const init = () => {
    const saved = localStorage.getItem('darkMode')
    if (saved !== null) {
      $q.dark.set(saved === '1')
    }
  }

  return { isDark, toggle, init }
}
