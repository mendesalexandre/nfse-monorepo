<template>
  <modal v-model="aberto" :titulo="titulo" tamanho="sm" @close="fechar">
    <v-alert
      type="warning"
      message="Esta credencial NÃO será exibida novamente. Copie e guarde em local seguro."
    />

    <div v-for="(valor, rotulo) in credenciais" :key="rotulo" class="q-mt-md">
      <v-label :label="rotulo" />
      <q-input
        :model-value="valor"
        readonly
        outlined
        dense
        type="text"
      >
        <template #append>
          <v-copy :valor="valor" tooltip="Copiar" />
        </template>
      </q-input>
    </div>

    <template #rodape>
      <q-btn unelevated color="primary" label="Fechar" icon-right="fa-light fa-check" @click="fechar" />
    </template>
  </modal>
</template>

<script setup>
import { computed } from 'vue'

/**
 * Modal "show-once" para credenciais sensíveis (api_key, client_secret, etc.).
 * Recebe um objeto `credenciais` { rotulo: valor } e exibe cada uma com botão copiar.
 */
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  titulo: { type: String, default: 'Nova credencial' },
  credenciais: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['update:modelValue', 'close'])

const aberto = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const fechar = () => {
  aberto.value = false
  emit('close')
}
</script>
