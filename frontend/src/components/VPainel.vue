<template>
  <q-dialog v-model="model" :persistent="persistente" :no-backdrop-dismiss="noBackdropDismiss"
    :position="lado" class="v-painel-container" :transition-show="`slide-${transicaoEntrada}`"
    :transition-hide="`slide-${transicaoSaida}`" transition-duration="200">
    <q-card class="v-painel-card" :style="{ width: larguraComputed }">
      <!-- Cabeçalho -->
      <q-card-section class="v-painel-cabecalho row items-center no-wrap">
        <div class="col">
          <div class="v-painel-titulo">{{ titulo }}</div>
          <div v-if="subtitulo" class="v-painel-subtitulo">{{ subtitulo }}</div>
        </div>
        <div class="v-painel-controles">
          <slot name="controles"></slot>
          <q-btn icon="fa-light fa-times" size="sm" round dense flat color="grey-7"
            @click="$emit('close')" />
        </div>
      </q-card-section>

      <q-separator />

      <!-- Conteúdo -->
      <div class="v-painel-conteudo" :class="{ 'no-padding': noPadding }">
        <slot></slot>
      </div>

      <!-- Rodapé -->
      <template v-if="$slots.rodape">
        <q-separator />
        <div class="v-painel-rodape">
          <slot name="rodape"></slot>
        </div>
      </template>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed } from "vue";

defineOptions({
  name: "VPainel",
});

const props = defineProps({
  titulo: {
    type: String,
    default: "",
  },
  subtitulo: {
    type: String,
    default: "",
  },
  lado: {
    type: String,
    default: "right",
    validator: (v) => ["right", "left"].includes(v),
  },
  largura: {
    type: [String, Number],
    default: 480,
  },
  persistente: {
    type: Boolean,
    default: false,
  },
  noBackdropDismiss: {
    type: Boolean,
    default: false,
  },
  noPadding: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["close"]);
const model = defineModel({ default: false });

// right: entra da direita (slide-left) e sai pela direita (slide-right)
// left: entra da esquerda (slide-right) e sai pela esquerda (slide-left)
const transicaoEntrada = computed(() => (props.lado === "right" ? "left" : "right"));
const transicaoSaida = computed(() => (props.lado === "right" ? "right" : "left"));

const larguraComputed = computed(() => {
  if (typeof props.largura === "number") return `${props.largura}px`;
  return props.largura;
});
</script>

<style lang="scss">
.v-painel-container .q-dialog__inner--minimized {
  padding: 0 !important;
}
</style>

<style lang="scss" scoped>
.v-painel-card {
  height: 100vh !important;
  max-height: 100vh !important;
  border-radius: 0 !important;
  display: flex;
  flex-direction: column;
}

.v-painel-cabecalho {
  padding: 16px !important;
  background-color: #fff;
  min-height: auto;
}

.v-painel-titulo {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  line-height: 1.3;
}

.v-painel-subtitulo {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 2px;
}

.v-painel-controles {
  display: flex;
  gap: 8px;
  align-items: center;
}

.v-painel-conteudo {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  background-color: #f5f5f5;

  &.no-padding {
    padding: 0;
  }
}

.v-painel-rodape {
  background-color: #fff;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}

@media (max-width: 600px) {
  .v-painel-card {
    width: 100vw !important;
    max-width: 100vw !important;
  }
}
</style>
