<template>
  <q-card flat bordered class="v-stat-card">
    <q-card-section class="q-pa-md">
      <div class="row items-start no-wrap">
        <div class="col">
          <div class="v-stat-card__label">{{ label }}</div>
          <div class="v-stat-card__valor">{{ valorFormatado }}</div>
          <div v-if="tendencia !== null" class="v-stat-card__tendencia" :class="tendenciaClass">
            <q-icon :name="tendenciaIcon" size="14px" />
            {{ Math.abs(tendencia) }}%
            <span v-if="tendenciaTexto" class="v-stat-card__tendencia-texto">{{ tendenciaTexto }}</span>
          </div>
        </div>
        <div v-if="icon" class="v-stat-card__icon" :style="{ backgroundColor: corFundo, color: cor }">
          <q-icon :name="icon" size="20px" />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from "vue";

defineOptions({ name: "VStatCard" });

const props = defineProps({
  label: { type: String, required: true },
  valor: { type: [String, Number], required: true },
  icon: { type: String, default: "" },
  cor: { type: String, default: "#4f46e5" },
  tendencia: { type: Number, default: null },
  tendenciaTexto: { type: String, default: "" },
  prefixo: { type: String, default: "" },
  sufixo: { type: String, default: "" },
});

const valorFormatado = computed(() => {
  return `${props.prefixo}${props.valor}${props.sufixo}`;
});

const tendenciaClass = computed(() => {
  if (props.tendencia > 0) return "v-stat-card__tendencia--up";
  if (props.tendencia < 0) return "v-stat-card__tendencia--down";
  return "v-stat-card__tendencia--neutral";
});

const tendenciaIcon = computed(() => {
  if (props.tendencia > 0) return "fa-light fa-arrow-trend-up";
  if (props.tendencia < 0) return "fa-light fa-arrow-trend-down";
  return "fa-light fa-minus";
});

const corFundo = computed(() => {
  return props.cor + "14";
});
</script>

<style lang="scss" scoped>
.v-stat-card {
  &__label {
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  &__valor {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
    margin-top: 4px;
  }

  &__icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  &__tendencia {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-top: 8px;

    &--up {
      color: #16a34a;
    }

    &--down {
      color: #dc2626;
    }

    &--neutral {
      color: #64748b;
    }
  }

  &__tendencia-texto {
    font-weight: 400;
    color: #64748b;
  }
}
</style>
