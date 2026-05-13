<template>
  <v-autocomplete
    v-bind="$attrs"
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :options="cidades"
    option-label="nome"
    option-value="nome"
    :loading="loading"
    :disable="!uf"
    :placeholder="placeholder"
    search-placeholder="Buscar cidade..."
    no-option-text="Nenhuma cidade encontrada"
    @search="onSearch"
  />
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

defineOptions({ name: 'VSelectCidade', inheritAttrs: false })

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null,
  },
  uf: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Selecione a cidade',
  },
})

defineEmits(['update:modelValue'])

const loading = ref(false)
const allCidades = ref([])
const cidades = ref([])

const fetchCidades = async (uf) => {
  if (!uf) {
    allCidades.value = []
    cidades.value = []
    return
  }

  loading.value = true
  try {
    const { data } = await axios.get(
      `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios?orderBy=nome`
    )
    allCidades.value = data.map((c) => ({ nome: c.nome }))
    cidades.value = allCidades.value
  } catch {
    allCidades.value = []
    cidades.value = []
  } finally {
    loading.value = false
  }
}

const onSearch = (term) => {
  if (!term) {
    cidades.value = allCidades.value
    return
  }
  const lower = term.toLowerCase()
  cidades.value = allCidades.value.filter((c) =>
    c.nome.toLowerCase().includes(lower)
  )
}

watch(() => props.uf, (newUf) => {
  fetchCidades(newUf)
}, { immediate: true })
</script>
