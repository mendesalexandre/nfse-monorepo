<template>
  <q-input v-bind="$attrs" :model-value="modelValue" @update:model-value="onInput"
    :mask="mask" unmasked-value :placeholder="placeholder" outlined dense hide-bottom-space />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

const digits = computed(() => String(props.modelValue || '').replace(/\D/g, ''))

const isCelular = computed(() => digits.value.length > 10)

const mask = computed(() => isCelular.value ? '(##) #####-####' : '(##) ####-####')

const placeholder = computed(() => isCelular.value ? '(00) 00000-0000' : '(00) 0000-0000')

const onInput = (val) => {
  emit('update:modelValue', val)
}
</script>
