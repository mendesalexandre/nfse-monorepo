<template>
  <ErrorNotFound
    code="403"
    title="Acesso negado"
    message="Você não tem permissão para acessar esta página."
    icon="fa-light fa-lock"
  />
</template>

<script setup>
import ErrorNotFound from './ErrorNotFound.vue'
defineOptions({ name: 'Error403' })
</script>
