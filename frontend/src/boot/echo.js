import { defineBoot } from '#q-app/wrappers'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export default defineBoot(({ app }) => {
  window.Pusher = Pusher

  const echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.REVERB_APP_KEY,
    wsHost: process.env.REVERB_HOST,
    wsPort: process.env.REVERB_PORT,
    wssPort: process.env.REVERB_PORT,
    forceTLS: process.env.REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
  })

  app.config.globalProperties.$echo = echo
})

export { }
