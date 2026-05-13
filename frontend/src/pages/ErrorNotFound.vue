<template>
  <div class="error-page">
    <div class="error-page__content">
      <q-icon :name="icon" size="64px" class="error-page__icon" />
      <h1 class="error-page__code">{{ code }}</h1>
      <h2 class="error-page__title">{{ title }}</h2>
      <p class="error-page__message">{{ message }}</p>
      <q-btn unelevated color="primary" label="Voltar ao início" icon="fa-light fa-arrow-left" to="/" />
    </div>
  </div>
</template>

<script setup>
defineOptions({ name: 'ErrorNotFound' })

defineProps({
  code: { type: [String, Number], default: 404 },
  title: { type: String, default: 'Página não encontrada' },
  message: { type: String, default: 'A página que você está procurando não existe ou foi removida.' },
  icon: { type: String, default: 'fa-light fa-circle-question' },
})
</script>

<style scoped>
.error-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 24px;
  background-color: #f5f5f5;
}

.error-page__content {
  text-align: center;
  max-width: 420px;
}

.error-page__icon {
  color: #94a3b8;
  margin-bottom: 16px;
}

.error-page__code {
  font-size: 5rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
  line-height: 1;
}

.error-page__title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #334155;
  margin: 12px 0 8px;
}

.error-page__message {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.6;
  margin: 0 0 24px;
}
</style>
