<template>
  <div class="text-center q-pa-lg">
    <q-icon :name="icon" size="3em" class="text-grey-5 q-mb-sm" />
    <div class="text-body2 text-grey-6">{{ mensagem }}</div>
    <div v-if="descricao" class="text-caption text-grey-5 q-mt-xs">{{ descricao }}</div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  mensagem: {
    type: String,
    default: 'Nenhum registro encontrado',
  },
  descricao: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: 'fa-regular fa-inbox',
  },
})
</script>
