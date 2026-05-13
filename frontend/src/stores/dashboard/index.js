import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    metricas: {
      clientes: { total: 0, ativos: 0, inativos: 0, cert_expirando_30d: 0, cert_vencido: 0 },
      emissoes: {
        total: 0, ultimas_24h: 0, ultimos_7d: 0,
        emitidas: 0, rejeitadas: 0, canceladas: 0, erro: 0, pendente: 0,
      },
      audit: { total: 0, ultimas_24h: 0 },
    },
    carregando: false,
  }),

  actions: {
    async carregar() {
      this.carregando = true
      try {
        const { data } = await api.get('/api/admin/dashboard')
        this.metricas = data
      } finally {
        this.carregando = false
      }
    },
  },
})
