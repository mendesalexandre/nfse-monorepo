<template>
  <ErrorNotFound
    code="500"
    title="Erro interno"
    message="Ocorreu um erro inesperado. Tente novamente mais tarde."
    icon="fa-light fa-triangle-exclamation"
  />
</template>

<script setup>
import ErrorNotFound from './ErrorNotFound.vue'
defineOptions({ name: 'Error500' })
</script>
