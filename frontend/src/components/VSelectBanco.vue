<template>
  <v-autocomplete v-bind="$attrs" :options="bancosComLogo" option-label="nome" option-value="id" outlined dense
    :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
    placeholder="Selecione o banco..." search-placeholder="Buscar banco...">
    <template v-slot:option="{ opt, selected }">
      <q-item-section avatar>
        <img v-if="opt.img" :src="opt.img" :alt="opt.nome" class="logo-banco" />
        <q-icon v-else name="fa-regular fa-credit-card" size="24px" color="grey-5" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ opt.nome }}</q-item-label>
        <q-item-label v-if="opt.codigo" caption>Codigo: {{ opt.codigo }}</q-item-label>
      </q-item-section>
      <q-item-section v-if="selected" side>
        <q-icon name="check" color="primary" size="20px" />
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

const bancosComLogo = computed(() =>
  props.options.map((b) => ({
    ...b,
    img: b.logo ? `/assets/bandeiras/bancos/${b.logo.replace('.png', '.svg')}` : null,
  }))
)
</script>

<style scoped>
.logo-banco {
  width: 28px;
  height: 20px;
  object-fit: contain;
}
</style>
