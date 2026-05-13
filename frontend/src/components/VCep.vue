<template>
  <q-input v-bind="$attrs" :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
    mask="#####-###" unmasked-value placeholder="00000-000" outlined dense hide-bottom-space :loading="buscando">
    <template v-slot:append>
      <q-icon name="fa-light fa-magnifying-glass" class="cursor-pointer" size="14px" @click="buscarCep">
        <q-tooltip>Buscar CEP</q-tooltip>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>
import { ref } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
})

const emit = defineEmits(['update:modelValue', 'endereco'])

const buscando = ref(false)

const buscarCep = async () => {
  const cep = String(props.modelValue || '').replace(/\D/g, '')
  if (cep.length !== 8) return

  buscando.value = true
  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    const data = await response.json()

    if (!data.erro) {
      emit('endereco', {
        logradouro: data.logradouro || '',
        bairro: data.bairro || '',
        cidade: data.localidade || '',
        uf: data.uf || '',
        complemento: data.complemento || '',
      })
    }
  } catch {
    // Silently fail - user can fill manually
  } finally {
    buscando.value = false
  }
}
</script>
