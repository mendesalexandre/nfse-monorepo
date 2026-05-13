<template>
  <q-form ref="formRef" greedy @submit.prevent="onSubmit" @reset="onReset">
    <slot :validate="validate" :reset="reset" :loading="loading" />
  </q-form>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({ name: 'VForm' })

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'reset'])

const formRef = ref(null)

const validate = () => formRef.value?.validate()

const reset = () => formRef.value?.resetValidation()

const onSubmit = async () => {
  const valid = await formRef.value.validate()
  if (valid) {
    emit('submit')
  }
}

const onReset = () => {
  formRef.value.resetValidation()
  emit('reset')
}

defineExpose({ validate, reset })
</script>
