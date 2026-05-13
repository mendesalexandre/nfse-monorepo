<template>
  <q-dialog v-model="model" persistent transition-duration="100" transition-show="scale" transition-hide="scale">
    <q-card class="v-confirm-card">
      <q-card-section class="v-confirm-body">
        <q-icon :name="iconName" :size="iconSize" :class="`v-confirm-icon v-confirm-icon--${type}`" />
        <h3 class="v-confirm-title">{{ titulo }}</h3>
        <p class="v-confirm-message">
          <slot>{{ mensagem }}</slot>
        </p>
      </q-card-section>

      <q-separator />

      <q-card-actions class="v-confirm-actions">
        <q-btn flat :label="labelCancelar" color="grey-7" @click="onCancel" />
        <q-btn unelevated :label="labelConfirmar" :color="btnColor" :loading="loading" @click="onConfirm" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ name: 'VConfirm' })

const props = defineProps({
  type: {
    type: String,
    default: 'warning',
    validator: (v) => ['warning', 'danger', 'info'].includes(v),
  },
  titulo: {
    type: String,
    default: 'Confirmar ação',
  },
  mensagem: {
    type: String,
    default: 'Tem certeza que deseja continuar?',
  },
  icon: {
    type: String,
    default: '',
  },
  iconSize: {
    type: String,
    default: '40px',
  },
  labelConfirmar: {
    type: String,
    default: 'Confirmar',
  },
  labelCancelar: {
    type: String,
    default: 'Cancelar',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['confirm', 'cancel'])
const model = defineModel({ default: false })

const iconName = computed(() => {
  if (props.icon) return props.icon
  const icons = {
    warning: 'fa-light fa-triangle-exclamation',
    danger: 'fa-light fa-trash-can',
    info: 'fa-light fa-circle-info',
  }
  return icons[props.type]
})

const btnColor = computed(() => {
  const colors = {
    warning: 'warning',
    danger: 'negative',
    info: 'primary',
  }
  return colors[props.type]
})

const onConfirm = () => {
  emit('confirm')
}

const onCancel = () => {
  model.value = false
  emit('cancel')
}
</script>

<style scoped>
.v-confirm-card {
  width: 100%;
  max-width: 380px;
}

.v-confirm-body {
  text-align: center;
  padding: 24px 24px 16px;
}

.v-confirm-icon {
  margin-bottom: 12px;
}

.v-confirm-icon--warning {
  color: #d97706;
}

.v-confirm-icon--danger {
  color: #dc2626;
}

.v-confirm-icon--info {
  color: #2563eb;
}

.v-confirm-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 8px;
}

.v-confirm-message {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.v-confirm-actions {
  padding: 12px 16px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}
</style>
