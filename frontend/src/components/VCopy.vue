<template>
  <span class="v-copy" @click="copiar">
    <span class="v-copy__texto">{{ valor }}</span>
    <q-icon :name="copiado ? 'fa-light fa-check' : 'fa-light fa-copy'" size="14px"
      class="v-copy__icon" :class="{ 'v-copy__icon--copiado': copiado }" />
    <q-tooltip v-if="!copiado">Copiar</q-tooltip>
    <q-tooltip v-else>Copiado!</q-tooltip>
  </span>
</template>

<script setup>
import { ref } from "vue";
import { copyToClipboard } from "quasar";

defineOptions({ name: "VCopy" });

const props = defineProps({
  valor: { type: [String, Number], required: true },
});

const copiado = ref(false);
let timer = null;

const copiar = async () => {
  try {
    await copyToClipboard(String(props.valor));
    copiado.value = true;
    clearTimeout(timer);
    timer = setTimeout(() => (copiado.value = false), 2000);
  } catch {
    // fallback silencioso
  }
};
</script>

<style lang="scss" scoped>
.v-copy {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  border-radius: 4px;
  padding: 2px 6px;
  transition: background-color 0.15s;

  &:hover {
    background-color: rgba(0, 0, 0, 0.04);
  }

  &__texto {
    user-select: all;
  }

  &__icon {
    color: #94a3b8;
    transition: color 0.2s;

    &--copiado {
      color: #16a34a;
    }
  }

  &:hover &__icon:not(&__icon--copiado) {
    color: #64748b;
  }
}
</style>
