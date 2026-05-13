import { defineStore } from 'pinia'
import { api, getCsrfCookie } from 'src/boot/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
  }),

  getters: {
    isAuthenticated: (state) => !!state.user,
    userName: (state) => state.user?.nome || '',
  },

  actions: {
    async login(credentials) {
      await getCsrfCookie()
      await api.post('/login', credentials)
      await this.fetchUser()
    },

    async register(payload) {
      await getCsrfCookie()
      await api.post('/register', payload)
      await this.fetchUser()
    },

    async forgotPassword(email) {
      await getCsrfCookie()
      const { data } = await api.post('/forgot-password', { email })
      return data
    },

    async fetchUser() {
      try {
        const { data } = await api.get('/api/user')
        this.user = data
        localStorage.setItem('user', JSON.stringify(data))
      } catch {
        this.user = null
        localStorage.removeItem('user')
      }
    },

    async logout() {
      try {
        await api.post('/logout')
      } catch {
        // ignora erro no logout
      }
      this.user = null
      localStorage.removeItem('user')
    },
  },
})
