<template>
  <q-page padding>
    <v-page-header
      titulo="Emissões"
      subtitulo="Todas as NFS-es processadas pela API"
      :breadcrumbs="[
        { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
        { label: 'Emissões' },
      ]"
    />

    <v-filter label="Filtros" :total-ativos="filtrosAtivos" iniciar-aberto @limpar="limparFiltros">
      <div class="row q-col-gutter-md">
        <div class="col-md-4 col-sm-6">
          <v-label label="Cliente" />
          <v-select
            v-model="filtros.cliente_id"
            :options="opcoesClientes"
            option-label="nome_empresa"
            option-value="id"
            placeholder="Todos"
          />
        </div>
        <div class="col-md-2 col-sm-6">
          <v-label label="Status" />
          <v-select
            v-model="filtros.status"
            :options="opcoesStatus"
            option-label="label"
            option-value="value"
          />
        </div>
        <div class="col-md-2 col-sm-6">
          <v-label label="De" />
          <v-date v-model="filtros.data_de" picker />
        </div>
        <div class="col-md-2 col-sm-6">
          <v-label label="Até" />
          <v-date v-model="filtros.data_ate" picker />
        </div>
        <div class="col-md-2 col-sm-12 self-end">
          <q-btn unelevated color="primary" label="Filtrar" icon="fa-light fa-filter" class="full-width" @click="buscar" />
        </div>
        <div class="col-12">
          <v-label label="Buscar por chave" />
          <q-input v-model="filtros.chave" outlined dense placeholder="Chave de acesso (parcial)..." clearable @keyup.enter="buscar">
            <template #prepend>
              <q-icon name="fa-light fa-magnifying-glass" />
            </template>
          </q-input>
        </div>
      </div>
    </v-filter>

    <q-card flat bordered class="q-mt-md">
      <q-table
        v-model:pagination="paginacao"
        :rows="lista"
        :columns="colunas"
        :loading="carregando"
        row-key="id"
        flat
        binary-state-sort
        @request="onRequest"
      >
        <template #body-cell-data="props">
          <q-td :props="props">{{ formatarData(props.row.data_criacao) }}</q-td>
        </template>

        <template #body-cell-cliente="props">
          <q-td :props="props">{{ props.row.cliente_nome || '—' }}</q-td>
        </template>

        <template #body-cell-chave="props">
          <q-td :props="props">
            <span v-if="props.row.chave_acesso" class="text-mono text-caption">
              {{ props.row.chave_acesso.slice(0, 8) }}…{{ props.row.chave_acesso.slice(-6) }}
            </span>
            <span v-else class="text-grey">—</span>
          </q-td>
        </template>

        <template #body-cell-status="props">
          <q-td :props="props">
            <status-nfse-badge :status="props.row.status" />
          </q-td>
        </template>

        <template #body-cell-valor="props">
          <q-td :props="props">R$ {{ formatarMoeda(props.row.valor_servicos) }}</q-td>
        </template>

        <template #body-cell-acoes="props">
          <q-td :props="props" class="text-right">
            <q-btn flat dense round icon="fa-light fa-eye" size="sm" color="primary"
              :to="{ name: 'emissoes-detalhe', params: { id: props.row.id } }">
              <q-tooltip>Ver detalhes</q-tooltip>
            </q-btn>
            <q-btn v-if="props.row.chave_acesso" flat dense round icon="fa-light fa-file-pdf" size="sm" color="grey-7"
              @click="baixarPdf(props.row.id)">
              <q-tooltip>Baixar DANFSe</q-tooltip>
            </q-btn>
          </q-td>
        </template>

        <template #no-data>
          <div class="full-width q-pa-xl text-center">
            <v-empty-state icon="fa-light fa-file-invoice" titulo="Nenhuma NFS-e encontrada" />
          </div>
        </template>
      </q-table>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useEmissaoStore } from 'src/stores/emissao'
import { useClienteStore } from 'src/stores/cliente'
import { useNotify } from 'src/composables/useNotify'
import StatusNfseBadge from 'src/components/StatusNfseBadge.vue'

const store = useEmissaoStore()
const clienteStore = useClienteStore()
const { lista, paginacao, filtros, carregando } = storeToRefs(store)
const { error } = useNotify()

const opcoesClientes = ref([])

const opcoesStatus = [
  { label: 'Todos', value: null },
  { label: 'Emitida', value: 'emitida' },
  { label: 'Cancelada', value: 'cancelada' },
  { label: 'Rejeitada', value: 'rejeitada' },
  { label: 'Erro', value: 'erro' },
  { label: 'Pendente', value: 'pendente' },
  { label: 'Substituída', value: 'substituida' },
]

const colunas = [
  { name: 'data', label: 'Data', field: 'data_criacao', align: 'left' },
  { name: 'cliente', label: 'Cliente', field: 'cliente_nome', align: 'left' },
  { name: 'numero_nfse', label: 'NFS-e', field: 'numero_nfse', align: 'left' },
  { name: 'chave', label: 'Chave', field: 'chave_acesso', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'valor', label: 'Valor', field: 'valor_servicos', align: 'right' },
  { name: 'acoes', label: '', field: 'id', align: 'right' },
]

const filtrosAtivos = computed(() => {
  let n = 0
  for (const v of Object.values(filtros.value)) {
    if (v !== null && v !== '') n++
  }
  return n
})

const buscar = async () => {
  paginacao.value.page = 1
  await store.listar()
}

const limparFiltros = async () => {
  store.limparFiltros()
  await store.listar()
}

const onRequest = async (props) => {
  paginacao.value.page = props.pagination.page
  paginacao.value.rowsPerPage = props.pagination.rowsPerPage
  paginacao.value.sortBy = props.pagination.sortBy
  paginacao.value.descending = props.pagination.descending
  await store.listar()
}

const formatarData = (iso) => {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

const formatarMoeda = (valor) => Number(valor || 0).toLocaleString('pt-BR', {
  minimumFractionDigits: 2, maximumFractionDigits: 2,
})

const baixarPdf = async (id) => {
  try {
    await store.baixarPdf(id)
  } catch (e) {
    error('Erro ao baixar PDF: ' + (e?.response?.data?.message || e.message))
  }
}

const carregarOpcoesClientes = async () => {
  try {
    // Busca até 100 clientes pra montar select
    clienteStore.paginacao.rowsPerPage = 100
    await clienteStore.listar()
    opcoesClientes.value = [{ id: null, nome_empresa: 'Todos' }, ...clienteStore.lista]
  } catch (e) {
    // silently
  }
}

onMounted(async () => {
  await Promise.all([store.listar(), carregarOpcoesClientes()])
})
</script>

<style scoped>
.text-mono {
  font-family: 'Menlo', 'Monaco', monospace;
}
</style>
