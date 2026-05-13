<template>
  <q-chip dense :color="cor" text-color="white" :icon="icone" class="text-weight-medium">
    {{ texto }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'

/**
 * Semáforo de validade do cert digital A1.
 *
 * - verde   → > 30 dias até vencer
 * - amarelo → 7 a 30 dias
 * - vermelho → vence em < 7 dias OU já venceu
 * - cinza → sem cert (nullish)
 */
const props = defineProps({
  validade: {
    type: [String, Date, null],
    default: null,
  },
})

const diasParaVencer = computed(() => {
  if (!props.validade) return null
  const hoje = new Date()
  hoje.setHours(0, 0, 0, 0)
  const validade = new Date(props.validade)
  validade.setHours(0, 0, 0, 0)
  return Math.floor((validade - hoje) / (1000 * 60 * 60 * 24))
})

const cor = computed(() => {
  const d = diasParaVencer.value
  if (d === null) return 'grey'
  if (d < 7) return 'negative'
  if (d <= 30) return 'warning'
  return 'positive'
})

const icone = computed(() => {
  const d = diasParaVencer.value
  if (d === null) return 'fa-light fa-circle-question'
  if (d < 0) return 'fa-light fa-triangle-exclamation'
  if (d < 7) return 'fa-light fa-triangle-exclamation'
  if (d <= 30) return 'fa-light fa-clock'
  return 'fa-light fa-shield-check'
})

const texto = computed(() => {
  const d = diasParaVencer.value
  if (d === null) return 'Sem certificado'
  if (d < 0) return `Vencido há ${Math.abs(d)} dia(s)`
  if (d === 0) return 'Vence hoje'
  if (d <= 30) return `Vence em ${d} dia(s)`
  return `Válido (${d} dias)`
})
</script>
