<template>
  <v-autocomplete v-bind="$attrs" :options="estadosComBandeira" option-label="nome" option-value="id" outlined dense
    :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
    placeholder="Selecione o estado..." search-placeholder="Buscar estado...">
    <template v-slot:option="{ opt, selected }">
      <q-item-section avatar>
        <img :src="opt.img" :alt="opt.sigla" class="bandeira-estado" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ opt.nome }}</q-item-label>
        <q-item-label caption>{{ opt.sigla }}</q-item-label>
      </q-item-section>
      <q-item-section v-if="selected" side>
        <q-icon name="fa-light fa-check" color="primary" size="14px" />
      </q-item-section>
    </template>
  </v-autocomplete>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: null,
  },
  options: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['update:modelValue'])

const estadosComBandeira = computed(() =>
  props.options.map((e) => ({
    ...e,
    img: `/assets/bandeiras/estados/${e.sigla}.svg`,
  }))
)
</script>

<style scoped>
.bandeira-estado {
  width: 24px;
  height: 18px;
  object-fit: contain;
}
</style>
