<template>
  <div class="v-page-header">
    <div class="v-page-header__left">
      <q-breadcrumbs v-if="breadcrumbs.length" class="v-page-header__breadcrumbs">
        <q-breadcrumbs-el v-for="(item, i) in breadcrumbs" :key="i" :label="item.label"
          :icon="item.icon" :to="item.to" />
      </q-breadcrumbs>
      <div class="v-page-header__titulo-row">
        <q-btn v-if="voltar" flat round dense icon="fa-light fa-arrow-left" size="sm"
          color="grey-7" class="q-mr-sm" @click="$emit('voltar')" />
        <div>
          <div class="v-page-header__titulo">{{ titulo }}</div>
          <div v-if="subtitulo" class="v-page-header__subtitulo">{{ subtitulo }}</div>
        </div>
      </div>
    </div>
    <div class="v-page-header__acoes">
      <slot name="acoes"></slot>
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: "VPageHeader" });

defineProps({
  titulo: { type: String, required: true },
  subtitulo: { type: String, default: "" },
  breadcrumbs: { type: Array, default: () => [] },
  voltar: { type: Boolean, default: false },
});

defineEmits(["voltar"]);
</script>

<style lang="scss" scoped>
.v-page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;

  &__left {
    flex: 1;
    min-width: 0;
  }

  &__breadcrumbs {
    margin-bottom: 4px;
  }

  &__titulo-row {
    display: flex;
    align-items: center;
  }

  &__titulo {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
  }

  &__subtitulo {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 2px;
  }

  &__acoes {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-shrink: 0;
  }
}

@media (max-width: 600px) {
  .v-page-header {
    flex-direction: column;
    gap: 12px;

    &__acoes {
      width: 100%;
      justify-content: flex-end;
    }
  }
}
</style>
