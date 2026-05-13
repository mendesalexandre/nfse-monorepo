<template>
  <div class="v-alert" :class="[`v-alert--${type}`, closable && 'v-alert--closable']">
    <q-icon v-if="icon || defaultIcon" :name="icon || defaultIcon" size="18px" class="v-alert-icon" />
    <div class="v-alert-content">
      <slot>{{ message }}</slot>
    </div>
    <q-icon v-if="closable" name="fa-light fa-xmark" size="14px" class="v-alert-close" @click="$emit('close')" />
  </div>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ name: 'VAlert' })

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (v) => ['info', 'success', 'warning', 'error'].includes(v),
  },
  message: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
  closable: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close'])

const defaultIcon = computed(() => {
  const icons = {
    info: 'fa-light fa-circle-info',
    success: 'fa-light fa-circle-check',
    warning: 'fa-light fa-triangle-exclamation',
    error: 'fa-light fa-circle-exclamation',
  }
  return icons[props.type]
})
</script>

<style scoped>
.v-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  border-left: 3px solid;
  font-size: 0.875rem;
  line-height: 1.4;
}

/* Info */
.v-alert--info {
  background-color: #eff6ff;
  border-left-color: #2563eb;
  color: #1d4ed8;
}

.v-alert--info .v-alert-icon {
  color: #2563eb;
}

/* Success */
.v-alert--success {
  background-color: #f0fdf4;
  border-left-color: #16a34a;
  color: #15803d;
}

.v-alert--success .v-alert-icon {
  color: #16a34a;
}

/* Warning */
.v-alert--warning {
  background-color: #fffbeb;
  border-left-color: #d97706;
  color: #b45309;
}

.v-alert--warning .v-alert-icon {
  color: #d97706;
}

/* Error */
.v-alert--error {
  background-color: #fef2f2;
  border-left-color: #dc2626;
  color: #dc2626;
}

.v-alert--error .v-alert-icon {
  color: #dc2626;
}

/* Content */
.v-alert-content {
  flex: 1;
}

/* Close */
.v-alert-close {
  cursor: pointer;
  opacity: 0.6;
  transition: opacity 0.15s ease;
}

.v-alert-close:hover {
  opacity: 1;
}
</style>
