<template>
  <q-input v-bind="$attrs" :model-value="displayValue" @update:model-value="onInput"
    :readonly="picker"
    mask="##/##/####" placeholder="dd/mm/aaaa" outlined dense hide-bottom-space>
    <template v-slot:append>
      <q-icon name="fa-light fa-calendar" class="cursor-pointer" size="14px" @click.stop="popupRef?.show()" />
    </template>

    <q-popup-proxy ref="popupRef" no-parent-event transition-show="scale" transition-hide="scale">
      <q-date v-model="proxyValue" mask="DD/MM/YYYY" minimal flat class="vdate-calendar"
        @update:model-value="onDateSelect" />
    </q-popup-proxy>
  </q-input>
</template>

<script setup>
import { ref, computed } from 'vue'
import { toDisplay, toIso, isValidDate } from 'src/utils/date'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  picker: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])
const popupRef = ref(null)

const displayValue = computed(() => toDisplay(props.modelValue))

const proxyValue = computed({
  get: () => toDisplay(props.modelValue),
  set: () => {},
})

const onInput = (val) => {
  if (val && val.length === 10 && isValidDate(val)) {
    emit('update:modelValue', toIso(val))
  } else if (!val || val.length === 0) {
    emit('update:modelValue', '')
  }
}

const onDateSelect = (val) => {
  emit('update:modelValue', toIso(val))
  popupRef.value?.hide()
}
</script>

<style lang="scss">
.vdate-calendar {
  min-height: auto !important;
  box-shadow: none !important;

  .q-date__view {
    min-height: auto !important;
    height: auto !important;
  }

  .q-date__calendar-days-container {
    min-height: auto !important;
    height: auto !important;
  }

  .q-date__calendar {
    min-height: auto !important;
  }
}
</style>
