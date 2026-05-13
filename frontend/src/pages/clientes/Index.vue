<template>
  <q-page padding>
    <v-page-header
      titulo="Clientes"
      subtitulo="Empresas autorizadas a emitir NFS-e via API"
      :breadcrumbs="[
        { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
        { label: 'Clientes' },
      ]"
    >
      <template #acoes>
        <q-btn unelevated color="primary" label="Novo cliente" icon="fa-light fa-plus" size="sm"
          :to="{ name: 'clientes-criar' }" />
      </template>
    </v-page-header>

    <v-filter label="Filtros" :total-ativos="filtrosAtivos" iniciar-aberto @limpar="limparFiltros">
      <div class="row q-col-gutter-md">
        <div class="col-md-6 col-sm-12">
          <v-label label="Buscar" />
          <q-input
            v-model="filtros.busca"
            outlined
            dense
            clearable
            placeholder="Nome, CNPJ ou e-mail..."
            @keyup.enter="buscar"
          >
            <template #prepend>
              <q-icon name="fa-light fa-magnifying-glass" />
            </template>
          </q-input>
        </div>
        <div class="col-md-3 col-sm-6">
          <v-label label="Status" />
          <v-select
            v-model="filtros.is_ativo"
            :options="[
              { label: 'Todos', value: null },
              { label: 'Ativos', value: true },
              { label: 'Inativos', value: false },
            ]"
            option-label="label"
            option-value="value"
          />
        </div>
        <div class="col-md-3 col-sm-6 self-end">
          <q-btn unelevated color="primary" label="Filtrar" icon="fa-light fa-filter" class="full-width" @click="buscar" />
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
        <template #body-cell-cnpj="props">
          <q-td :props="props">
            <span class="text-mono">{{ formatarCnpj(props.row.cnpj) }}</span>
          </q-td>
        </template>

        <template #body-cell-cert="props">
          <q-td :props="props">
            <cert-semaforo :validade="props.row.cert_validade" />
          </q-td>
        </template>

        <template #body-cell-ambiente="props">
          <q-td :props="props">
            <q-chip
              dense
              :color="props.row.ambiente === 'producao' ? 'positive' : 'warning'"
              text-color="white"
            >
              {{ props.row.ambiente === 'producao' ? 'PROD' : 'HOMOLOG' }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-is_ativo="props">
          <q-td :props="props">
            <v-status-badge :valor="props.row.is_ativo" label-ativo="Ativo" label-inativo="Inativo" />
          </q-td>
        </template>

        <template #body-cell-acoes="props">
          <q-td :props="props" class="text-right">
            <q-btn flat dense round icon="fa-light fa-eye" size="sm" color="primary"
              :to="{ name: 'clientes-detalhe', params: { id: props.row.id } }">
              <q-tooltip>Ver detalhes</q-tooltip>
            </q-btn>
            <q-btn flat dense round icon="fa-light fa-pen" size="sm" color="grey-7"
              :to="{ name: 'clientes-editar', params: { id: props.row.id } }">
              <q-tooltip>Editar</q-tooltip>
            </q-btn>
            <q-btn flat dense round icon="fa-light fa-trash" size="sm" color="negative"
              @click="confirmarRemocao(props.row)">
              <q-tooltip>Remover</q-tooltip>
            </q-btn>
          </q-td>
        </template>

        <template #no-data>
          <div class="full-width q-pa-xl text-center">
            <v-empty-state icon="fa-light fa-building" titulo="Nenhum cliente encontrado"
              descricao="Crie o primeiro cliente para começar a emitir NFS-e." />
          </div>
        </template>
      </q-table>
    </q-card>

    <v-confirm
      v-model="confirmRemocao"
      type="danger"
      titulo="Remover cliente?"
      :mensagem="`Tem certeza que deseja remover '${clienteRemovendo?.nome_empresa}'? Esta ação pode ser desfeita restaurando do banco.`"
      label-confirmar="Remover"
      :loading="removendo"
      @confirm="removerConfirmado"
      @cancel="confirmRemocao = false"
    />
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useClienteStore } from 'src/stores/cliente'
import { useNotify } from 'src/composables/useNotify'
import CertSemaforo from 'src/components/CertSemaforo.vue'

const store = useClienteStore()
const { lista, paginacao, filtros, carregando } = storeToRefs(store)
const { success, error } = useNotify()

const confirmRemocao = ref(false)
const removendo = ref(false)
const clienteRemovendo = ref(null)

const colunas = [
  { name: 'nome_empresa', label: 'Nome', field: 'nome_empresa', align: 'left', sortable: true },
  { name: 'cnpj', label: 'CNPJ', field: 'cnpj', align: 'left' },
  { name: 'email', label: 'E-mail', field: 'email', align: 'left' },
  { name: 'cert', label: 'Certificado', field: 'cert_validade', align: 'left' },
  { name: 'ambiente', label: 'Ambiente', field: 'ambiente', align: 'center' },
  { name: 'is_ativo', label: 'Status', field: 'is_ativo', align: 'center' },
  { name: 'acoes', label: 'Ações', field: 'id', align: 'right' },
]

const filtrosAtivos = computed(() => {
  let n = 0
  if (filtros.value.busca) n++
  if (filtros.value.is_ativo !== null && filtros.value.is_ativo !== '') n++
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

const formatarCnpj = (cnpj) => {
  if (!cnpj || cnpj.length !== 14) return cnpj
  return `${cnpj.slice(0, 2)}.${cnpj.slice(2, 5)}.${cnpj.slice(5, 8)}/${cnpj.slice(8, 12)}-${cnpj.slice(12)}`
}

const confirmarRemocao = (cliente) => {
  clienteRemovendo.value = cliente
  confirmRemocao.value = true
}

const removerConfirmado = async () => {
  removendo.value = true
  try {
    await store.remover(clienteRemovendo.value.id)
    success('Cliente removido')
    confirmRemocao.value = false
    await store.listar()
  } catch (e) {
    error(e?.response?.data?.message || 'Erro ao remover')
  } finally {
    removendo.value = false
  }
}

onMounted(() => store.listar())
</script>

<style scoped>
.text-mono {
  font-family: 'Menlo', 'Monaco', monospace;
  font-size: 0.875rem;
}
</style>
