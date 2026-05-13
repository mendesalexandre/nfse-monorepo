import { useQuasar } from 'quasar'

export function useNotify() {
  const $q = useQuasar()

  const notify = (opts) => $q.notify(opts)

  const success = (message, opts = {}) =>
    notify({ type: 'positive', icon: 'fa-light fa-circle-check', message, ...opts })

  const error = (message, opts = {}) =>
    notify({ type: 'negative', icon: 'fa-light fa-circle-exclamation', message, ...opts })

  const warning = (message, opts = {}) =>
    notify({ type: 'warning', icon: 'fa-light fa-triangle-exclamation', message, ...opts })

  const info = (message, opts = {}) =>
    notify({ type: 'info', icon: 'fa-light fa-circle-info', message, ...opts })

  return { notify, success, error, warning, info }
}
