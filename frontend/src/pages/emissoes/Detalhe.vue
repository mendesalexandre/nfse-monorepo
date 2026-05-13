<template>
  <q-page padding>
    <v-page-header
      :titulo="atual?.numero_nfse ? `NFS-e ${atual.numero_nfse}` : 'Emissão'"
      :subtitulo="atual?.chave_acesso ? `Chave ${atual.chave_acesso}` : ''"
      :breadcrumbs="[
        { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
        { label: 'Emissões', to: { name: 'emissoes' } },
        { label: atual?.numero_nfse || '...' },
      ]"
    >
      <template #acoes>
        <q-btn outline color="grey-7" label="Voltar" icon="fa-light fa-arrow-left" size="sm"
          :to="{ name: 'emissoes' }" />
        <q-btn v-if="atual?.chave_acesso" unelevated color="primary" label="Baixar DANFSe"
          icon="fa-light fa-file-pdf" size="sm" @click="baixarPdf" />
      </template>
    </v-page-header>

    <div v-if="!atual" class="row justify-center q-pa-xl">
      <q-spinner-dots color="primary" size="40px" />
    </div>

    <div v-else>
      <!-- Cabeçalho -->
      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-md-3 col-sm-6">
          <q-card flat bordered>
            <q-card-section class="q-pa-md">
              <div class="text-caption text-grey-7">Status</div>
              <status-nfse-badge :status="atual.status" class="q-mt-xs" />
            </q-card-section>
          </q-card>
        </div>
        <div class="col-md-3 col-sm-6">
          <q-card flat bordered>
            <q-card-section class="q-pa-md">
              <div class="text-caption text-grey-7">SEFIN cStat</div>
              <div class="text-h6 text-weight-bold">{{ atual.c_stat || '—' }}</div>
              <div class="text-caption text-grey">{{ atual.x_motivo || '' }}</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-md-3 col-sm-6">
          <q-card flat bordered>
            <q-card-section class="q-pa-md">
              <div class="text-caption text-grey-7">Valor de serviços</div>
              <div class="text-h6 text-weight-bold">R$ {{ formatarMoeda(atual.valor_servicos) }}</div>
              <div class="text-caption text-grey">ISS: R$ {{ formatarMoeda(atual.valor_iss) }}</div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-md-3 col-sm-6">
          <q-card flat bordered>
            <q-card-section class="q-pa-md">
              <div class="text-caption text-grey-7">Emissão</div>
              <div class="text-body1 text-weight-bold">{{ formatarData(atual.data_emissao) }}</div>
              <div class="text-caption text-grey">DPS {{ atual.serie_dps }}/{{ atual.numero_dps }}</div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Tomador -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-subtitle2 text-weight-bold q-mb-sm">Tomador</div>
          <div class="row q-col-gutter-md">
            <div class="col-md-6">
              <div class="text-caption text-grey-7">Documento</div>
              <div class="text-body1 text-mono">{{ atual.tomador_documento || '—' }}</div>
            </div>
            <div class="col-md-6">
              <div class="text-caption text-grey-7">Nome</div>
              <div class="text-body1">{{ atual.tomador_nome || '—' }}</div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Discriminação -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section>
          <div class="text-subtitle2 text-weight-bold q-mb-sm">Discriminação do serviço</div>
          <pre class="discriminacao">{{ atual.discriminacao || '—' }}</pre>
        </q-card-section>
      </q-card>

      <!-- Payload do request (collapsible) -->
      <q-card flat bordered class="q-mb-md">
        <q-expansion-item
          icon="fa-light fa-code"
          label="Payload do request"
          :caption="`POST /api/v1/nfse — ${formatarData(atual.data_criacao)}`"
        >
          <q-card-section>
            <pre class="json-block">{{ JSON.stringify(atual.request_payload, null, 2) }}</pre>
          </q-card-section>
        </q-expansion-item>
      </q-card>

      <!-- Response XML (collapsible) -->
      <q-card flat bordered class="q-mb-md">
        <q-expansion-item
          icon="fa-light fa-file-code"
          label="Response do SEFIN (XML)"
          :caption="`Processamento: ${formatarData(atual.data_processamento)}`"
        >
          <q-card-section>
            <pre class="xml-block">{{ atual.response_xml || '—' }}</pre>
          </q-card-section>
        </q-expansion-item>
      </q-card>
    </div>
  </q-page>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useRoute } from 'vue-router'
import { useEmissaoStore } from 'src/stores/emissao'
import { useNotify } from 'src/composables/useNotify'
import StatusNfseBadge from 'src/components/StatusNfseBadge.vue'

const route = useRoute()
const store = useEmissaoStore()
const { atual } = storeToRefs(store)
const { error } = useNotify()

const id = computed(() => route.params.id)

const formatarData = (iso) => {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

const formatarMoeda = (valor) => Number(valor || 0).toLocaleString('pt-BR', {
  minimumFractionDigits: 2, maximumFractionDigits: 2,
})

const baixarPdf = async () => {
  try {
    await store.baixarPdf(id.value)
  } catch (e) {
    error('Erro ao baixar PDF: ' + (e?.response?.data?.message || e.message))
  }
}

onMounted(async () => {
  try {
    await store.buscar(id.value)
  } catch (e) {
    error('Erro ao carregar NFS-e: ' + (e?.response?.data?.message || e.message))
  }
})
</script>

<style scoped>
.text-mono { font-family: 'Menlo', 'Monaco', monospace; }
.discriminacao {
  background: #f8fafc;
  padding: 12px;
  border-radius: 6px;
  white-space: pre-wrap;
  font-family: inherit;
  font-size: 0.875rem;
  margin: 0;
}
.json-block, .xml-block {
  background: #0f172a;
  color: #e2e8f0;
  padding: 16px;
  border-radius: 6px;
  font-family: 'Menlo', 'Monaco', monospace;
  font-size: 0.8125rem;
  overflow: auto;
  max-height: 500px;
  margin: 0;
}
</style>
