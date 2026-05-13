<template>
  <div class="row q-gutter-sm justify-center">
    <q-input v-for="(_, index) in length" :key="index" v-model="fieldValues[index]"
      :ref="(el) => updateFieldRef(el, index)" outlined dense hide-bottom-space maxlength="1"
      input-class="text-center" style="width: 6ch" @keyup="onKeyUp($event, index)"
      @update:model-value="onUpdate($event, index)" />
  </div>
</template>

<script setup>
import { computed, onBeforeUpdate, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  length: {
    type: Number,
    default: 6,
  },
})

const emit = defineEmits(['update:modelValue'])

const fields = ref([])
const fieldValues = ref(Array(props.length).fill(''))

const composite = computed(() => {
  const filled = fieldValues.value.filter((v) => v)
  return filled.length === props.length ? filled.join('') : ''
})

watch(composite, (val) => {
  emit('update:modelValue', val)
})

onBeforeUpdate(() => {
  fields.value = []
})

const updateFieldRef = (el, index) => {
  if (el) fields.value[index] = el
}

const focus = (index) => {
  if (index >= 0 && index < props.length) {
    fields.value[index]?.select()
  } else if (index >= props.length && composite.value) {
    fields.value[props.length - 1]?.blur()
  }
}

const onUpdate = (value, index) => {
  if (value) focus(index + 1)
}

const onKeyUp = (event, index) => {
  const key = event.key

  if (['Tab', 'Shift', 'Meta', 'Control', 'Alt', 'Delete'].includes(key)) return

  if (key === 'ArrowLeft' || key === 'Backspace') {
    if (index > 0) focus(index - 1)
    return
  }

  if (key === 'ArrowRight') {
    if (index < props.length - 1) focus(index + 1)
  }
}
</script>
