<template>
  <q-layout view="lHh Lpr lFf">
    <q-header class="bg-white">
      <q-toolbar>
        <q-btn flat round dense aria-label="Menu" @click="toggleLeftDrawer" color="dark" icon="fa-light fa-bars" />

        <q-space />

        <!-- Dark mode toggle -->
        <q-btn flat round dense :icon="isDark ? 'fa-light fa-sun' : 'fa-light fa-moon'" color="grey-7" @click="toggle">
          <q-tooltip>{{ isDark ? 'Modo claro' : 'Modo escuro' }}</q-tooltip>
        </q-btn>

        <!-- Busca no header -->
        <q-btn flat round dense icon="fa-light fa-magnifying-glass" color="grey-7" @click="modalBusca = true">
          <q-tooltip>Buscar</q-tooltip>
        </q-btn>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above side="left" :width="60" :breakpoint="500">
      <q-list class="drawer-menu">
        <!-- Menu Principal -->
        <q-item clickable class="drawer-item" :to="{ name: 'home' }" exact>
          <q-item-section>
            <q-icon name="fa-light fa-gauge-high" size="16px" />
          </q-item-section>
          <q-tooltip anchor="center right" self="center left" :offset="[8, 0]">Dashboard</q-tooltip>
        </q-item>

        <q-item clickable class="drawer-item" :to="{ name: 'clientes' }">
          <q-item-section>
            <q-icon name="fa-light fa-building" size="16px" />
          </q-item-section>
          <q-tooltip anchor="center right" self="center left" :offset="[8, 0]">Clientes</q-tooltip>
        </q-item>

        <q-item clickable class="drawer-item" :to="{ name: 'emissoes' }">
          <q-item-section>
            <q-icon name="fa-light fa-file-invoice" size="16px" />
          </q-item-section>
          <q-tooltip anchor="center right" self="center left" :offset="[8, 0]">Emissões NFS-e</q-tooltip>
        </q-item>
      </q-list>

      <!-- Menu do usuário no rodapé -->
      <div class="absolute-bottom">
        <q-separator class="q-mx-sm" />
        <q-list class="drawer-menu">
          <q-item clickable class="drawer-item">
            <q-item-section>
              <q-icon name="fa-light fa-gear" size="16px" />
            </q-item-section>
            <q-tooltip anchor="center right" self="center left" :offset="[8, 0]">Configurações</q-tooltip>
          </q-item>

          <q-item clickable class="drawer-item">
            <q-item-section>
              <q-icon name="fa-light fa-user" size="16px" />
            </q-item-section>
            <q-tooltip anchor="center right" self="center left" :offset="[8, 0]">Perfil</q-tooltip>
            <q-menu anchor="top middle" self="bottom middle">
              <q-list style="min-width: 180px">
                <q-item-label header class="text-grey-8">
                  <q-icon name="fa-light fa-user" size="16px" class="q-mr-sm" />
                  {{ user?.nome || "Usuário" }}
                </q-item-label>
                <q-separator />
                <q-item clickable v-close-popup @click="verPerfil">
                  <q-item-section avatar>
                    <q-icon name="fa-light fa-gear" size="16px" color="grey-6" />
                  </q-item-section>
                  <q-item-section>Configurações</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="editarPerfil">
                  <q-item-section avatar>
                    <q-icon name="fa-light fa-user-pen" size="16px" color="grey-6" />
                  </q-item-section>
                  <q-item-section>Editar Perfil</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="alterarSenha">
                  <q-item-section avatar>
                    <q-icon name="fa-light fa-lock" size="16px" color="grey-6" />
                  </q-item-section>
                  <q-item-section>Alterar Senha</q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup @click="logout">
                  <q-item-section avatar>
                    <q-icon name="fa-light fa-arrow-right-from-bracket" size="16px" color="red-6" />
                  </q-item-section>
                  <q-item-section>Sair</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-item>

          <q-item clickable class="drawer-item drawer-item--danger" @click="logout">
            <q-item-section>
              <q-icon name="fa-light fa-arrow-right-from-bracket" size="16px" />
            </q-item-section>
            <q-tooltip anchor="center right" self="center left" :offset="[8, 0]">Sair</q-tooltip>
          </q-item>
        </q-list>
      </div>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>

  <!-- Modal de Busca -->
  <modal v-model="modalBusca" titulo="Buscar" tamanho="sm" @close="modalBusca = false">
    <q-input v-model="termoBusca" outlined dense placeholder="Digite para buscar..." autofocus>
      <template v-slot:prepend>
        <q-icon name="fa-light fa-magnifying-glass" />
      </template>
    </q-input>

    <div class="q-mt-md text-grey-5 text-center text-body2">
      <v-empty-state mensagem="Digite algo para buscar" icon="fa-light fa-magnifying-glass" />
    </div>

    <template #rodape>
      <q-btn flat label="Fechar" color="grey-7" @click="modalBusca = false" />
      <q-btn unelevated label="Buscar" color="primary" @click="modalBusca = false" />
    </template>
  </modal>

  <!-- Modal Exemplo -->
  <modal v-model="modalExemplo" titulo="Componentes" tamanho="lg" @close="modalExemplo = false">
    <div class="row q-col-gutter-md">
      <div class="col-md-12 col-sm-12">
        <v-label label="Nome" obrigatorio />
        <q-input v-model="inputNome" outlined dense />
      </div>

      <div class="col-md-6 col-sm-12">
        <v-label label="CPF" obrigatorio />
        <v-cpf v-model="inputCpf" />
      </div>
      <div class="col-md-6 col-sm-12">
        <v-label label="CNPJ" />
        <v-cnpj v-model="inputCnpj" />
      </div>

      <div class="col-md-6 col-sm-12">
        <v-label label="Telefone" />
        <v-telefone v-model="inputTelefone" />
      </div>
      <div class="col-md-6 col-sm-12">
        <v-label label="CEP" ajuda="Digite o CEP para buscar o endereço automaticamente" />
        <v-cep v-model="inputCep" />
      </div>

      <div class="col-md-6 col-sm-12">
        <v-label label="Senha" obrigatorio />
        <v-password v-model="inputSenha" />
      </div>
      <div class="col-md-6 col-sm-12">
        <v-label label="Data" />
        <v-date v-model="inputData" picker />
      </div>

      <div class="col-md-6 col-sm-12">
        <v-label label="Valor (R$)" />
        <v-money v-model="inputValor" />
      </div>
      <div class="col-md-6 col-sm-12">
        <v-label label="Porcentagem" />
        <v-money v-model="inputPorcentagem" prefix="" suffix="%" :precision="2" />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Código OTP" />
        <v-otp v-model="inputOtp" :length="6" />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Anexo" />
        <v-upload hint="Arraste ou clique para enviar (máx. 5MB)" accept="image/*,.pdf" :max-file-size="5242880" />
      </div>

      <div class="col-md-6 col-sm-12">
        <v-label label="Status" />
        <v-status-badge :valor="inputAtivo" label-ativo="Ativo" label-inativo="Inativo" />
      </div>

      <div class="col-md-6 col-sm-12">
        <div class="row items-center q-gutter-sm">
          <q-toggle v-model="inputAtivo" label="Ativo" />
          <q-checkbox v-model="inputTermos" label="Aceito os termos" />
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="QR Code" />
        <v-qrcode value="https://quasar.dev" :size="120" />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Tooltips" />
        <div class="row q-gutter-sm">
          <q-btn outline color="grey-7" label="Hover me">
            <q-tooltip>Tooltip padrão</q-tooltip>
          </q-btn>
          <q-btn outline color="grey-7" label="Top">
            <q-tooltip anchor="top middle" self="bottom middle">Tooltip no topo</q-tooltip>
          </q-btn>
          <q-btn outline color="grey-7" label="Bottom">
            <q-tooltip anchor="bottom middle" self="top middle">Tooltip embaixo</q-tooltip>
          </q-btn>
          <q-btn outline color="grey-7" label="Left">
            <q-tooltip anchor="center left" self="center right">Tooltip à esquerda</q-tooltip>
          </q-btn>
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Alerts" />
        <div class="column q-gutter-sm">
          <v-alert type="info" message="12 mensagens não lidas. Toque para ver." />
          <v-alert type="success" message="Compra confirmada com sucesso!" />
          <v-alert type="warning" message="Atenção: endereço de e-mail inválido!" />
          <v-alert type="error" message="Erro! Falha ao processar a solicitação." closable />
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Badges" />
        <div class="row q-gutter-sm items-center">
          <q-badge color="primary">Primary</q-badge>
          <q-badge color="positive">Ativo</q-badge>
          <q-badge color="negative">Bloqueado</q-badge>
          <q-badge color="warning">Pendente</q-badge>
          <q-badge color="info">Novo</q-badge>
          <q-badge color="grey">Rascunho</q-badge>
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Tabela" />
        <q-table :rows="tabelaRows" :columns="tabelaColumns" row-key="id" flat :rows-per-page-options="[5, 10, 20, 50, 0]" :pagination="{ rowsPerPage: 5 }" />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Breadcrumbs" />
        <q-breadcrumbs>
          <q-breadcrumbs-el label="Início" icon="fa-light fa-house" />
          <q-breadcrumbs-el label="Usuários" icon="fa-light fa-users" />
          <q-breadcrumbs-el label="Editar" />
        </q-breadcrumbs>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Confirmação" />
        <div class="row q-gutter-sm">
          <q-btn outline color="warning" label="Warning" @click="showConfirmWarning = true" />
          <q-btn outline color="negative" label="Deletar" @click="showConfirmDanger = true" />
          <q-btn outline color="primary" label="Info" @click="showConfirmInfo = true" />
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Accordion" />
        <div>
          <q-expansion-item label="O que é este projeto?" default-opened>
            <div class="q-expansion-item__content">
              Um template base Quasar com visual shadcn/Tailwind para iniciar novos projetos rapidamente.
            </div>
          </q-expansion-item>
          <q-expansion-item label="Quais componentes estão inclusos?">
            <div class="q-expansion-item__content">
              Mais de 20 componentes globais: inputs com máscara, modal, alert, date picker, money, confirm, upload, entre outros.
            </div>
          </q-expansion-item>
          <q-expansion-item label="Posso customizar as cores?">
            <div class="q-expansion-item__content">
              Sim, basta editar o arquivo <code>src/css/quasar.variables.scss</code> com sua paleta de cores.
            </div>
          </q-expansion-item>
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Paginação" />
        <q-pagination v-model="paginaAtual" :max="10" direction-links boundary-links />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Chips" />
        <div class="row q-gutter-sm items-center">
          <q-chip color="primary" icon="fa-light fa-tag">Primary</q-chip>
          <q-chip color="positive" icon="fa-light fa-check">Ativo</q-chip>
          <q-chip color="negative" icon="fa-light fa-xmark">Bloqueado</q-chip>
          <q-chip color="warning" icon="fa-light fa-clock">Pendente</q-chip>
          <q-chip color="info" icon="fa-light fa-circle-info">Novo</q-chip>
          <q-chip color="grey">Rascunho</q-chip>
          <q-chip color="primary" removable @remove="() => {}">Removível</q-chip>
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Timeline" />
        <q-timeline color="primary">
          <q-timeline-entry
            title="Pedido criado"
            subtitle="04/03/2026 — 10:30"
            icon="fa-light fa-file-lines"
          >
            Pedido #1234 foi criado com sucesso.
          </q-timeline-entry>
          <q-timeline-entry
            title="Pagamento confirmado"
            subtitle="04/03/2026 — 11:00"
            icon="fa-light fa-check"
            color="positive"
          >
            Pagamento via PIX confirmado.
          </q-timeline-entry>
          <q-timeline-entry
            title="Em separação"
            subtitle="04/03/2026 — 14:00"
            icon="fa-light fa-clock"
            color="warning"
          >
            Produtos sendo preparados para envio.
          </q-timeline-entry>
        </q-timeline>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Stepper" />
        <q-stepper v-model="stepAtual" flat animated>
          <q-step :name="1" title="Dados pessoais" icon="fa-light fa-user" :done="stepAtual > 1">
            Preencha seus dados pessoais.
          </q-step>
          <q-step :name="2" title="Endereço" icon="fa-light fa-location-dot" :done="stepAtual > 2">
            Informe o endereço de entrega.
          </q-step>
          <q-step :name="3" title="Confirmação" icon="fa-light fa-check">
            Revise e confirme seus dados.
          </q-step>

          <template #navigation>
            <q-stepper-navigation>
              <q-btn v-if="stepAtual > 1" flat label="Voltar" @click="stepAtual--" class="q-mr-sm" />
              <q-btn unelevated color="primary" :label="stepAtual === 3 ? 'Finalizar' : 'Próximo'" @click="stepAtual < 3 ? stepAtual++ : (stepAtual = 1)" />
            </q-stepper-navigation>
          </template>
        </q-stepper>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Data Table (v-data-table)" />
        <v-data-table
          :rows="tabelaRows"
          :columns="tabelaColumns"
          row-key="id"
          searchable
          search-placeholder="Buscar usuário..."
        />
      </div>

      <div class="col-md-6 col-sm-12">
        <v-label label="Estado" />
        <v-select-estado v-model="inputEstado" :options="listaEstados" />
      </div>
      <div class="col-md-6 col-sm-12">
        <v-label label="Cidade" />
        <v-select-cidade v-model="inputCidade" :uf="inputEstado" />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Progress" />
        <div class="column q-gutter-sm">
          <q-linear-progress :value="0.7" color="primary" />
          <q-linear-progress :value="0.4" color="positive" />
          <q-linear-progress :value="0.9" color="warning" />
        </div>
        <div class="row q-gutter-md q-mt-sm items-center">
          <q-circular-progress :value="75" size="60px" :thickness="0.2" color="primary" track-color="grey-3" show-value />
          <q-circular-progress :value="45" size="60px" :thickness="0.2" color="positive" track-color="grey-3" show-value />
        </div>
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Editor (TinyMCE)" />
        <v-editor v-model="inputEditor" />
      </div>

      <div class="col-md-12 col-sm-12">
        <v-label label="Skeleton" />
        <div class="column q-gutter-sm">
          <q-skeleton type="text" width="60%" />
          <q-skeleton type="text" width="40%" />
          <q-skeleton height="80px" />
        </div>
      </div>
    </div>

    <template #rodape>
      <q-btn flat label="Cancelar" color="grey-7" @click="modalExemplo = false" />
      <q-btn unelevated label="Salvar" color="primary" @click="modalExemplo = false" />
    </template>
  </modal>

  <!-- Confirms -->
  <v-confirm v-model="showConfirmWarning" type="warning" titulo="Atenção"
    mensagem="Deseja realmente prosseguir com esta ação?" @confirm="showConfirmWarning = false" />
  <v-confirm v-model="showConfirmDanger" type="danger" titulo="Excluir registro"
    mensagem="Esta ação não pode ser desfeita. Deseja continuar?" @confirm="showConfirmDanger = false" />
  <v-confirm v-model="showConfirmInfo" type="info" titulo="Informação"
    mensagem="Deseja enviar a notificação para todos os usuários?" @confirm="showConfirmInfo = false" />
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useDarkMode } from "src/composables/useDarkMode";
import { useAuthStore } from "src/stores/auth";

defineOptions({
  name: "MainLayout",
});

const $q = useQuasar();
const router = useRouter();
const auth = useAuthStore();
const { isDark, toggle, init: initDarkMode } = useDarkMode();
const user = auth.user;

const leftDrawerOpen = ref(false);
const modalBusca = ref(false);
const modalExemplo = ref(false);
const termoBusca = ref("");

const inputNome = ref("");
const inputCpf = ref("");
const inputCnpj = ref("");
const inputTelefone = ref("");
const inputCep = ref("");
const inputSenha = ref("");
const inputOtp = ref("");
const inputData = ref("");
const inputValor = ref(0);
const inputPorcentagem = ref(0);
const showConfirmWarning = ref(false);
const showConfirmDanger = ref(false);
const showConfirmInfo = ref(false);
const paginaAtual = ref(1);
const stepAtual = ref(1);
const inputEstado = ref("");
const inputCidade = ref("");
const listaEstados = [
  { id: "AC", nome: "Acre", sigla: "AC" }, { id: "AL", nome: "Alagoas", sigla: "AL" }, { id: "AP", nome: "Amapá", sigla: "AP" },
  { id: "AM", nome: "Amazonas", sigla: "AM" }, { id: "BA", nome: "Bahia", sigla: "BA" }, { id: "CE", nome: "Ceará", sigla: "CE" },
  { id: "DF", nome: "Distrito Federal", sigla: "DF" }, { id: "ES", nome: "Espírito Santo", sigla: "ES" }, { id: "GO", nome: "Goiás", sigla: "GO" },
  { id: "MA", nome: "Maranhão", sigla: "MA" }, { id: "MT", nome: "Mato Grosso", sigla: "MT" }, { id: "MS", nome: "Mato Grosso do Sul", sigla: "MS" },
  { id: "MG", nome: "Minas Gerais", sigla: "MG" }, { id: "PA", nome: "Pará", sigla: "PA" }, { id: "PB", nome: "Paraíba", sigla: "PB" },
  { id: "PR", nome: "Paraná", sigla: "PR" }, { id: "PE", nome: "Pernambuco", sigla: "PE" }, { id: "PI", nome: "Piauí", sigla: "PI" },
  { id: "RJ", nome: "Rio de Janeiro", sigla: "RJ" }, { id: "RN", nome: "Rio Grande do Norte", sigla: "RN" },
  { id: "RS", nome: "Rio Grande do Sul", sigla: "RS" }, { id: "RO", nome: "Rondônia", sigla: "RO" }, { id: "RR", nome: "Roraima", sigla: "RR" },
  { id: "SC", nome: "Santa Catarina", sigla: "SC" }, { id: "SP", nome: "São Paulo", sigla: "SP" }, { id: "SE", nome: "Sergipe", sigla: "SE" },
  { id: "TO", nome: "Tocantins", sigla: "TO" },
];
const inputEditor = ref("<p>Teste do editor TinyMCE</p>");
const inputAtivo = ref(false);
const inputTermos = ref(false);

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

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value;
};

const logout = async () => {
  await auth.logout();
  router.push({ name: "login" });
};

const verPerfil = () => {
  console.log("Ver perfil");
};

const editarPerfil = () => {
  console.log("Editar perfil");
};

const alterarSenha = () => {
  console.log("Alterar senha");
};

onMounted(() => {
  initDarkMode();
});
</script>

<style lang="scss" scoped>
.drawer-menu {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 8px 0;
}

.drawer-item {
  width: 40px;
  min-height: 40px;
  padding: 0;
  margin: 2px 0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: color 0.15s ease, background-color 0.15s ease;

  &:hover {
    color: #0f172a;
    background-color: rgba(0, 0, 0, 0.04);
  }

  &.q-router-link--active {
    color: var(--q-primary);
    background-color: rgba(79, 70, 229, 0.08);
  }

  :deep(.q-item__section) {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  :deep(.q-focus-helper),
  :deep(.q-ripple) {
    display: none;
  }

  &--danger {
    color: #dc2626;

    &:hover {
      color: #dc2626;
      background-color: rgba(220, 38, 38, 0.08);
    }
  }
}
</style>