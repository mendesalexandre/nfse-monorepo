<template>
  <div class="q-pa-md">
    <!-- Formulário Compacto -->
    <q-card bordered class="q-mb-md rounded-borders">
      <q-card-section class="q-pa-none">
        <div class="ds-header">
          <q-icon name="eva-bookmark-outline" class="q-mr-sm" />
          <span class="ds-header-title">Calcular Emolumento</span>
        </div>
      </q-card-section>

      <q-card-section>
        <div class="row q-col-gutter-xs">
          <div class="col-md-6 col-sm-12 col-xs-12">
            <label class="form-label text-weight-medium">Serviço</label>
            <q-select
              :options="tabelaCustas"
              option-label="nome"
              v-model="tabelaCusta.servico_selecionado"
              option-value="id"
              map-options
              emit-value
              outlined
              dense
              class="ds-input"
              placeholder="Selecione um serviço"
            />
          </div>

          <div class="col-md-2 col-sm-12 col-xs-12">
            <label class="form-label text-weight-medium">Base de Cálculo</label>
            <money
              v-model.number="tabelaCusta.base_calculo"
              outlined
              dense
              :disable="desativarValorBaseCalculo"
              placeholder="R$ 0,00"
            />
          </div>

          <div class="col-md-1 col-sm-12 col-xs-12">
            <label class="form-label text-weight-medium">Qtde.</label>
            <q-input
              v-model="tabelaCusta.quantidade"
              outlined
              dense
              min="1"
              class="ds-input"
              input-mode="numeric"
              placeholder="1"
            />
          </div>

          <div class="col-md-2 col-sm-12 col-xs-12">
            <label class="form-label text-weight-medium">Nº de Matrícula</label>
            <q-input
              v-model="tabelaCusta.quantidade"
              outlined
              dense
              min="1"
              class="ds-input"
              input-mode="numeric"
              placeholder="1"
            />
          </div>
          <div class="col-md-1 col-sm-12 col-xs-12">
            <v-label :label="'&nbsp;'" />

            <q-btn
              :icon="
                adicionarAtoLoading
                  ? 'eva-loader-outline'
                  : 'eva-arrow-circle-down-outline'
              "
              color="primary"
              outline
              @click="calcularEmolumento()"
              :disable="!tabelaCusta.servico_selecionado"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabela de Atos -->
    <q-card bordered class="rounded-borders" v-if="atos.length">
      <q-card-section class="q-pa-none">
        <div class="ds-header">
          <q-icon name="eva-list-outline" class="q-mr-sm" />
          <span class="ds-header-title">Atos Cadastrados</span>
          <q-space />
          <q-chip
            :label="atos.length"
            color="blue-grey-3"
            text-color="blue-grey-8"
            size="sm"
            class="rounded-borders"
          />
          <q-btn
            label="Enviar Todos"
            color="green-6"
            icon="eva-paper-plane-outline"
            size="sm"
            outline
            class="rounded-borders"
            @click="mostrarDialogEnviarTodos"
            v-if="atosNaoEnviados.length > 0"
          />
        </div>
      </q-card-section>

      <q-card-section class="q-pa-none">
        <q-table
          bordered
          :rows="atos"
          :columns="colunas"
          class="ds-compact-table"
          hide-bottom
          :rows-per-page-options="[0]"
          dense
        >
          <template #header-cell="props">
            <q-th :props="props" class="ds-table-header">
              {{ props.col.label }}
            </q-th>
          </template>

          <template #body="props">
            <q-tr :props="props" class="ds-table-row">
              <q-td key="is_pago" :props="props" class="text-center">
                <q-icon
                  size="12px"
                  name="eva-radio-button-on-outline"
                  :color="props.row.is_pago ? 'green-6' : 'red-6'"
                >
                  <q-tooltip>
                    {{
                      props.row.is_pago
                        ? "Emolumento já pago"
                        : "Emolumento não pago"
                    }}
                  </q-tooltip>
                </q-icon>
              </q-td>

              <q-td key="nome" :props="props" class="text-left">
                <div class="text-body2 text-weight-medium">
                  {{ props.row.nome }}
                </div>
              </q-td>

              <q-td key="valor_base_calculo" :props="props" class="text-right">
                <div class="text-body2">
                  {{ formatarDinheiroBrasil(props.row.valor_base_calculo) }}
                </div>
              </q-td>

              <q-td key="quantidade" :props="props" class="text-center">
                <q-chip
                  :label="props.row?.quantidade"
                  color="blue-grey-2"
                  text-color="blue-grey-8"
                  size="sm"
                  class="rounded-borders"
                />
              </q-td>

              <q-td key="valor_emolumento" :props="props" class="text-right">
                <div class="text-body2">
                  {{ formatarDinheiroBrasil(props.row?.valor_emolumento) }}
                </div>
              </q-td>

              <q-td key="valor_iss" :props="props" class="text-right">
                <div class="text-body2">
                  {{ formatarDinheiroBrasil(props.row?.valor_iss) }}
                </div>
              </q-td>

              <q-td key="valor_total" :props="props" class="text-right">
                <div class="text-weight-bold text-green-7">
                  {{ formatarDinheiroBrasil(props.row?.valor_total) }}
                </div>
              </q-td>

              <q-td key="acao" :props="props" class="text-center">
                <div class="row q-gutter-xs justify-center">
                  <q-btn
                    icon="eva-edit-outline"
                    flat
                    round
                    size="sm"
                    color="grey-7"
                    @click="editar(props.row)"
                  >
                    <q-tooltip>Editar</q-tooltip>
                  </q-btn>

                  <q-btn
                    v-if="!props.row.lancamento_id"
                    icon="eva-paper-plane-outline"
                    flat
                    round
                    size="sm"
                    color="grey-7"
                    @click="enviarParaFinanceiro(props.row)"
                  >
                    <q-tooltip>Enviar ao Financeiro</q-tooltip>
                  </q-btn>

                  <q-icon
                    v-else
                    name="eva-checkmark-circle-outline"
                    color="green-6"
                    size="sm"
                  >
                    <q-tooltip>Já enviado ao financeiro</q-tooltip>
                  </q-icon>

                  <q-btn
                    icon="eva-trash-2-outline"
                    flat
                    round
                    size="sm"
                    color="red-8"
                    @click="excluir(props.row)"
                  >
                    <q-tooltip>Excluir</q-tooltip>
                  </q-btn>
                </div>
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- Estado vazio -->
    <q-card bordered class="rounded-borders" v-else>
      <q-card-section class="text-center q-py-xl">
        <q-icon
          name="eva-calculator-outline"
          size="4em"
          color="blue-grey-4"
          class="q-mb-md"
        />
        <div class="text-h6 text-grey-8 q-mb-sm">Nenhum ato cadastrado</div>
        <div class="text-caption text-grey-6">
          Selecione um serviço e calcule o emolumento para começar
        </div>
      </q-card-section>
    </q-card>

    <!-- Dialog de Exclusão -->
    <CustomDialog
      v-model="showDeleteDialog"
      type="danger"
      title="Confirmar Exclusão"
      content-title="Excluir Ato"
      :message="`Tem certeza que deseja excluir o ato <strong>&quot;${atoParaExcluir?.nome}&quot;</strong>?`"
      warning="Esta ação não pode ser desfeita."
      footer-info="Última chance para cancelar"
      icon="eva-alert-triangle-outline"
      main-icon="eva-trash-outline"
      confirm-label="Excluir Definitivamente"
      confirm-color="red-6"
      confirm-icon="eva-trash-2-outline"
      cancel-label="Manter Ato"
      :loading="excludingAto"
      loading-text="Excluindo..."
      @confirm="confirmarExclusao"
      @cancel="cancelarExclusao"
    />

    <!-- Dialog de Envio Individual -->
    <CustomDialog
      v-model="showFinanceDialog"
      type="info"
      title="Enviar ao Financeiro"
      content-title="Confirmar Envio"
      :message="`Enviar o ato <strong>&quot;${atoParaEnviar?.nome}&quot;</strong> para o sistema financeiro?`"
      icon="eva-paper-plane-outline"
      main-icon="eva-credit-card-outline"
      confirm-label="Enviar para Financeiro"
      confirm-color="blue-6"
      confirm-icon="eva-paper-plane-outline"
      :loading="sendingToFinance"
      loading-text="Enviando..."
      @confirm="confirmarEnvioFinanceiro"
    />

    <!-- Dialog de Envio em Massa -->
    <CustomDialog
      v-model="showMassFinanceDialog"
      type="warning"
      title="Enviar Todos ao Financeiro"
      content-title="Envio em Massa"
      :message="`Enviar <strong>${atosNaoEnviados.length} ato(s)</strong> para o sistema financeiro?`"
      :footer-info="`Total: ${formatarDinheiroBrasil(totalAtosNaoEnviados)}`"
      icon="eva-paper-plane-outline"
      main-icon="eva-archive-outline"
      confirm-label="Enviar Todos"
      confirm-color="green-6"
      confirm-icon="eva-paper-plane-outline"
      :loading="sendingAllToFinance"
      loading-text="Enviando atos..."
      @confirm="confirmarEnvioTodos"
    />

    <!-- Dialog de Sucesso -->
    <CustomDialog
      v-model="showSuccessDialog"
      type="success"
      title="Operação Realizada"
      :content-title="successTitle"
      :message="successMessage"
      icon="eva-checkmark-circle-outline"
      main-icon="eva-checkmark-circle-outline"
      confirm-label="Entendi"
      confirm-color="green-6"
      confirm-icon="eva-checkmark-outline"
      :show-cancel-button="false"
      :show-close-button="false"
      @confirm="showSuccessDialog = false"
    />
  </div>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { useTabelaCustaStore } from "src/stores/tabela-custa";
import { useProtocoloStore } from "src/stores/protocolo";
import { useServicoStore } from "src/stores/servico";
import { onMounted, ref, computed } from "vue";
import { formatarDinheiroBrasil } from "src/Utils";
import { useQuasar } from "quasar";
import { api } from "src/boot/axios";
import CustomDialog from "src/components/Modal/CustomDialog.vue";

const $q = useQuasar();
const protocoloStore = useProtocoloStore();
const { protocolo, atos } = storeToRefs(protocoloStore);

const servicoStore = useServicoStore();
const { servico } = storeToRefs(servicoStore);

const tabelaCustaAto = useTabelaCustaStore();
const { tabelaCusta, tabelaCustas } = storeToRefs(tabelaCustaAto);

const desativarValorBaseCalculo = ref(false);

// Estados dos dialogs
const showDeleteDialog = ref(false);
const showFinanceDialog = ref(false);
const showMassFinanceDialog = ref(false);
const showSuccessDialog = ref(false);

// Estados de loading
const adicionarAtoLoading = ref(false);
const excludingAto = ref(false);
const sendingToFinance = ref(false);
const sendingAllToFinance = ref(false);

// Dados dos dialogs
const atoParaExcluir = ref(null);
const atoParaEnviar = ref(null);
const successTitle = ref("");
const successMessage = ref("");

// Computed para atos não enviados
const atosNaoEnviados = computed(() => {
  return atos.value.filter((a) => !a.lancamento_id);
});

// Computed para total dos atos não enviados
const totalAtosNaoEnviados = computed(() => {
  return atosNaoEnviados.value.reduce(
    (total, ato) => total + (ato.valor_total || 0),
    0
  );
});

const calcularEmolumento = async () => {
  if (!tabelaCusta.value.servico_selecionado) {
    $q.notify({
      color: "warning",
      message: "Selecione um serviço",
      icon: "eva-alert-triangle-outline",
      position: "top-right",
    });
    return;
  }

  try {
    const response = await tabelaCustaAto.calcularEmolumento({
      servico_id: tabelaCusta.value.servico_selecionado,
      base_calculo: tabelaCusta.value.base_calculo,
      quantidade: tabelaCusta.value.quantidade,
      protocolo_id: protocolo.value.id,
    });

    protocolo?.value?.atos.push(response?.data);
    await protocoloStore.show(protocolo.value.uuid);

    // Limpar formulário
    tabelaCusta.value.servico_selecionado = null;
    tabelaCusta.value.base_calculo = 0;
    tabelaCusta.value.quantidade = 1;

    $q.notify({
      color: "positive",
      message: "Ato adicionado com sucesso",
      icon: "eva-checkmark-circle-outline",
      position: "top-right",
    });
  } catch (e) {
    console.log(e);
    $q.notify({
      color: "negative",
      message: "Erro ao calcular emolumento",
      icon: "eva-alert-circle-outline",
      position: "top-right",
    });
  }
};

const editar = (ato) => {
  $q.notify({
    color: "info",
    message: "Função de edição em desenvolvimento",
    icon: "eva-edit-outline",
    position: "top-right",
  });
};

// Funções de exclusão
const excluir = (ato) => {
  atoParaExcluir.value = ato;
  showDeleteDialog.value = true;
};

const confirmarExclusao = async () => {
  excludingAto.value = true;

  try {
    // Simula API call por 1 segundo
    await new Promise((resolve) => setTimeout(resolve, 1000));

    // Sua lógica de exclusão aqui
    // await api.delete(`/api/atos/${atoParaExcluir.value.id}`);

    successTitle.value = "Ato Excluído";
    successMessage.value = `O ato <strong>"${atoParaExcluir.value.nome}"</strong> foi removido com sucesso.`;
    showSuccessDialog.value = true;

    showDeleteDialog.value = false;

    // Recarregar dados se necessário
    // await protocoloStore.show(protocolo.value.uuid);
  } catch (error) {
    $q.notify({
      color: "negative",
      message: "Erro ao excluir ato",
      icon: "eva-alert-circle-outline",
      position: "top-right",
    });
  } finally {
    excludingAto.value = false;
  }
};

const cancelarExclusao = () => {
  atoParaExcluir.value = null;
};

// Funções de envio ao financeiro
const enviarParaFinanceiro = (ato) => {
  atoParaEnviar.value = ato;
  showFinanceDialog.value = true;
};

const confirmarEnvioFinanceiro = async () => {
  sendingToFinance.value = true;

  try {
    const payload = {
      descricao: `Emolumento: ${atoParaEnviar.value.nome}`,
      valor: atoParaEnviar.value.valor_total,
      tipo: "receita",
      data_vencimento: new Date(),
      cliente_id: protocolo.value?.solicitante_id,
      protocolo_id: protocolo.value.id,
      ato_id: atoParaEnviar.value.id,
    };

    const { data } = await api.post("/api/contas-receber", payload);

    successTitle.value = "Enviado para Financeiro";
    successMessage.value = `O ato <strong>"${atoParaEnviar.value.nome}"</strong> foi enviado para o sistema financeiro.`;
    showSuccessDialog.value = true;

    showFinanceDialog.value = false;
    await protocoloStore.show(protocolo.value.uuid);
  } catch (error) {
    console.error(error);
    $q.notify({
      color: "negative",
      message: "Erro ao enviar para o financeiro.",
      icon: "eva-alert-circle-outline",
      position: "top-right",
    });
  } finally {
    sendingToFinance.value = false;
  }
};

// Funções de envio em massa
const mostrarDialogEnviarTodos = () => {
  showMassFinanceDialog.value = true;
};

const confirmarEnvioTodos = async () => {
  sendingAllToFinance.value = true;

  try {
    for (const ato of atosNaoEnviados.value) {
      const payload = {
        descricao: `Emolumento: ${ato.nome}`,
        valor: ato.valor_total,
        tipo: "receita",
        data_vencimento: new Date(),
        cliente_id: protocolo.value?.solicitante_id,
        protocolo_id: protocolo.value.id,
        ato_id: ato.id,
      };
      await api.post("/api/contas-receber", payload);
    }

    successTitle.value = "Atos Enviados em Massa";
    successMessage.value = `<strong>${atosNaoEnviados.value.length} ato(s)</strong> foram enviados para o sistema financeiro com sucesso!`;
    showSuccessDialog.value = true;

    showMassFinanceDialog.value = false;
    await protocoloStore.show(protocolo.value.uuid);
  } catch (error) {
    console.error(error);
    $q.notify({
      color: "negative",
      message: "Erro ao enviar atos em massa.",
      icon: "eva-alert-circle-outline",
      position: "top-right",
    });
  } finally {
    sendingAllToFinance.value = false;
  }
};

const colunas = ref([
  { name: "is_pago", label: "", align: "center", style: "width: 40px" },
  { name: "nome", label: "Serviço", align: "left", field: (row) => row.nome },
  {
    name: "valor_base_calculo",
    label: "Base de Cálculo",
    align: "right",
    field: (row) => formatarDinheiroBrasil(row.valor_base_calculo),
    style: "width: 120px",
  },
  {
    name: "quantidade",
    label: "Qtd",
    align: "center",
    field: (row) => row.quantidade,
    style: "width: 80px",
  },
  {
    name: "valor_emolumento",
    label: "Emolumento",
    align: "right",
    field: (row) => formatarDinheiroBrasil(row.valor_emolumento),
    style: "width: 120px",
  },
  {
    name: "valor_iss",
    label: "ISS",
    align: "right",
    field: (row) => formatarDinheiroBrasil(row.valor_iss),
    style: "width: 100px",
  },
  {
    name: "valor_total",
    label: "Total",
    align: "right",
    field: (row) => formatarDinheiroBrasil(row.valor_total),
    style: "width: 120px",
  },
  { name: "acao", label: "Ações", align: "center", style: "width: 150px" },
]);

onMounted(async () => {
  try {
    await tabelaCustaAto.index();
  } catch (error) {
    console.error("Erro ao carregar tabela de custas:", error);
    $q.notify({
      color: "negative",
      message: "Erro ao carregar tabela de custas.",
      icon: "eva-alert-circle-outline",
      position: "top-right",
    });
  }
});
</script>
