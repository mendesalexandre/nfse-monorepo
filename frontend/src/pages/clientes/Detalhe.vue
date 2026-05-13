<template>
  <q-page padding>
    <v-page-header
      :titulo="atual?.nome_empresa || 'Carregando…'"
      :subtitulo="`CNPJ: ${formatarCnpj(atual?.cnpj)} · ${atual?.ambiente?.toUpperCase() || ''}`"
      :breadcrumbs="[
        { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
        { label: 'Clientes', to: { name: 'clientes' } },
        { label: atual?.nome_empresa || '' },
      ]"
    >
      <template #acoes>
        <q-btn outline color="grey-7" label="Voltar" icon="fa-light fa-arrow-left" size="sm"
          :to="{ name: 'clientes' }" />
        <q-btn unelevated color="primary" label="Editar" icon="fa-light fa-pen" size="sm"
          :to="{ name: 'clientes-editar', params: { id: idCliente } }" />
      </template>
    </v-page-header>

    <q-card flat bordered>
      <q-tabs v-model="abaAtiva" align="left" dense narrow-indicator>
        <q-tab name="dados" icon="fa-light fa-circle-info" label="Dados" />
        <q-tab name="credenciais" icon="fa-light fa-key" label="Credenciais" />
        <q-tab name="certificado" icon="fa-light fa-shield-check" label="Certificado" />
        <q-tab name="emissoes" icon="fa-light fa-file-invoice" label="Emissões" />
      </q-tabs>
      <q-separator />

      <q-tab-panels v-model="abaAtiva" animated>
        <!-- ABA: Dados -->
        <q-tab-panel name="dados">
          <div v-if="atual" class="row q-col-gutter-md">
            <div class="col-md-6 col-sm-12">
              <CampoInfo label="Nome" :valor="atual.nome_empresa" />
              <CampoInfo label="CNPJ" :valor="formatarCnpj(atual.cnpj)" mono />
              <CampoInfo label="E-mail" :valor="atual.email" />
              <CampoInfo label="Telefone" :valor="atual.telefone" />
            </div>
            <div class="col-md-6 col-sm-12">
              <CampoInfo label="Razão social (prestador)" :valor="atual.razao_social_prestador" />
              <CampoInfo label="Inscrição municipal" :valor="atual.inscricao_municipal" />
              <CampoInfo label="Município" :valor="`${atual.codigo_municipio_ibge} (${atual.uf})`" />
              <CampoInfo label="Endereço" :valor="enderecoFormatado" />
            </div>
            <div class="col-12">
              <q-separator class="q-my-md" />
              <div class="text-subtitle2 text-weight-bold q-mb-sm">Configuração</div>
              <div class="row q-gutter-md">
                <q-chip :color="atual.ambiente === 'producao' ? 'positive' : 'warning'" text-color="white" dense>
                  {{ atual.ambiente === 'producao' ? 'Produção' : 'Homologação' }}
                </q-chip>
                <q-chip dense :color="atual.is_ativo ? 'positive' : 'grey'" text-color="white">
                  {{ atual.is_ativo ? 'Ativo' : 'Inativo' }}
                </q-chip>
                <q-chip dense :color="atual.incluir_ibscbs ? 'info' : 'grey-5'" text-color="white">
                  {{ atual.incluir_ibscbs ? 'IBS/CBS habilitado' : 'IBS/CBS desabilitado' }}
                </q-chip>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- ABA: Credenciais -->
        <q-tab-panel name="credenciais">
          <v-alert type="warning" message="Após gerar uma nova credencial, ela só será mostrada UMA vez. Copie imediatamente." />

          <div class="q-mt-md">
            <v-label label="X-Api-Key (preview)" />
            <q-input :model-value="atual?.api_key_preview || '—'" readonly outlined dense />

            <v-label label="Client ID" class="q-mt-md" />
            <q-input :model-value="atual?.client_id || '—'" readonly outlined dense>
              <template #append>
                <v-copy v-if="atual?.client_id" :valor="atual.client_id" tooltip="Copiar" />
              </template>
            </q-input>
          </div>

          <q-separator class="q-my-lg" />

          <div class="text-subtitle2 text-weight-bold q-mb-sm">Ações</div>
          <div class="row q-gutter-sm">
            <q-btn outline color="primary" icon="fa-light fa-rotate" label="Regenerar API Key"
              @click="regenerar('api-key')" />
            <q-btn outline color="primary" icon="fa-light fa-rotate" label="Regenerar Client Secret"
              @click="regenerar('client-secret')" />
            <q-btn outline color="negative" icon="fa-light fa-ban" label="Revogar tudo"
              @click="confirmRevogar = true" />
          </div>
        </q-tab-panel>

        <!-- ABA: Certificado -->
        <q-tab-panel name="certificado">
          <div class="row q-col-gutter-md items-center q-mb-md">
            <div class="col-md-6 col-sm-12">
              <div class="text-subtitle2 text-weight-bold q-mb-sm">Certificado atual</div>
              <CampoInfo label="CNPJ do cert" :valor="atual?.cert_cnpj" mono />
              <CampoInfo label="Validade" :valor="atual?.cert_validade" />
              <div class="q-mt-sm">
                <cert-semaforo :validade="atual?.cert_validade" />
              </div>
            </div>
            <div class="col-md-6 col-sm-12">
              <q-banner v-if="!atual?.tem_certificado" class="bg-amber-1 text-amber-9" rounded>
                <template #avatar>
                  <q-icon name="fa-light fa-triangle-exclamation" color="amber" />
                </template>
                Cliente ainda sem certificado A1. Faça upload abaixo.
              </q-banner>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <div class="text-subtitle2 text-weight-bold q-mb-sm">Subir novo certificado</div>
          <div class="row q-col-gutter-md">
            <div class="col-md-7 col-sm-12">
              <v-label label="Arquivo PFX" obrigatorio />
              <q-file v-model="certForm.pfx" accept=".pfx,.p12" outlined dense
                clearable label="Selecione o arquivo .pfx">
                <template #prepend>
                  <q-icon name="fa-light fa-file-shield" />
                </template>
              </q-file>
            </div>
            <div class="col-md-5 col-sm-12">
              <v-label label="Senha do certificado" obrigatorio />
              <v-password v-model="certForm.senha" />
            </div>
          </div>

          <div class="q-mt-md">
            <q-btn unelevated color="primary" label="Enviar certificado" icon="fa-light fa-upload"
              :loading="enviandoCert" :disable="!certForm.pfx || !certForm.senha"
              @click="enviarCert" />
          </div>
        </q-tab-panel>

        <!-- ABA: Emissões -->
        <q-tab-panel name="emissoes">
          <div v-if="carregandoEmissoes" class="row justify-center q-pa-xl">
            <q-spinner-dots color="primary" size="40px" />
          </div>
          <div v-else-if="!emissoesCliente.length">
            <v-empty-state icon="fa-light fa-file-invoice" titulo="Sem emissões"
              descricao="Este cliente ainda não emitiu nenhuma NFS-e." />
          </div>
          <q-table v-else flat dense
            :rows="emissoesCliente"
            :columns="colunasEmissoes"
            row-key="id"
            :pagination="{ rowsPerPage: 25 }"
            >
            <template #body-cell-status="props">
              <q-td :props="props">
                <status-nfse-badge :status="props.row.status" />
              </q-td>
            </template>
            <template #body-cell-chave="props">
              <q-td :props="props">
                <span class="text-mono text-caption">{{ props.row.chave_acesso ? props.row.chave_acesso.slice(0, 8) + '…' + props.row.chave_acesso.slice(-6) : '—' }}</span>
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
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <credencial-modal v-model="modalCredenciais" :titulo="tituloModalCredencial"
      :credenciais="credenciaisGeradas" />

    <v-confirm v-model="confirmRevogar" type="danger"
      titulo="Revogar todas as credenciais?"
      mensagem="Ao revogar, a API key e o client secret atuais ficarão inutilizáveis. O cliente NÃO conseguirá mais emitir NFS-e até gerar novas credenciais."
      label-confirmar="Revogar"
      :loading="revogando"
      @confirm="revogarConfirmado"
      @cancel="confirmRevogar = false"
    />
  </q-page>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch, h } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { useClienteStore } from 'src/stores/cliente'
import { useEmissaoStore } from 'src/stores/emissao'
import { useNotify } from 'src/composables/useNotify'
import CertSemaforo from 'src/components/CertSemaforo.vue'
import StatusNfseBadge from 'src/components/StatusNfseBadge.vue'
import CredencialModal from 'src/components/CredencialModal.vue'

const route = useRoute()
const idCliente = computed(() => route.params.id)

const store = useClienteStore()
const emissaoStore = useEmissaoStore()
const { atual } = storeToRefs(store)
const { success, error } = useNotify()

const abaAtiva = ref('dados')
const certForm = reactive({ pfx: null, senha: '' })
const enviandoCert = ref(false)
const modalCredenciais = ref(false)
const tituloModalCredencial = ref('Nova credencial')
const credenciaisGeradas = ref({})
const confirmRevogar = ref(false)
const revogando = ref(false)

const emissoesCliente = ref([])
const carregandoEmissoes = ref(false)

const colunasEmissoes = [
  { name: 'data', label: 'Data', field: (r) => formatarData(r.data_criacao), align: 'left' },
  { name: 'chave', label: 'Chave', field: 'chave_acesso', align: 'left' },
  { name: 'numero', label: 'NFS-e', field: 'numero_nfse', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'center' },
  { name: 'valor', label: 'Valor', field: 'valor_servicos', align: 'right' },
  { name: 'acoes', label: '', field: 'id', align: 'right' },
]

const enderecoFormatado = computed(() => {
  if (!atual.value) return ''
  const partes = [
    atual.value.logradouro,
    atual.value.numero,
    atual.value.complemento,
    atual.value.bairro,
    atual.value.cep,
  ].filter(Boolean)
  return partes.join(', ')
})

const formatarCnpj = (cnpj) => {
  if (!cnpj || cnpj.length !== 14) return cnpj || '—'
  return `${cnpj.slice(0, 2)}.${cnpj.slice(2, 5)}.${cnpj.slice(5, 8)}/${cnpj.slice(8, 12)}-${cnpj.slice(12)}`
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

const carregarEmissoes = async () => {
  carregandoEmissoes.value = true
  try {
    emissoesCliente.value = await emissaoStore.listarPorCliente(idCliente.value, 50)
  } catch (e) {
    error('Erro ao carregar emissões: ' + (e?.response?.data?.message || e.message))
  } finally {
    carregandoEmissoes.value = false
  }
}

const enviarCert = async () => {
  enviandoCert.value = true
  try {
    await store.uploadCert(idCliente.value, certForm.pfx, certForm.senha)
    success('Certificado atualizado com sucesso')
    certForm.pfx = null
    certForm.senha = ''
    await store.buscar(idCliente.value)
  } catch (e) {
    error(e?.response?.data?.message || 'Erro ao enviar certificado')
  } finally {
    enviandoCert.value = false
  }
}

const regenerar = async (tipo) => {
  try {
    if (tipo === 'api-key') {
      const resp = await store.regenerarApiKey(idCliente.value)
      tituloModalCredencial.value = 'Nova X-Api-Key'
      credenciaisGeradas.value = { 'X-Api-Key': resp.api_key }
    } else {
      const resp = await store.regenerarClientSecret(idCliente.value)
      tituloModalCredencial.value = 'Novo Client Secret'
      credenciaisGeradas.value = { 'Client Secret': resp.client_secret }
    }
    modalCredenciais.value = true
    await store.buscar(idCliente.value)
  } catch (e) {
    error(e?.response?.data?.message || 'Erro ao regenerar')
  }
}

const revogarConfirmado = async () => {
  revogando.value = true
  try {
    await store.revogar(idCliente.value)
    success('Credenciais revogadas')
    confirmRevogar.value = false
    await store.buscar(idCliente.value)
  } catch (e) {
    error(e?.response?.data?.message || 'Erro ao revogar')
  } finally {
    revogando.value = false
  }
}

// Componente helper inline pra exibir info: "label: valor"
const CampoInfo = {
  props: {
    label: String,
    valor: { type: [String, Number, null], default: null },
    mono: Boolean,
  },
  setup(props) {
    return () => h('div', { class: 'q-mb-sm' }, [
      h('div', { class: 'text-caption text-grey-7' }, props.label),
      h('div', { class: ['text-body1', props.mono ? 'text-mono' : ''] }, props.valor || '—'),
    ])
  },
}

// Carrega emissões só quando a aba é ativada
watch(abaAtiva, (val) => {
  if (val === 'emissoes' && !emissoesCliente.value.length) {
    carregarEmissoes()
  }
})

onMounted(async () => {
  await store.buscar(idCliente.value)
})
</script>

<style scoped>
.text-mono {
  font-family: 'Menlo', 'Monaco', monospace;
}
</style>
