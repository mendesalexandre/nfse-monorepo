<template>
  <q-page padding>
    <!-- Page Header -->
    <v-page-header titulo="Dashboard" subtitulo="Visão geral do sistema" :breadcrumbs="[
      { label: 'Início', icon: 'fa-light fa-house', to: { name: 'home' } },
      { label: 'Dashboard' },
    ]">
      <template #acoes>
        <q-btn outline color="grey-7" label="Exportar" icon="fa-light fa-download" size="sm" />
        <q-btn unelevated color="primary" label="Novo Registro" icon="fa-light fa-plus" size="sm" />
      </template>
    </v-page-header>

    <!-- Stat Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card label="Total Usuários" :valor="1248" icon="fa-light fa-users" cor="#4f46e5" :tendencia="12.5"
          tendencia-texto="vs mês anterior" />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card label="Receita" valor="84.320" prefixo="R$ " icon="fa-light fa-wallet" cor="#16a34a"
          :tendencia="8.2" tendencia-texto="vs mês anterior" />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card label="Pedidos" :valor="356" icon="fa-light fa-cart-shopping" cor="#d97706" :tendencia="-3.1"
          tendencia-texto="vs mês anterior" />
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <v-stat-card label="Taxa Conversão" valor="4.6" sufixo="%" icon="fa-light fa-chart-line" cor="#dc2626"
          :tendencia="0.8" tendencia-texto="vs mês anterior" />
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Coluna esquerda -->
      <div class="col-md-8 col-sm-12">
        <!-- Filtros + Tabela -->
        <v-filter label="Filtros" :total-ativos="filtrosAtivos" iniciar-aberto @limpar="limparFiltros">
          <div class="row q-col-gutter-md">
            <div class="col-md-4 col-sm-6 col-xs-12">
              <v-label label="Nome" />
              <q-input v-model="filtroNome" outlined dense placeholder="Buscar por nome..." />
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <v-label label="Status" />
              <v-select v-model="filtroStatus" :options="opcoesStatus" option-label="label" option-value="value" />
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
              <v-label label="Data" />
              <v-date v-model="filtroData" picker />
            </div>
          </div>
          <template #acoes>
            <q-btn outline color="grey-7" label="Limpar" size="sm" @click="limparFiltros" />
            <q-btn unelevated color="primary" label="Filtrar" size="sm" icon="fa-light fa-filter" />
          </template>
        </v-filter>

        <v-data-table :rows="tabelaRows" :columns="tabelaColumns" row-key="id" searchable
          search-placeholder="Buscar usuário..." />
      </div>

      <!-- Coluna direita -->
      <div class="col-md-4 col-sm-12">
        <!-- Avatares -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="text-subtitle2 text-weight-bold q-mb-md">Equipe Online</div>
            <div class="column q-gutter-sm">
              <div v-for="user in usuarios" :key="user.nome" class="row items-center no-wrap q-gutter-sm">
                <v-avatar :nome="user.nome" :src="user.avatar" :status="user.status" :tamanho="36" :cor="user.cor" />
                <div class="col">
                  <div class="text-body2 text-weight-medium">{{ user.nome }}</div>
                  <div class="text-caption text-grey">{{ user.cargo }}</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Checklist -->
        <v-checklist titulo="Onboarding" subtitulo="Complete as etapas para ativar sua conta." :items="checklistItems"
          closable clicavel class="q-mb-md" @click="toggleChecklist" />

        <!-- Copy -->
        <q-card flat bordered class="q-mb-md">
          <q-card-section>
            <div class="text-subtitle2 text-weight-bold q-mb-md">Dados para Copiar</div>
            <div class="column q-gutter-sm">
              <div class="row items-center">
                <span class="text-grey-7 col-4">CPF:</span>
                <v-copy valor="123.456.789-00" />
              </div>
              <div class="row items-center">
                <span class="text-grey-7 col-4">Chave PIX:</span>
                <v-copy valor="email@empresa.com.br" />
              </div>
              <div class="row items-center">
                <span class="text-grey-7 col-4">Protocolo:</span>
                <v-copy valor="2026-031845-ABC" />
              </div>
            </div>
          </q-card-section>
        </q-card>

        <!-- Timeline -->
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle2 text-weight-bold q-mb-md">Atividade Recente</div>
            <v-timeline :items="atividadesRecentes" />
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Painel lateral -->
    <q-btn unelevated color="primary" label="Abrir Painel" icon="fa-light fa-panel-ews"
      class="fixed-bottom-right q-ma-md" @click="painelAberto = true" />

    <v-painel v-model="painelAberto" titulo="Detalhes do Usuário" subtitulo="Alexandre Mendes · Admin"
      @close="painelAberto = false">
      <div class="column items-center q-mb-md">
        <v-avatar nome="Alexandre Mendes" :tamanho="64" status="online" cor="#4f46e5" />
        <div class="text-subtitle1 text-weight-bold q-mt-sm">Alexandre Mendes</div>
        <div class="text-caption text-grey">Administrador</div>
      </div>

      <div class="q-mb-md">
        <div class="row q-col-gutter-sm q-mb-xs">
          <div class="col-5 text-grey-7">E-mail</div>
          <div class="col-7"><v-copy valor="alexandre@email.com" /></div>
        </div>
        <div class="row q-col-gutter-sm q-mb-xs">
          <div class="col-5 text-grey-7">Telefone</div>
          <div class="col-7"><v-copy valor="(11) 99999-8888" /></div>
        </div>
        <div class="row q-col-gutter-sm q-mb-xs">
          <div class="col-5 text-grey-7">CPF</div>
          <div class="col-7"><v-copy valor="123.456.789-00" /></div>
        </div>
        <div class="row q-col-gutter-sm">
          <div class="col-5 text-grey-7">Status</div>
          <div class="col-7"><v-status-badge :valor="true" label-ativo="Ativo" label-inativo="Inativo" /></div>
        </div>
      </div>

      <div class="text-caption text-grey-8 text-weight-bold text-uppercase q-mb-sm">
        <q-icon name="fa-light fa-clock-rotate-left" class="q-mr-xs" /> Histórico
      </div>
      <v-timeline :items="atividadesPainel" />

      <template #rodape>
        <q-btn outline color="grey-7" label="Fechar" @click="painelAberto = false" />
        <q-btn unelevated color="primary" label="Editar" icon="fa-light fa-pen" />
      </template>
    </v-painel>
  </q-page>
</template>

<script setup>
import { ref, computed } from "vue";

const painelAberto = ref(false);
const filtroNome = ref("");
const filtroStatus = ref(null);
const filtroData = ref("");

const opcoesStatus = [
  { label: "Ativo", value: "Ativo" },
  { label: "Inativo", value: "Inativo" },
  { label: "Pendente", value: "Pendente" },
];

const filtrosAtivos = computed(() => {
  let count = 0;
  if (filtroNome.value) count++;
  if (filtroStatus.value) count++;
  if (filtroData.value) count++;
  return count;
});

const limparFiltros = () => {
  filtroNome.value = "";
  filtroStatus.value = null;
  filtroData.value = "";
};

const checklistItems = ref([
  { titulo: "Configurar Perfil", descricao: "Adicione seus dados básicos para personalizar sua experiência.", icon: "fa-light fa-user", concluido: true },
  { titulo: "Conectar Ferramentas", descricao: "Integre seus apps favoritos para automatizar processos.", icon: "fa-light fa-plug", concluido: false },
  { titulo: "Personalizar Preferências", descricao: "Ajuste notificações, tema e configurações de exibição.", icon: "fa-light fa-sliders", concluido: false },
  { titulo: "Convidar Equipe", descricao: "Traga sua equipe para colaborar e maximizar produtividade.", icon: "fa-light fa-users", concluido: false },
]);

const toggleChecklist = (item, index) => {
  checklistItems.value[index].concluido = !checklistItems.value[index].concluido;
};

const usuarios = [
  { nome: "Alexandre Mendes", cargo: "Admin", status: "online", cor: "#4f46e5", avatar: "" },
  { nome: "Maria Silva", cargo: "Financeiro", status: "online", cor: "#16a34a", avatar: "" },
  { nome: "João Santos", cargo: "Suporte", status: "ausente", cor: "#d97706", avatar: "" },
  { nome: "Ana Costa", cargo: "Comercial", status: "offline", cor: "#ec4899", avatar: "" },
];

const atividadesRecentes = [
  { titulo: "Novo usuário cadastrado", descricao: "Maria Silva criou a conta", data: "Há 5 min", icon: "fa-light fa-user-plus", cor: "#4f46e5", autor: "Maria Silva" },
  { titulo: "Pagamento confirmado", descricao: "Pedido #1234 pago via PIX", data: "Há 30 min", icon: "fa-light fa-check", cor: "#16a34a" },
  { titulo: "Relatório exportado", descricao: "Relatório financeiro de março", data: "Há 2h", icon: "fa-light fa-file-export", cor: "#d97706", autor: "João Santos" },
  { titulo: "Erro no servidor", descricao: "Timeout na API de pagamentos", data: "Há 4h", icon: "fa-light fa-circle-exclamation", cor: "#dc2626" },
];

const atividadesPainel = [
  { titulo: "Login realizado", data: "Hoje, 09:15", icon: "fa-light fa-right-to-bracket", cor: "#4f46e5" },
  { titulo: "Perfil atualizado", data: "Ontem, 16:30", icon: "fa-light fa-user-pen", cor: "#16a34a" },
  { titulo: "Senha alterada", data: "15/03/2026", icon: "fa-light fa-lock", cor: "#d97706" },
];

const tabelaColumns = [
  { name: "id", label: "ID", field: "id", align: "left", sortable: true },
  { name: "nome", label: "Nome", field: "nome", align: "left", sortable: true },
  { name: "email", label: "E-mail", field: "email", align: "left" },
  { name: "status", label: "Status", field: "status", align: "center" },
];

const tabelaRows = [
  { id: 1, nome: "Alexandre Mendes", email: "alexandre@email.com", status: "Ativo" },
  { id: 2, nome: "Maria Silva", email: "maria@email.com", status: "Ativo" },
  { id: 3, nome: "João Santos", email: "joao@email.com", status: "Inativo" },
  { id: 4, nome: "Ana Costa", email: "ana@email.com", status: "Pendente" },
  { id: 5, nome: "Pedro Lima", email: "pedro@email.com", status: "Ativo" },
];
</script>