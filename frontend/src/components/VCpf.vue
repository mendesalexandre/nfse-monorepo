<template>
  <q-input v-bind="$attrs" :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
    mask="###.###.###-##" unmasked-value placeholder="000.000.000-00" outlined dense hide-bottom-space />
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
})

defineEmits(['update:modelValue'])
</script>
