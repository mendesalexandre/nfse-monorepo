<template>
  <div class="v-filter">
    <div class="v-filter__header" @click="toggleAberto">
      <div class="v-filter__header-left">
        <q-icon name="fa-light fa-filter" size="16px" color="grey-7" />
        <span class="v-filter__label">{{ label }}</span>
        <q-badge v-if="totalAtivos > 0" color="primary" :label="totalAtivos" class="q-ml-xs" />
      </div>
      <div class="v-filter__header-right">
        <q-btn v-if="totalAtivos > 0" flat dense size="sm" label="Limpar" color="grey-7"
          class="v-filter__limpar" @click.stop="$emit('limpar')" />
        <q-icon :name="aberto ? 'fa-light fa-chevron-up' : 'fa-light fa-chevron-down'" size="14px" color="grey-6" />
      </div>
    </div>
    <q-slide-transition>
      <div v-show="aberto" class="v-filter__conteudo">
        <slot></slot>
        <div v-if="$slots.acoes" class="v-filter__acoes">
          <slot name="acoes"></slot>
        </div>
      </div>
    </q-slide-transition>
  </div>
</template>

<script setup>
import { ref } from "vue";

defineOptions({ name: "VFilter" });

const props = defineProps({
  label: { type: String, default: "Filtros" },
  totalAtivos: { type: Number, default: 0 },
  iniciarAberto: { type: Boolean, default: false },
});

defineEmits(["limpar"]);

const aberto = ref(props.iniciarAberto);

const toggleAberto = () => {
  aberto.value = !aberto.value;
};
</script>

<style lang="scss" scoped>
.v-filter {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  margin-bottom: 16px;

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s;

    &:hover {
      background-color: #f8fafc;
    }
  }

  &__header-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  &__header-right {
    display: flex;
    align-items: center;
    gap: 4px;
  }

  &__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
  }

  &__limpar {
    font-size: 0.75rem;
  }

  &__conteudo {
    padding: 0 14px 14px;
  }

  &__acoes {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
  }
}
</style>
