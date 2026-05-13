import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

/**
 * Store de NFS-es emitidas — listagem/visualização admin (read-only).
 * Emissão real continua sendo via X-Api-Key direto na API pública.
 */
export const useEmissaoStore = defineStore('emissao', {
  state: () => ({
    lista: [],
    paginacao: {
      page: 1,
      rowsPerPage: 20,
      rowsNumber: 0,
      sortBy: null,
      descending: false,
    },
    filtros: {
      cliente_id: null,
      status: null,
      data_de: '',
      data_ate: '',
      chave: '',
    },
    atual: null,
    carregando: false,
  }),

  actions: {
    async listar() {
      this.carregando = true
      try {
        const params = {
          page: this.paginacao.page,
          per_page: this.paginacao.rowsPerPage,
        }
        for (const [k, v] of Object.entries(this.filtros)) {
          if (v !== null && v !== '') params[k] = v
        }

        const { data } = await api.get('/api/admin/nfses', { params })
        this.lista = data.data
        this.paginacao.rowsNumber = data.meta?.total ?? data.total ?? 0
      } finally {
        this.carregando = false
      }
    },

    async buscar(id) {
      const { data } = await api.get(`/api/admin/nfses/${id}`)
      this.atual = data.data
      return this.atual
    },

    /**
     * Lista NFS-es de um cliente específico — usado na aba "Emissões" do detalhe.
     * Não toca o state principal pra não conflitar com a listagem geral.
     */
    async listarPorCliente(clienteId, limit = 50) {
      const { data } = await api.get('/api/admin/nfses', {
        params: { cliente_id: clienteId, per_page: limit },
      })
      return data.data
    },

    async baixarPdf(id) {
      const resp = await api.get(`/api/admin/nfses/${id}/pdf`, { responseType: 'blob' })
      const blob = new Blob([resp.data], { type: 'application/pdf' })
      const url = window.URL.createObjectURL(blob)
      window.open(url, '_blank')
      // Não dá pra revogar imediatamente — abriu nova aba
      setTimeout(() => window.URL.revokeObjectURL(url), 60_000)
    },

    limparFiltros() {
      this.filtros = {
        cliente_id: null,
        status: null,
        data_de: '',
        data_ate: '',
        chave: '',
      }
      this.paginacao.page = 1
    },
  },
})
