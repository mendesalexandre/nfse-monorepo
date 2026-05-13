<template>
  <q-badge :color="badgeColor" :label="badgeLabel" />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  valor: {
    type: [Boolean, String, Number],
    required: true,
  },
  // Labels customizáveis
  labelAtivo: {
    type: String,
    default: 'Ativo',
  },
  labelInativo: {
    type: String,
    default: 'Inativo',
  },
  // Cores customizáveis
  corAtivo: {
    type: String,
    default: 'positive',
  },
  corInativo: {
    type: String,
    default: 'grey',
  },
  // Mapa de status customizado: { valor: { label, cor } }
  mapa: {
    type: Object,
    default: null,
  },
})

const badgeColor = computed(() => {
  if (props.mapa && props.mapa[props.valor]) {
    return props.mapa[props.valor].cor
  }
  return props.valor ? props.corAtivo : props.corInativo
})

const badgeLabel = computed(() => {
  if (props.mapa && props.mapa[props.valor]) {
    return props.mapa[props.valor].label
  }
  return props.valor ? props.labelAtivo : props.labelInativo
})
</script>
