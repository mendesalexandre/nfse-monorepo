<template>
  <q-chip dense :color="cor" text-color="white" :icon="icone" class="text-weight-medium">
    {{ rotulo }}
  </q-chip>
</template>

<script setup>
import { computed } from 'vue'

/**
 * Badge colorido pelo status interno da NFS-e.
 * - emitida    → verde
 * - cancelada  → cinza
 * - rejeitada  → vermelho
 * - erro       → vermelho escuro
 * - pendente   → amarelo
 * - substituida → roxo
 */
const props = defineProps({
  status: { type: String, required: true },
})

const mapa = {
  emitida: { cor: 'positive', icone: 'fa-light fa-circle-check', rotulo: 'Emitida' },
  cancelada: { cor: 'grey-7', icone: 'fa-light fa-ban', rotulo: 'Cancelada' },
  rejeitada: { cor: 'negative', icone: 'fa-light fa-circle-xmark', rotulo: 'Rejeitada' },
  erro: { cor: 'red-9', icone: 'fa-light fa-triangle-exclamation', rotulo: 'Erro' },
  pendente: { cor: 'warning', icone: 'fa-light fa-clock', rotulo: 'Pendente' },
  substituida: { cor: 'purple', icone: 'fa-light fa-arrow-right-arrow-left', rotulo: 'Substituída' },
}

const dado = computed(() => mapa[props.status] || { cor: 'grey', icone: 'fa-light fa-circle-question', rotulo: props.status || '—' })
const cor = computed(() => dado.value.cor)
const icone = computed(() => dado.value.icone)
const rotulo = computed(() => dado.value.rotulo)
</script>
