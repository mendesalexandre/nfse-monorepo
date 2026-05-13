<template>
  <div class="row q-col-gutter-md q-pa-md">
    <!-- Coluna Principal -->
    <div class="col-md-8 col-sm-12 col-xs-12">
      <!-- Dados Gerais -->
      <q-card bordered class="q-mb-md rounded-borders">
        <q-card-section class="q-pa-none">
          <div class="ds-header">
            <q-icon name="eva-info-outline" class="q-mr-sm" />
            <span class="ds-header-title">Dados Gerais</span>
            <q-space />
            <q-chip
              :color="getStatusColor(protocolo?.status)"
              :label="protocolo?.status || 'Pendente'"
              text-color="white"
              size="sm"
              class="rounded-borders"
              outline
            />
          </div>
        </q-card-section>

        <q-card-section class="q-pa-none">
          <!-- Tabela com background branco -->
          <q-markup-table flat class="ds-table-branca">
            <tbody>
              <tr>
                <td class="ds-table-label">Serviço Principal</td>
                <td class="ds-table-value">
                  {{ protocolo?.natureza?.nome || "--" }}
                </td>
              </tr>
              <tr>
                <td class="ds-table-label">Data de Cadastro</td>
                <td class="ds-table-value">
                  {{ dataHora(protocolo?.data_cadastro) || "--" }}
                </td>
              </tr>
              <tr>
                <td class="ds-table-label">Vencimento</td>
                <td class="ds-table-value">
                  {{ data(protocolo?.data_cadastro) || "--" }}
                </td>
              </tr>
              <tr>
                <td class="ds-table-label">Solicitante</td>
                <td class="ds-table-value">
                  <div class="text-weight-medium text-primary">
                    {{ protocolo?.cliente?.nome || "--" }}
                  </div>
                  <div
                    v-if="protocolo?.cliente?.cpf_cnpj"
                    class="text-caption text-grey-6"
                  >
                    {{ formatarCpfCnpj(protocolo.cliente.cpf_cnpj) }}
                  </div>
                </td>
              </tr>
              <tr>
                <td class="ds-table-label">Cartório</td>
                <td class="ds-table-value">
                  {{ protocolo?.cartorio?.nome || "--" }}
                </td>
              </tr>
            </tbody>
          </q-markup-table>
        </q-card-section>
      </q-card>

      <!-- Anotações - VERSÃO MELHORADA COM SCROLL INTERNO -->
      <q-card bordered class="rounded-borders ds-anotacoes-melhoradas">
        <q-card-section class="q-pa-none">
          <div class="ds-header">
            <q-icon name="eva-message-square-outline" class="q-mr-sm" />
            <span class="ds-header-title">Anotações</span>
            <q-space />
            <q-chip
              v-if="protocolo?.anotacoes?.length > 0"
              :label="protocolo.anotacoes.length"
              color="blue-grey-3"
              text-color="blue-grey-8"
              size="sm"
              class="rounded-borders q-mr-sm"
            />
            <q-btn
              icon="eva-plus"
              flat
              round
              size="sm"
              color="primary"
              @click="toggleNovaAnotacao"
              :class="{ 'rotate-45': mostrandoNovaAnotacao }"
            />
          </div>
        </q-card-section>

        <q-card-section class="q-pa-md">
          <!-- Editor - só aparece quando necessário -->
          <q-slide-transition>
            <div
              v-show="mostrandoNovaAnotacao"
              class="q-mb-md ds-nova-anotacao"
            >
              <editor-basico
                v-model="novaAnotacao"
                :altura="120"
                id="ide-minuta"
                placeholder="Digite sua anotação..."
              />
              <div class="row justify-end q-mt-sm q-gutter-xs">
                <q-btn
                  label="Cancelar"
                  flat
                  size="sm"
                  @click="cancelarAnotacao"
                  class="rounded-borders"
                />
                <q-btn
                  label="Publicar"
                  color="primary"
                  size="sm"
                  icon="eva-paper-plane-outline"
                  @click="publicarAnotacao"
                  :disable="!novaAnotacao?.trim()"
                  class="rounded-borders"
                />
              </div>
            </div>
          </q-slide-transition>

          <!-- Histórico fixo e lista com scroll interno -->
          <div v-if="protocolo?.anotacoes?.length > 0" class="ds-annotations">
            <div class="text-caption text-grey-7 q-mb-sm">
              <span>Histórico de Anotações:</span>
            </div>

            <div class="ds-annotations-container">
              <div
                v-for="anotacao in protocolo.anotacoes"
                :key="anotacao.id"
                class="ds-annotation-item"
              >
                <div class="row items-center q-gutter-sm q-mb-xs">
                  <q-avatar size="24px" color="blue-grey-6" text-color="white">
                    {{ anotacao?.autor?.nome?.charAt(0) || "U" }}
                  </q-avatar>
                  <span class="text-caption text-weight-medium text-grey-8">
                    {{ anotacao?.autor?.nome }}
                  </span>
                  <span class="text-caption text-grey-6">
                    {{ dataHora(anotacao?.data_cadastro) }}
                  </span>
                  <q-space />
                  <div class="row q-gutter-xs anotacao-actions">
                    <q-btn
                      icon="eva-edit-outline"
                      flat
                      round
                      size="sm"
                      color="grey-7"
                      @click="editarAnotacao(anotacao)"
                    >
                      <q-tooltip>Editar</q-tooltip>
                    </q-btn>
                    <q-btn
                      icon="eva-trash-2-outline"
                      flat
                      round
                      size="sm"
                      color="grey-7"
                      @click="confirmarExclusao(anotacao)"
                    >
                      <q-tooltip>Excluir</q-tooltip>
                    </q-btn>
                  </div>
                </div>
                <div
                  class="text-body2 text-grey-8 anotacao-conteudo"
                  v-html="anotacao.anotacao"
                />
              </div>
            </div>
          </div>

          <!-- Estado vazio -->
          <div v-else class="text-center text-grey-6 q-py-md">
            <q-icon
              name="eva-message-square-outline"
              size="3em"
              class="q-mb-sm"
            />
            <div class="text-body2">Nenhuma anotação ainda</div>
            <div class="text-caption">
              Clique no + acima para adicionar a primeira
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Coluna Lateral -->
    <div class="col-md-4 col-sm-12 col-xs-12">
      <!-- Processo Atual -->
      <q-card bordered class="q-mb-md rounded-borders">
        <q-card-section class="q-pa-none">
          <div class="ds-header">
            <q-icon name="eva-activity-outline" class="q-mr-sm" />
            <span class="ds-header-title">Processo Atual</span>
            <q-space />
            <q-btn
              label="Encaminhar"
              outline
              color="primary"
              size="sm"
              icon="eva-arrow-forward-outline"
              @click="mostrarModalNovoOficio"
              class="rounded-borders"
            />
          </div>
        </q-card-section>

        <q-card-section class="q-pa-md">
          <div class="ds-process-item">
            <div class="row items-center q-gutter-sm">
              <q-avatar size="32px" color="blue-grey-6" text-color="white">
                {{ protocolo?.funcionario?.nome?.charAt(0) || "U" }}
              </q-avatar>
              <div class="col">
                <div class="text-weight-medium text-grey-8">
                  {{ protocolo?.funcionario?.nome || "Não atribuído" }}
                </div>
                <div class="text-caption text-primary">
                  {{ protocolo?.etapa?.nome || "Sem etapa" }}
                </div>
              </div>
              <q-btn
                icon="eva-arrowhead-right-outline"
                outline
                color="green-6"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Partes no Documento -->
      <q-card bordered class="q-mb-md rounded-borders">
        <q-card-section class="q-pa-none">
          <div class="ds-header">
            <q-icon name="eva-people-outline" class="q-mr-sm" />
            <span class="ds-header-title">Partes</span>
          </div>
        </q-card-section>

        <q-card-section class="q-pa-md">
          <div class="text-center text-grey-6 q-py-md">
            <q-icon name="eva-people-outline" size="2em" class="q-mb-sm" />
            <div class="text-caption">Nenhuma parte cadastrada</div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Ações Rápidas -->
      <q-card bordered class="rounded-borders">
        <q-card-section class="q-pa-none">
          <div class="ds-header">
            <q-icon name="eva-flash-outline" class="q-mr-sm" />
            <span class="ds-header-title">Próximas Etapas</span>
          </div>
        </q-card-section>

        <q-card-section class="q-pa-md">
          <div
            v-if="protocolo?.etapa?.etapas_seguinte?.length > 0"
            class="column q-gutter-sm"
          >
            <q-btn
              v-for="etapa in protocolo.etapa.etapas_seguinte"
              :key="etapa.id"
              outline
              color="green-6"
              no-caps
              class="text-left justify-start rounded-borders"
              size="sm"
              @click="
                avancarEtapa(protocolo.uuid, { proxima_etapa_id: etapa.id })
              "
            >
              <div>
                <div class="text-caption text-grey-6">Avançar para</div>
                <div class="text-weight-medium">{{ etapa.nome }}</div>
              </div>
            </q-btn>
          </div>

          <div v-else class="text-center text-grey-6 q-py-md">
            <q-icon
              name="eva-checkmark-circle-outline"
              size="2em"
              color="green-6"
              class="q-mb-sm"
            />
            <div class="text-caption">Processo finalizado</div>
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import { useProtocoloStore } from "src/stores/protocolo";
import { data, dataHora } from "src/Utils/DataHora";
import Oficio from "src/pages/oficio/Index.vue";
import { formatarCpfCnpj } from "src/Utils";
import { useProtocoloAnotacaoStore } from "src/stores/protocolo-anotacao";
import { Codemirror } from "vue-codemirror";

defineOptions({
  name: "ProtocoloGeralIndex",
});

const protocoloAnotacaoStore = useProtocoloAnotacaoStore();
const protocoloStore = useProtocoloStore();
const { protocolo } = storeToRefs(protocoloStore);

const modalNovoOficio = ref(false);
const mostrandoNovaAnotacao = ref(false);
const novaAnotacao = ref("");

// Função para definir cor do status
const getStatusColor = (status) => {
  const statusColors = {
    VIGENTE: "green-9",
    Pendente: "orange-6",
    Finalizado: "blue-6",
    Cancelado: "red-6",
  };
  return statusColors[status] || "grey-6";
};

const toggleNovaAnotacao = () => {
  mostrandoNovaAnotacao.value = !mostrandoNovaAnotacao.value;
  if (!mostrandoNovaAnotacao.value) {
    novaAnotacao.value = "";
  }
};

const cancelarAnotacao = () => {
  mostrandoNovaAnotacao.value = false;
  novaAnotacao.value = "";
};

async function publicarAnotacao() {
  if (!novaAnotacao.value?.trim()) return;

  await protocoloAnotacaoStore.create({
    protocolo_id: protocolo.value.id,
    anotacao: novaAnotacao.value,
  });

  await protocoloStore.show(protocolo.value.uuid);
  novaAnotacao.value = "";
  mostrandoNovaAnotacao.value = false;
}

async function avancarEtapa(id, dto) {
  try {
    await protocoloStore.avancarEtapa(id, dto);
  } catch (e) {
    console.log(e);
  }
}

const mostrarModalNovoOficio = () => {
  modalNovoOficio.value = !modalNovoOficio.value;
};

const editarAnotacao = (anotacao) => {
  // Implementar edição
  console.log("Editar anotação:", anotacao);
};

const confirmarExclusao = (anotacao) => {
  // Implementar confirmação de exclusão
  console.log("Excluir anotação:", anotacao);
};
</script>

<style lang="scss" scoped>
.ds-header {
  display: flex;
  align-items: center;
  padding: 16px;
  background: #fafafa;
  border-bottom: 1px solid #f0f0f0;

  .ds-header-title {
    font-weight: 600;
    color: #424242;
  }
}

.ds-anotacoes-melhoradas {
  .ds-nova-anotacao {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 2px;
    border: 1px solid #e9ecef;
  }

  .ds-annotations {
    // Container das anotações com altura fixa
    max-height: 400px;

    .ds-annotations-container {
      // Área com scroll interno limitado
      max-height: 320px;
      overflow-y: auto;

      &::-webkit-scrollbar {
        width: 4px;
      }

      &::-webkit-scrollbar-track {
        background: #f5f5f5;
      }

      &::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 2px;

        &:hover {
          background: #999;
        }
      }
    }
  }

  .ds-annotation-item {
    padding: 4px;
    border-bottom: 1px solid #f5f5f5;
    transition: all 0.2s ease;

    &:hover {
      .anotacao-actions {
        opacity: 1;
      }
    }

    &:last-child {
      border-bottom: none;
    }

    .anotacao-actions {
      opacity: 0;
      transition: opacity 0.2s ease;
    }

    .anotacao-conteudo {
      margin-left: 32px;
      margin-top: 8px;
      line-height: 1.5;
    }
  }
}

.rotate-45 {
  transform: rotate(45deg);
  transition: transform 0.2s ease;
}

// Tabela com background branco
.ds-table-branca {
  .ds-table-label {
    width: 30%;
    font-weight: 500;
    color: #666;
    padding: 8px 16px;
    background: #ffffff !important; // Background branco forçado
  }

  .ds-table-value {
    padding: 4px 16px;
    color: #424242;
    background: #ffffff !important; // Background branco forçado
  }

  // Remove hover effect da tabela
  tbody tr:hover {
    background: transparent !important;
  }

  // Força background branco em toda a tabela
  tbody tr {
    background: #ffffff !important;
  }
}

.ds-table {
  .ds-table-label {
    width: 30%;
    font-weight: 500;
    color: #666;
    padding: 8px 16px;
    background: #fafafa;
  }

  .ds-table-value {
    padding: 8px 16px;
    color: #424242;
  }

  // Remove hover effect da tabela
  tbody tr:hover {
    background: transparent !important;
  }
}
</style>
