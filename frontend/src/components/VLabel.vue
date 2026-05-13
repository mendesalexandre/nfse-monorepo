<template>
  <label class="v-label">
    <span class="v-label-titulo" :title="obrigatorio ? 'Este campo é obrigatório' : ''">
      {{ uppercase ? label.toUpperCase() : label }}
    </span>

    <span v-if="obrigatorio" class="v-label-required">*</span>

    <q-icon v-if="ajuda" name="fa-light fa-circle-question" size="12px" class="v-label-ajuda">
      <q-tooltip>{{ ajuda }}</q-tooltip>
    </q-icon>
  </label>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  obrigatorio: {
    type: Boolean,
    default: false,
  },
  ajuda: {
    type: String,
    default: "",
  },
  uppercase: {
    type: Boolean,
    default: false,
  },
});
</script>

<style scoped>
.v-label {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 2px;
}

.v-label-titulo {
  font-size: 13px;
  font-weight: 400;
  color: #64748b;
  letter-spacing: 0.5px;
}

.v-label-required {
  font-size: 13px;
  font-weight: 400;
  color: #dc2626;
  line-height: 1;
}

.v-label-ajuda {
  color: #94a3b8;
  cursor: pointer;
  transition: color 0.15s ease;
}

.v-label-ajuda:hover {
  color: #64748b;
}
</style>
