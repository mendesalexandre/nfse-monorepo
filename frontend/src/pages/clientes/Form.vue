<template>
  <q-page padding>
    <v-page-header
      :titulo="modo === 'criar' ? 'Novo cliente' : 'Editar cliente'"
      :subtitulo="modo === 'criar' ? 'Cadastrar empresa autorizada a emitir NFS-e' : `Editar ${form.nome_empresa || ''}`"
      :breadcrumbs="[
        { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
        { label: 'Clientes', to: { name: 'clientes' } },
        { label: modo === 'criar' ? 'Novo' : 'Editar' },
      ]"
    />

    <q-form @submit.prevent="salvar">
      <!-- Dados da empresa -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-subtitle1 text-weight-bold q-mb-md">Dados da empresa</div>
          <div class="row q-col-gutter-md">
            <div class="col-md-8 col-sm-12">
              <v-label label="Nome da empresa" obrigatorio />
              <q-input v-model="form.nome_empresa" outlined dense />
            </div>
            <div class="col-md-4 col-sm-12">
              <v-label label="CNPJ" obrigatorio />
              <v-cnpj v-model="form.cnpj" />
            </div>
            <div class="col-md-6 col-sm-12">
              <v-label label="E-mail" obrigatorio />
              <q-input v-model="form.email" type="email" outlined dense />
            </div>
            <div class="col-md-6 col-sm-12">
              <v-label label="Telefone" />
              <v-telefone v-model="form.telefone" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Endereço prestador -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-subtitle1 text-weight-bold q-mb-md">Endereço do prestador</div>
          <div class="row q-col-gutter-md">
            <div class="col-md-3 col-sm-6">
              <v-label label="CEP" obrigatorio />
              <v-cep v-model="form.cep" @endereco="aplicarCep" />
            </div>
            <div class="col-md-7 col-sm-12">
              <v-label label="Logradouro" obrigatorio />
              <q-input v-model="form.logradouro" outlined dense />
            </div>
            <div class="col-md-2 col-sm-6">
              <v-label label="Número" obrigatorio />
              <q-input v-model="form.numero" outlined dense />
            </div>
            <div class="col-md-4 col-sm-6">
              <v-label label="Bairro" obrigatorio />
              <q-input v-model="form.bairro" outlined dense />
            </div>
            <div class="col-md-4 col-sm-6">
              <v-label label="Complemento" />
              <q-input v-model="form.complemento" outlined dense />
            </div>
            <div class="col-md-2 col-sm-6">
              <v-label label="UF" obrigatorio />
              <q-input v-model="form.uf" outlined dense maxlength="2" upper-case
                @update:model-value="(v) => (form.uf = (v || '').toUpperCase())" />
            </div>
            <div class="col-md-2 col-sm-6">
              <v-label label="Município (IBGE)" obrigatorio ajuda="Código IBGE de 7 dígitos" />
              <q-input v-model="form.codigo_municipio_ibge" outlined dense maxlength="7" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Tributário -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-subtitle1 text-weight-bold q-mb-md">Tributário</div>
          <div class="row q-col-gutter-md">
            <div class="col-md-8 col-sm-12">
              <v-label label="Razão social do prestador" obrigatorio />
              <q-input v-model="form.razao_social_prestador" outlined dense />
            </div>
            <div class="col-md-4 col-sm-12">
              <v-label label="Inscrição municipal" obrigatorio />
              <q-input v-model="form.inscricao_municipal" outlined dense />
            </div>
            <div class="col-md-6 col-sm-12">
              <v-label label="Regime especial de tributação" obrigatorio
                ajuda="0 = Nenhum (recomendado p/ permitir vDedRed)" />
              <v-select
                v-model="form.regime_especial_tributacao"
                :options="opcoesRegime"
                option-label="label"
                option-value="value"
              />
            </div>
            <div class="col-md-6 col-sm-12">
              <v-label label="Situação Simples Nacional" obrigatorio />
              <v-select
                v-model="form.simples_nacional"
                :options="opcoesSimples"
                option-label="label"
                option-value="value"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Toggles -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-subtitle1 text-weight-bold q-mb-md">Configuração</div>
          <div class="row q-col-gutter-md items-center">
            <div class="col-md-4 col-sm-6">
              <v-label label="Ambiente" obrigatorio />
              <q-option-group
                v-model="form.ambiente"
                :options="[
                  { label: 'Homologação', value: 'homologacao' },
                  { label: 'Produção', value: 'producao' },
                ]"
                type="radio"
                inline
              />
            </div>
            <div class="col-md-4 col-sm-6">
              <q-toggle v-model="form.incluir_ibscbs" label="Incluir IBS/CBS na DPS" />
            </div>
            <div class="col-md-4 col-sm-6">
              <q-toggle v-model="form.is_ativo" label="Cliente ativo" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <div class="row q-gutter-sm justify-end">
        <q-btn outline color="grey-7" label="Cancelar" :to="{ name: 'clientes' }" />
        <q-btn type="submit" unelevated color="primary"
          :label="modo === 'criar' ? 'Criar cliente' : 'Salvar alterações'"
          :loading="salvando" icon-right="fa-light fa-arrow-right" />
      </div>
    </q-form>

    <credencial-modal
      v-model="modalCredenciais"
      titulo="Credenciais geradas"
      :credenciais="credenciaisRecemCriadas"
      @close="apoisCriarCredencial"
    />
  </q-page>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useRouter, useRoute } from 'vue-router'
import { useClienteStore } from 'src/stores/cliente'
import { useNotify } from 'src/composables/useNotify'
import CredencialModal from 'src/components/CredencialModal.vue'

const router = useRouter()
const route = useRoute()
const store = useClienteStore()
const { salvando } = storeToRefs(store)
const { success, error } = useNotify()

const modo = computed(() => (route.params.id ? 'editar' : 'criar'))

const form = reactive({
  nome_empresa: '',
  cnpj: '',
  email: '',
  telefone: '',
  cep: '',
  uf: '',
  codigo_municipio_ibge: '',
  logradouro: '',
  numero: '',
  bairro: '',
  complemento: '',
  inscricao_municipal: '',
  razao_social_prestador: '',
  regime_especial_tributacao: 0,
  simples_nacional: 1,
  ambiente: 'homologacao',
  incluir_ibscbs: false,
  is_ativo: true,
})

const opcoesRegime = [
  { label: '0 — Nenhum (recomendado, permite vDedRed)', value: 0 },
  { label: '1 — Microempresa Municipal', value: 1 },
  { label: '2 — Estimativa', value: 2 },
  { label: '3 — Sociedade de Profissionais', value: 3 },
  { label: '4 — Notário ou Registrador (BLOQUEIA dedução)', value: 4 },
  { label: '5 — Cooperativa', value: 5 },
  { label: '6 — MEI Simples', value: 6 },
  { label: '7 — ME/EPP Simples', value: 7 },
]

const opcoesSimples = [
  { label: '1 — Não Optante', value: 1 },
  { label: '2 — Optante', value: 2 },
  { label: '3 — MEI', value: 3 },
]

const modalCredenciais = ref(false)
const credenciaisRecemCriadas = ref({})
const idCriado = ref(null)

const aplicarCep = (endereco) => {
  if (!endereco) return
  if (endereco.logradouro) form.logradouro = endereco.logradouro
  if (endereco.bairro) form.bairro = endereco.bairro
  if (endereco.localidade) form.cidade = endereco.localidade
  if (endereco.uf) form.uf = endereco.uf
  if (endereco.ibge) form.codigo_municipio_ibge = endereco.ibge
}

const salvar = async () => {
  try {
    if (modo.value === 'criar') {
      const resp = await store.criar({ ...form })
      idCriado.value = resp.cliente?.data?.id ?? resp.cliente?.id
      credenciaisRecemCriadas.value = {
        'X-Api-Key': resp.credenciais.api_key,
        'Client ID': resp.credenciais.client_id,
        'Client Secret': resp.credenciais.client_secret,
      }
      modalCredenciais.value = true
      success('Cliente criado com sucesso')
    } else {
      await store.atualizar(route.params.id, { ...form })
      success('Cliente atualizado')
      router.push({ name: 'clientes-detalhe', params: { id: route.params.id } })
    }
  } catch (e) {
    const msg = e?.response?.data?.message || 'Erro ao salvar cliente'
    error(msg)
    if (e?.response?.status === 422 && e.response.data.errors) {
      // Lista o primeiro erro de validação
      const primeiro = Object.values(e.response.data.errors)[0]
      if (primeiro?.[0]) error(primeiro[0])
    }
  }
}

const apoisCriarCredencial = () => {
  if (idCriado.value) router.push({ name: 'clientes-detalhe', params: { id: idCriado.value } })
  else router.push({ name: 'clientes' })
}

const carregarParaEdicao = async () => {
  if (!route.params.id) return
  const cliente = await store.buscar(route.params.id)
  Object.assign(form, {
    nome_empresa: cliente.nome_empresa,
    cnpj: cliente.cnpj,
    email: cliente.email,
    telefone: cliente.telefone || '',
    cep: cliente.cep,
    uf: cliente.uf,
    codigo_municipio_ibge: cliente.codigo_municipio_ibge,
    logradouro: cliente.logradouro,
    numero: cliente.numero,
    bairro: cliente.bairro,
    complemento: cliente.complemento || '',
    inscricao_municipal: cliente.inscricao_municipal,
    razao_social_prestador: cliente.razao_social_prestador,
    regime_especial_tributacao: cliente.regime_especial_tributacao,
    simples_nacional: cliente.simples_nacional,
    ambiente: cliente.ambiente,
    incluir_ibscbs: cliente.incluir_ibscbs,
    is_ativo: cliente.is_ativo,
  })
}

onMounted(() => carregarParaEdicao())
</script>
