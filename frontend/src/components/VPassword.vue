<template>
  <q-input v-bind="$attrs" :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
    :type="visible ? 'text' : 'password'" outlined dense hide-bottom-space>
    <template v-slot:append>
      <q-icon :name="visible ? 'fa-light fa-eye-slash' : 'fa-light fa-eye'" class="cursor-pointer" size="14px"
        @click="visible = !visible">
        <q-tooltip>{{ visible ? 'Ocultar' : 'Mostrar' }} senha</q-tooltip>
      </q-icon>
      <slot name="append-after" />
    </template>
  </q-input>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue'])

const visible = ref(false)
</script>
