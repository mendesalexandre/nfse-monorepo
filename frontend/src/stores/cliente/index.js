import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

/**
 * Store de clientes da API multi-tenant. Cobre o CRUD admin + operações de
 * credencial (regenerar / revogar) + upload de cert.
 */
export const useClienteStore = defineStore('cliente', {
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
      busca: '',
      is_ativo: null,
    },
    atual: null,
    carregando: false,
    salvando: false,
  }),

  actions: {
    async listar() {
      this.carregando = true
      try {
        const params = {
          page: this.paginacao.page,
          per_page: this.paginacao.rowsPerPage,
        }
        if (this.filtros.busca) params.busca = this.filtros.busca
        if (this.filtros.is_ativo !== null && this.filtros.is_ativo !== '')
          params.is_ativo = this.filtros.is_ativo

        const { data } = await api.get('/api/admin/clientes', { params })
        this.lista = data.data
        this.paginacao.rowsNumber = data.meta?.total ?? data.total ?? 0
      } finally {
        this.carregando = false
      }
    },

    async buscar(id) {
      const { data } = await api.get(`/api/admin/clientes/${id}`)
      this.atual = data.data
      return this.atual
    },

    async criar(payload) {
      this.salvando = true
      try {
        const { data } = await api.post('/api/admin/clientes', payload)
        return data // { cliente, credenciais }
      } finally {
        this.salvando = false
      }
    },

    async atualizar(id, payload) {
      this.salvando = true
      try {
        const { data } = await api.put(`/api/admin/clientes/${id}`, payload)
        this.atual = data.data
        return this.atual
      } finally {
        this.salvando = false
      }
    },

    async remover(id) {
      const { data } = await api.delete(`/api/admin/clientes/${id}`)
      return data
    },

    async uploadCert(id, file, senha) {
      this.salvando = true
      try {
        const fd = new FormData()
        fd.append('pfx', file)
        fd.append('senha', senha)
        const { data } = await api.post(`/api/admin/clientes/${id}/cert`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        if (data?.cliente?.data) this.atual = data.cliente.data
        else if (data?.cliente) this.atual = data.cliente
        return data
      } finally {
        this.salvando = false
      }
    },

    async regenerarApiKey(id) {
      const { data } = await api.post(`/api/admin/clientes/${id}/regenerar-api-key`)
      return data // { api_key, aviso }
    },

    async regenerarClientSecret(id) {
      const { data } = await api.post(`/api/admin/clientes/${id}/regenerar-client-secret`)
      return data // { client_secret, aviso }
    },

    async revogar(id) {
      const { data } = await api.post(`/api/admin/clientes/${id}/revogar`)
      return data
    },

    limparFiltros() {
      this.filtros = { busca: '', is_ativo: null }
      this.paginacao.page = 1
    },
  },
})
