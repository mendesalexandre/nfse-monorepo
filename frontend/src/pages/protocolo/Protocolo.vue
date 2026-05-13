<template>
  <q-page>
    <!-- Header compacto -->
    <q-header class="text-dark bg-white shadow-sm compact-header" elevated>
      <q-toolbar class="q-py-xs">
        <div class="row items-center full-width q-gutter-sm">
          <!-- Info do protocolo compacta -->
          <div class="protocol-badge-compact">
            <q-chip :label="titulo" color="blue-grey-2" text-color="blue-grey-8" size="sm"
              class="rounded-borders text-weight-medium" />
          </div>

          <!-- TABS COMPACTAS -->
          <q-tabs no-caps v-model="tab" align="left" class="compact-tabs" active-color="primary"
            indicator-color="primary" dense>
            <q-route-tab name="geral" class="compact-tab" :to="{
              name: 'protocolo.geral',
              params: { id: $route.params.id },
            }">
              <div class="tab-content-compact">
                <q-icon name="eva-info-outline" size="16px" />
                <span class="tab-label-compact">Geral</span>
              </div>
            </q-route-tab>

            <q-route-tab name="ato_registro" class="compact-tab"
              :to="{ name: 'protocolo.atos', params: { id: $route.params.id } }">
              <div class="tab-content-compact">
                <q-icon name="eva-calculator-outline" size="16px" />
                <span class="tab-label-compact">Atos</span>
                <q-chip v-if="totalAtos > 0" :label="totalAtos" color="orange-3" text-color="orange-8" size="xs"
                  class="rounded-borders tab-badge-compact" />
              </div>
            </q-route-tab>

            <q-route-tab name="financeiro" class="compact-tab" :to="{
              name: 'protocolo.financeiro',
              params: { id: $route.params.id },
            }">
              <div class="tab-content-compact">
                <q-icon name="eva-credit-card-outline" size="16px" />
                <span class="tab-label-compact">Financeiro</span>
                <q-chip v-if="valorRestante > 0" label="!" color="red-3" text-color="red-8" size="xs"
                  class="rounded-borders tab-badge-compact" />
              </div>
            </q-route-tab>
          </q-tabs>

          <q-space />

          <!-- Ações principais compactas -->
          <div class="row q-gutter-xs">
            <q-btn v-if="valorRestante > 0" color="green-6" size="sm" icon="eva-credit-card-outline"
              @click="receberPagamento" class="rounded-borders" dense>
              <q-tooltip>Receber Pagamento</q-tooltip>
            </q-btn>

            <q-btn color="blue-6" size="sm" icon="eva-printer-outline" outline @click="imprimirProtocolo"
              class="rounded-borders" dense>
              <q-tooltip>Imprimir</q-tooltip>
            </q-btn>

            <q-btn icon="eva-more-vertical-outline" flat round size="sm" color="grey-7">
              <q-menu class="rounded-borders">
                <q-list dense>
                  <q-item clickable @click="duplicarProtocolo" class="q-py-xs">
                    <q-item-section avatar>
                      <q-icon name="eva-copy-outline" size="16px" />
                    </q-item-section>
                    <q-item-section>
                      <div class="text-caption">Duplicar</div>
                    </q-item-section>
                  </q-item>

                  <q-item clickable @click="arquivarProtocolo" class="q-py-xs">
                    <q-item-section avatar>
                      <q-icon name="eva-archive-outline" size="16px" />
                    </q-item-section>
                    <q-item-section>
                      <div class="text-caption">Arquivar</div>
                    </q-item-section>
                  </q-item>

                  <q-separator />

                  <q-item clickable @click="excluirProtocolo" class="text-negative q-py-xs">
                    <q-item-section avatar>
                      <q-icon name="eva-trash-2-outline" size="16px" />
                    </q-item-section>
                    <q-item-section>
                      <div class="text-caption">Excluir</div>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </div>
        </div>
      </q-toolbar>
    </q-header>

    <!-- Conteúdo principal -->
    <div class="protocol-content-compact">
      <router-view />
    </div>

    <!-- Footer financeiro compacto -->
    <q-footer class="bg-white text-dark compact-footer" elevated>
      <q-card-section class="q-py-sm">
        <div class="row items-center justify-between">
          <!-- Resumo financeiro inline -->
          <div class="col">
            <div class="financial-summary-compact">
              <div class="row items-center q-gutter-md">
                <div class="summary-item-compact">
                  <span class="text-caption text-grey-6">Total:</span>
                  <span class="text-weight-bold text-grey-8 q-ml-xs">
                    {{ formatarDinheiroBrasil(totalEmolumentoGeral) }}
                  </span>
                </div>

                <div v-if="valorDesconto > 0" class="summary-item-compact">
                  <span class="text-caption text-grey-6">Desconto:</span>
                  <span class="text-weight-medium text-red-6 q-ml-xs">
                    -{{ formatarDinheiroBrasil(valorDesconto) }}
                  </span>
                </div>

                <div v-if="valorPago > 0" class="summary-item-compact">
                  <span class="text-caption text-grey-6">Pago:</span>
                  <span class="text-weight-medium text-green-6 q-ml-xs">
                    {{ formatarDinheiroBrasil(valorPago) }}
                  </span>
                </div>

                <div class="summary-item-compact">
                  <span class="text-caption text-grey-6">Restante:</span>
                  <span class="text-weight-bold text-primary q-ml-xs">
                    {{
                      formatarDinheiroBrasil(
                        totalEmolumentoGeral - valorDesconto - valorPago
                      )
                    }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Status visual -->
          <div class="col-auto">
            <q-chip v-if="valorRestante > 0" label="Pendente" color="orange-3" text-color="orange-8" size="sm"
              class="rounded-borders" />
            <q-chip v-else label="Quitado" color="green-3" text-color="green-8" size="sm" class="rounded-borders" />
          </div>
        </div>
      </q-card-section>
    </q-footer>
  </q-page>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { useProtocoloStore } from "src/stores/protocolo";
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const tab = ref("geral");

defineOptions({
  name: "ProtocoloIndex",
});

import { useQuasar } from "quasar";
import { formatarDinheiroBrasil } from "src/Utils";

const protocoloStore = useProtocoloStore();
const { protocolo, protocoloSelecionado, totalEmolumentoGeral } =
  storeToRefs(protocoloStore);
const $route = useRoute();
const $router = useRouter();
const $q = useQuasar();

const valorPago = ref(10.0);
const desconto = ref(100.0);
const valorDesconto = ref(0);

const valorRestante = computed(() => {
  return totalEmolumentoGeral.value - valorPago.value - desconto.value;
});

// Computed para as badges das tabs
const totalAtos = computed(() => {
  return protocolo.value?.atos?.length || 0;
});

const titulo = computed(() =>
  protocolo.value?.id
    ? `#${protocolo.value.numero_protocolo_formatado}`
    : "Novo Protocolo"
);

// Métodos de ação
const receberPagamento = () => {
  $q.notify({
    color: "positive",
    message: "Abrindo tela de pagamento",
    icon: "eva-credit-card-outline",
    position: "top-right",
  });
};

const imprimirProtocolo = () => {
  window.print();
};

const duplicarProtocolo = () => {
  $q.dialog({
    title: "Duplicar Protocolo",
    message: "Deseja criar uma cópia deste protocolo?",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    $q.notify({
      color: "positive",
      message: "Protocolo duplicado com sucesso",
      icon: "eva-copy-outline",
      position: "top-right",
    });
  });
};

const arquivarProtocolo = () => {
  $q.dialog({
    title: "Arquivar Protocolo",
    message: "Este protocolo será movido para o arquivo.",
    cancel: true,
    persistent: true,
  }).onOk(() => {
    $q.notify({
      color: "info",
      message: "Protocolo arquivado",
      icon: "eva-archive-outline",
      position: "top-right",
    });
  });
};

const excluirProtocolo = () => {
  $q.dialog({
    title: "Excluir Protocolo",
    message: "ATENÇÃO: Esta ação não pode ser desfeita. Confirma a exclusão?",
    cancel: true,
    persistent: true,
    color: "negative",
  }).onOk(() => {
    $q.notify({
      color: "negative",
      message: "Protocolo excluído",
      icon: "eva-trash-2-outline",
      position: "top-right",
    });
    $router.push({ name: "dashboard" });
  });
};

onMounted(async () => {
  if (!protocoloStore.protocoloSelecionado) {
    try {
      await protocoloStore.show($route.params.id);
    } catch (error) {
      console.warn("Erro ao carregar protocolo após refresh:", error);
      $q.notify({
        color: "negative",
        message: "Não foi possível carregar o protocolo.",
        icon: "eva-alert-circle-outline",
        position: "top-right",
      });
      $router.push({ name: "dashboard" });
    } finally {
    }
  }
});
</script>

<style lang="scss" scoped>
// Header compacto
.compact-header {
  min-height: 56px;
  border-bottom: 1px solid #f0f0f0;
}

.protocol-badge-compact {
  margin-right: 8px;
}

// Tabs compactas
.compact-tabs {
  background: transparent;

  .compact-tab {
    min-height: 40px;
    padding: 0;
    transition: all 0.2s ease;

    &:hover {
      background: rgba(var(--q-primary-rgb), 0.05);
    }

    &.q-tab--active {
      background: rgba(var(--q-primary-rgb), 0.1);

      .tab-content-compact {
        .q-icon {
          color: var(--q-primary);
        }

        .tab-label-compact {
          color: var(--q-primary);
          font-weight: 600;
        }
      }
    }

    .tab-content-compact {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 12px;
      position: relative;

      .q-icon {
        color: #666;
        transition: color 0.2s ease;
      }

      .tab-label-compact {
        font-size: 0.8rem;
        font-weight: 500;
        color: #555;
        transition: all 0.2s ease;
      }

      .tab-badge-compact {
        margin-left: 4px;
      }
    }
  }
}

// Conteúdo principal
.protocol-content-compact {
  min-height: calc(100vh - 120px);
  padding-bottom: 60px; // Espaço para o footer
}

// Footer compacto
.compact-footer {
  border-top: 1px solid #f0f0f0;
  min-height: 60px;
}

.financial-summary-compact {
  .summary-item-compact {
    display: flex;
    align-items: center;
    white-space: nowrap;
  }
}

// Responsividade
@media (max-width: 768px) {
  .compact-header {
    min-height: 52px;
  }

  .compact-tabs {
    .compact-tab {
      min-height: 36px;

      .tab-content-compact {
        padding: 6px 8px;
        gap: 4px;

        .tab-label-compact {
          font-size: 0.75rem;
        }
      }
    }
  }

  .financial-summary-compact {
    .row {
      gap: 8px;
    }

    .summary-item-compact {
      font-size: 0.8rem;
    }
  }
}

// Mobile muito pequeno
@media (max-width: 480px) {
  .compact-tabs {
    .compact-tab {
      .tab-content-compact {
        .tab-label-compact {
          display: none;
        }

        .q-icon {
          font-size: 18px;
        }
      }
    }
  }

  .financial-summary-compact {
    .row {
      flex-direction: column;
      align-items: flex-start;
      gap: 4px;
    }
  }
}
</style>