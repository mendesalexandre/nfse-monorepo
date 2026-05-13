<template>
  <q-card flat bordered class="v-checklist">
    <!-- Cabeçalho -->
    <q-card-section class="v-checklist__header">
      <div class="col">
        <div class="v-checklist__titulo">{{ titulo }}</div>
        <div v-if="subtitulo" class="v-checklist__subtitulo">{{ subtitulo }}</div>
      </div>
      <q-btn v-if="closable" icon="fa-light fa-times" flat round dense size="sm" color="grey-7"
        @click="$emit('close')" />
    </q-card-section>

    <!-- Items -->
    <div class="v-checklist__items">
      <div v-for="(item, i) in items" :key="i" class="v-checklist__item"
        :class="{ 'v-checklist__item--concluido': item.concluido, 'v-checklist__item--clicavel': clicavel }"
        @click="clicavel ? $emit('click', item, i) : null">
        <div class="v-checklist__item-icon" :style="iconStyle(item)">
          <q-icon :name="item.icon || 'fa-light fa-circle'" size="18px" />
        </div>
        <div class="v-checklist__item-conteudo">
          <div class="v-checklist__item-titulo">{{ item.titulo }}</div>
          <div v-if="item.descricao" class="v-checklist__item-descricao">{{ item.descricao }}</div>
        </div>
        <q-icon v-if="item.concluido" name="fa-light fa-circle-check" size="20px" color="positive" />
      </div>
    </div>

    <!-- Progresso -->
    <q-card-section v-if="mostrarProgresso" class="v-checklist__footer">
      <div class="v-checklist__progresso">
        <span class="v-checklist__progresso-label">{{ labelProgresso }}</span>
        <strong>{{ progresso }}%</strong>
        <q-circular-progress :value="progresso" size="20px" :thickness="0.25" color="primary" track-color="grey-3" />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from "vue";

defineOptions({ name: "VChecklist" });

const props = defineProps({
  titulo: { type: String, default: "Checklist" },
  subtitulo: { type: String, default: "" },
  items: { type: Array, default: () => [] },
  closable: { type: Boolean, default: false },
  clicavel: { type: Boolean, default: false },
  mostrarProgresso: { type: Boolean, default: true },
  labelProgresso: { type: String, default: "Progresso:" },
});

defineEmits(["close", "click"]);

const progresso = computed(() => {
  if (!props.items.length) return 0;
  const concluidos = props.items.filter((i) => i.concluido).length;
  return Math.round((concluidos / props.items.length) * 100);
});

const iconStyle = (item) => {
  if (item.concluido) {
    return { backgroundColor: "#f0fdf4", color: "#16a34a" };
  }
  return { backgroundColor: "#f8fafc", color: "#64748b" };
};
</script>

<style lang="scss" scoped>
.v-checklist {
  &__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 16px 16px 8px !important;
  }

  &__titulo {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
  }

  &__subtitulo {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-top: 2px;
  }

  &__items {
    padding: 8px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  &__item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #f8fafc;
    border-radius: 10px;
    transition: background-color 0.15s, box-shadow 0.15s;

    &--clicavel {
      cursor: pointer;

      &:hover {
        background: #f1f5f9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
      }
    }

    &--concluido {
      background: #f0fdf4;
    }

    &--concluido &-titulo {
      color: #16a34a;
    }
  }

  &__item-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  &__item-conteudo {
    flex: 1;
    min-width: 0;
  }

  &__item-titulo {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1e293b;
  }

  &__item-descricao {
    font-size: 0.78rem;
    color: #94a3b8;
    margin-top: 2px;
    line-height: 1.4;
  }

  &__footer {
    padding: 12px 16px !important;
  }

  &__progresso {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.8rem;
    color: #64748b;
  }
}
</style>
