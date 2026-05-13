<template>
  <q-dialog v-model="model" :persistent="persistente" :maximized="isMaximized" :full-width="fullWidth"
    :full-height="fullHeight" :seamless="seamless" :position="position" :square="square"
    :no-backdrop-dismiss="noBackdropDismiss" allow-focus-outside class="modal-container" transition-duration="100"
    transition-show="jump-down" transition-hide="jump-up">
    <q-card :style="cardStyles" class="modal-cabecalho" :class="[
      cardCompleto ? 'full-height' : '',
      isMaximized ? 'modal-maximized' : `modal-${tamanho}`,
    ]">
      <!-- Cabeçalho -->
      <q-card-section class="row items-center cabecalho-container"
        :class="[classCabecalho, noPaddingCabecalho ? 'no-padding' : '']"
        :style="{ backgroundColor: corCabecalho, ...styleCabecalho }">
        <!-- Botão Voltar -->
        <q-btn v-if="showBackBtn" icon="fa-light fa-arrow-left" flat round dense size="sm" color="grey-7"
          class="q-mr-sm" @click="$emit('back')" />

        <div class="titulo-container">
          <div class="titulo-centralizado" :class="[classTitulo, corTituloCabecalho]" :style="styleTitulo">
            <img v-if="icone" :src="icone" :alt="titulo" style="height: 24px; vertical-align: middle" class="q-mr-sm" />
            {{ titulo }}
          </div>
          <div v-if="subtitulo" class="subtitulo-centralizado" :class="classSubtitulo" :style="styleSubtitulo">
            {{ subtitulo }}
          </div>
        </div>

        <div class="controles-direita">
          <slot name="controles">
            <q-btn v-if="showMaximizeBtn"
              :icon="internalMaximized ? 'fa-light fa-minimize' : 'fa-light fa-maximize'" size="sm" round dense flat
              color="grey-7" @click="toggleMaximize" />
            <q-btn icon="fa-light fa-times" size="sm" round dense flat :color="corBotaoFechar"
              @click="$emit('close')" />
          </slot>
        </div>
      </q-card-section>

      <!-- Separador -->
      <q-separator v-if="separador" />

      <!-- Área de Tabs -->
      <div v-if="$slots.tabs" class="modal-tabs-container">
        <slot name="tabs"></slot>
      </div>

      <q-separator v-if="$slots.tabs && separador" />

      <!-- Conteúdo -->
      <div class="modal-conteudo" :class="[
        background,
        $slots.tabs ? 'modal-has-tabs' : 'modal-no-tabs',
        isMaximized ? 'conteudo-maximized' : '',
        !$slots.rodape ? 'sem-rodape' : '',
        noPadding ? 'no-padding' : ''
      ]">
        <slot></slot>
      </div>

      <!-- Rodapé -->
      <template v-if="$slots.rodape">
        <q-separator v-if="separador" />
        <div class="modal-rodape">
          <slot name="rodape"></slot>
        </div>
      </template>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { uid } from "quasar";
import { computed, ref, watch } from "vue";

defineOptions({
  name: "Modal",
});

const props = defineProps({
  nome: String,
  id: {
    type: String,
    default: `modal-${uid()}`,
  },
  titulo: {
    type: String,
    default: "Título do Modal",
  },
  subtitulo: {
    type: String,
    default: "",
  },
  icone: {
    type: String,
    default: "",
  },
  visivel: {
    type: Boolean,
    default: true,
  },
  persistente: {
    type: Boolean,
    default: true,
  },
  separador: {
    type: Boolean,
    default: true,
  },
  altura: {
    type: [String, Number],
    default: "auto",
  },
  largura: {
    type: [String, Number],
    default: "auto",
  },
  background: {
    type: String,
    default: "",
  },
  corCabecalho: {
    type: String,
    default: "",
  },
  corTituloCabecalho: {
    type: String,
    default: '',
  },
  cardCompleto: {
    type: Boolean,
    default: false,
  },
  mobile: {
    type: Boolean,
    default: false,
  },
  tamanho: {
    type: String,
    default: "md",
    validator: (value) => ["xs", "sm", "md", "lg", "xl"].includes(value),
  },
  maximized: {
    type: Boolean,
    default: false,
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
  fullHeight: {
    type: Boolean,
    default: false,
  },
  seamless: {
    type: Boolean,
    default: false,
  },
  noPadding: {
    type: Boolean,
    default: false,
  },
  noPaddingCabecalho: {
    type: Boolean,
    default: false,
  },
  position: {
    type: String,
    default: 'standard',
    validator: (value) => ['standard', 'top', 'right', 'bottom', 'left'].includes(value),
  },
  square: {
    type: Boolean,
    default: false,
  },
  noBackdropDismiss: {
    type: Boolean,
    default: false,
  },
  showMaximizeBtn: {
    type: Boolean,
    default: false,
  },
  showBackBtn: {
    type: Boolean,
    default: false,
  },
  corBotaoVoltar: {
    type: String,
    default: 'grey-7',
  },
  corBotaoFechar: {
    type: String,
    default: 'dark',
  },
  classCabecalho: {
    type: String,
    default: '',
  },
  styleCabecalho: {
    type: Object,
    default: () => ({}),
  },
  classTitulo: {
    type: String,
    default: 'text-h6 text-wrap text-uppercase text-bold',
  },
  styleTitulo: {
    type: Object,
    default: () => ({}),
  },
  classSubtitulo: {
    type: String,
    default: 'text-caption text-grey-7',
  },
  styleSubtitulo: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['close', 'update:maximized', 'back']);
const model = defineModel({ default: false });

const internalMaximized = ref(props.maximized);

const isMaximized = computed(() => internalMaximized.value || props.mobile || props.maximized);

const toggleMaximize = () => {
  internalMaximized.value = !internalMaximized.value;
  emit('update:maximized', internalMaximized.value);
};

watch(() => props.maximized, (newVal) => {
  internalMaximized.value = newVal;
});

const cardStyles = computed(() => {
  let styles = {};

  if (isMaximized.value) return styles;

  if (props.fullWidth) styles.width = '100%';
  if (props.fullHeight) styles.height = '100%';

  if (props.altura !== 'auto' && !props.fullHeight) {
    styles.height = typeof props.altura === 'number' ? `${props.altura}px` : props.altura;
  }

  if (props.largura !== 'auto' && !props.fullWidth) {
    styles.width = typeof props.largura === 'number' ? `${props.largura}px` : props.largura;
  }

  if (props.corCabecalho) {
    styles.backgroundColor = props.corCabecalho;
  }

  return styles;
});
</script>

<style lang="scss" scoped>
.cabecalho-container {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.titulo-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.titulo-centralizado {
  text-align: center;
}

.subtitulo-centralizado {
  text-align: center;
  margin-top: 4px;
}

.controles-direita {
  position: absolute;
  right: 16px;
  display: flex;
  gap: 8px;
  align-items: center;
}

.modal-container :deep(.q-dialog__inner--minimized) {
  padding: 50px 0 !important;
}

.modal-container :deep(.q-dialog__inner--maximized) {
  padding: 0 !important;
}

.modal-cabecalho {
  position: relative;
  display: flex;
  flex-direction: column;

  &.modal-maximized {
    width: 100vw !important;
    height: 100vh !important;
    max-width: 100vw !important;
    max-height: 100vh !important;
    border-radius: 0 !important;
    margin: 0 !important;
  }
}

.modal-tabs-container {
  :deep(.q-tabs) {
    background: transparent;

    .q-tab {
      padding: 0px 16px;
      min-height: 30px !important;
      border-radius: 0;
      text-transform: none;
      color: #37474f;
      font-weight: 400;
      letter-spacing: 0.5px;

      &.q-tab--active {
        color: #495057;
        font-weight: 400;
      }

      &:hover {
        color: #495057;
        background-color: rgba(0, 0, 0, 0.04);
      }
    }

    .q-tab__indicator {
      height: 1px;
      background-color: #37474f;
    }

    .q-tab__label {
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 0.5px;
      color: #37474f;
    }

    .q-ripple {
      display: none;
    }
  }
}

.modal-conteudo {
  flex: 1;
  overflow-y: auto;
  padding: 16px !important;
  background-color: #f5f5f5 !important;

  &.no-padding {
    padding: 0 !important;
  }

  &.modal-no-tabs.sem-rodape {
    max-height: 100vh;
    overflow: hidden;
  }

  &.conteudo-maximized {
    &.modal-no-tabs {
      max-height: calc(100vh - 120px) !important;

      &.sem-rodape {
        max-height: calc(100vh - 64px) !important;
      }
    }

    &.modal-has-tabs {
      max-height: calc(100vh - 160px) !important;

      &.sem-rodape {
        max-height: calc(100vh - 104px) !important;
      }
    }
  }
}

.modal-rodape {
  background-color: #fff !important;
  padding: 12px 16px !important;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}

// Tamanhos
.modal-xs {
  max-width: 400px !important;
  width: 100% !important;
}

.modal-sm {
  max-width: 500px !important;
  width: 100% !important;
}

.modal-md {
  max-width: 600px !important;
  width: 100% !important;
}

.modal-lg {
  max-width: 900px !important;
  width: 100% !important;
}

.modal-xl {
  max-width: 1200px !important;
  width: 100% !important;
}

// Responsividade
@media (max-width: 768px) {

  .modal-xs,
  .modal-sm,
  .modal-md,
  .modal-lg,
  .modal-xl {
    width: 100% !important;
    max-width: none !important;
    overflow: hidden;
  }

  .modal-conteudo {
    &.modal-no-tabs {
      max-height: calc(100vh - 100px) !important;
    }

    &.modal-has-tabs {
      max-height: calc(100vh - 120px) !important;
    }

    padding: 12px !important;
  }
}

@media (max-width: 480px) {

  .modal-xs,
  .modal-sm,
  .modal-md,
  .modal-lg,
  .modal-xl {
    width: 98% !important;
    overflow: hidden;
  }
}
</style>