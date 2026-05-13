<template>
  <q-page padding>
    <v-page-header
      titulo="Dashboard"
      subtitulo="Visão geral da operação NFS-e"
      :breadcrumbs="[
        { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
        { label: 'Dashboard' },
      ]"
    />

    <!-- Cards: Clientes -->
    <div class="text-overline text-grey-7 q-mb-sm">CLIENTES</div>
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Total de clientes"
          :valor="metricas.clientes.total"
          icon="fa-light fa-building"
          cor="#4f46e5"
        />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Ativos"
          :valor="metricas.clientes.ativos"
          icon="fa-light fa-circle-check"
          cor="#16a34a"
        />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Cert expira em 30d"
          :valor="metricas.clientes.cert_expirando_30d"
          icon="fa-light fa-clock"
          cor="#d97706"
        />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Cert vencido"
          :valor="metricas.clientes.cert_vencido"
          icon="fa-light fa-triangle-exclamation"
          cor="#dc2626"
        />
      </div>
    </div>

    <!-- Cards: Emissões -->
    <div class="text-overline text-grey-7 q-mb-sm">NFS-e</div>
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Total de emissões"
          :valor="metricas.emissoes.total"
          icon="fa-light fa-file-invoice"
          cor="#0ea5e9"
        />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Últimas 24h"
          :valor="metricas.emissoes.ultimas_24h"
          icon="fa-light fa-bolt"
          cor="#4f46e5"
        />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Emitidas"
          :valor="metricas.emissoes.emitidas"
          icon="fa-light fa-circle-check"
          cor="#16a34a"
        />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card
          label="Falhas (rejeitada/erro)"
          :valor="metricas.emissoes.rejeitadas + metricas.emissoes.erro"
          icon="fa-light fa-circle-xmark"
          cor="#dc2626"
        />
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <div class="col-md-6 col-sm-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle2 text-weight-bold q-mb-md">Distribuição de status</div>
            <div class="column q-gutter-sm">
              <div v-for="item in distribuicaoStatus" :key="item.status" class="row items-center">
                <status-nfse-badge :status="item.status" class="q-mr-sm" />
                <div class="col text-grey-8">{{ item.label }}</div>
                <div class="text-weight-bold">{{ item.valor }}</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-md-6 col-sm-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle2 text-weight-bold q-mb-md">Atividade recente</div>
            <div class="row items-center q-mb-sm">
              <q-icon name="fa-light fa-list-check" size="20px" color="primary" class="q-mr-sm" />
              <div class="col">
                <div class="text-body2">Total de eventos auditados</div>
                <div class="text-caption text-grey">Append-only LGPD</div>
              </div>
              <div class="text-h6 text-weight-bold">{{ metricas.audit.total }}</div>
            </div>
            <q-separator class="q-my-sm" />
            <div class="row items-center">
              <q-icon name="fa-light fa-clock-rotate-left" size="20px" color="info" class="q-mr-sm" />
              <div class="col">
                <div class="text-body2">Últimas 24h</div>
                <div class="text-caption text-grey">Emissão / cancelamento / consulta</div>
              </div>
              <div class="text-h6 text-weight-bold">{{ metricas.audit.ultimas_24h }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useDashboardStore } from 'src/stores/dashboard'
import StatusNfseBadge from 'src/components/StatusNfseBadge.vue'

const store = useDashboardStore()
const { metricas } = storeToRefs(store)

const distribuicaoStatus = computed(() => [
  { status: 'emitida', label: 'Emitidas', valor: metricas.value.emissoes.emitidas },
  { status: 'cancelada', label: 'Canceladas', valor: metricas.value.emissoes.canceladas },
  { status: 'rejeitada', label: 'Rejeitadas', valor: metricas.value.emissoes.rejeitadas },
  { status: 'erro', label: 'Erro de transporte', valor: metricas.value.emissoes.erro },
  { status: 'pendente', label: 'Pendentes', valor: metricas.value.emissoes.pendente },
])

onMounted(() => store.carregar())
</script>
