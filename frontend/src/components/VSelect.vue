<template>
  <q-select v-bind="$attrs" :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)"
    :options="options" :clearable="clearable" :emit-value="emitValue" :option-label="optionLabel"
    :option-value="optionValue" :map-options="mapOptions" :hide-bottom-space="hideBottomSpace" outlined dense>
    <template v-slot:no-option>
      <slot name="no-option">
        <q-item>
          <q-item-section class="text-grey">
            {{ noOptionText }}
          </q-item-section>
        </q-item>
      </slot>
    </template>

    <template v-slot:option="scope">
      <slot name="option" v-bind="scope">
        <q-item v-bind="scope.itemProps">
          <q-item-section v-if="scope.opt.icon" avatar>
            <q-icon :name="scope.opt.icon" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ scope.opt[optionLabel] || scope.opt.label || scope.opt.nome }}</q-item-label>
            <q-item-label v-if="scope.opt.description" caption>
              {{ scope.opt.description }}
            </q-item-label>
          </q-item-section>
          <q-item-section v-if="scope.selected" side>
            <q-icon name="fa-light fa-check" color="primary" size="14px" />
          </q-item-section>
        </q-item>
      </slot>
    </template>

    <template v-if="$slots.prepend" v-slot:prepend>
      <slot name="prepend" />
    </template>

    <template v-if="$slots.append" v-slot:append>
      <slot name="append" />
    </template>
  </q-select>
</template>

<script setup>
defineOptions({
  name: 'VSelect',
  inheritAttrs: false,
})

defineProps({
  modelValue: {
    type: [String, Number, Object, Array],
    default: null,
  },
  options: {
    type: Array,
    default: () => [],
  },
  clearable: {
    type: Boolean,
    default: true,
  },
  emitValue: {
    type: Boolean,
    default: true,
  },
  mapOptions: {
    type: Boolean,
    default: true,
  },
  optionLabel: {
    type: String,
    default: 'nome',
  },
  optionValue: {
    type: String,
    default: 'id',
  },
  noOptionText: {
    type: String,
    default: 'Nenhuma opção encontrada',
  },
  hideBottomSpace: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['update:modelValue'])
</script>
