<template>
  <div class="v-timeline">
    <div v-for="(item, i) in items" :key="i" class="v-timeline__item">
      <div class="v-timeline__dot-col">
        <div class="v-timeline__dot" :style="{ backgroundColor: item.cor || corPadrao }">
          <q-icon :name="item.icon || 'fa-light fa-circle'" size="12px" color="white" />
        </div>
        <div v-if="i < items.length - 1" class="v-timeline__linha"></div>
      </div>
      <div class="v-timeline__conteudo">
        <div class="v-timeline__cabecalho">
          <span class="v-timeline__titulo">{{ item.titulo }}</span>
          <span class="v-timeline__data">{{ item.data }}</span>
        </div>
        <div v-if="item.descricao" class="v-timeline__descricao">{{ item.descricao }}</div>
        <div v-if="item.autor" class="v-timeline__autor">
          <v-avatar :nome="item.autor" :tamanho="20" cor="#64748b" />
          <span>{{ item.autor }}</span>
        </div>
      </div>
    </div>
    <v-empty-state v-if="!items.length" mensagem="Nenhuma atividade registrada" icon="fa-light fa-clock-rotate-left" />
  </div>
</template>

<script setup>
defineOptions({ name: "VTimeline" });

defineProps({
  items: { type: Array, default: () => [] },
  corPadrao: { type: String, default: "#4f46e5" },
});
</script>

<style lang="scss" scoped>
.v-timeline {
  &__item {
    display: flex;
    gap: 12px;
  }

  &__dot-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
  }

  &__dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  &__linha {
    width: 2px;
    flex: 1;
    min-height: 20px;
    background-color: #e2e8f0;
  }

  &__conteudo {
    flex: 1;
    padding-bottom: 20px;
    min-width: 0;
  }

  &__cabecalho {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    min-height: 28px;
  }

  &__titulo {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
  }

  &__data {
    font-size: 0.75rem;
    color: #94a3b8;
    white-space: nowrap;
  }

  &__descricao {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 4px;
    line-height: 1.5;
  }

  &__autor {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 6px;
  }
}
</style>
