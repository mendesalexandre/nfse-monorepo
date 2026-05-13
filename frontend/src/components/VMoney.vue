<template>
  <q-input v-bind="inputAttrs" :model-value="displayValue" @update:model-value="onInput"
    @blur="onBlur" @focus="onFocus"
    :rules="wrappedRules"
    outlined dense hide-bottom-space inputmode="decimal">
    <template v-if="prefix" v-slot:prepend>
      <span class="v-money-addon">{{ prefix }}</span>
    </template>
    <template v-if="suffix" v-slot:append>
      <span class="v-money-addon">{{ suffix }}</span>
    </template>
  </q-input>
</template>

<script setup>
import { ref, computed, useAttrs } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  modelValue: {
    type: [Number, String],
    default: 0,
  },
  rules: {
    type: Array,
    default: () => [],
  },
  prefix: {
    type: String,
    default: 'R$',
  },
  suffix: {
    type: String,
    default: '',
  },
  precision: {
    type: Number,
    default: 2,
  },
  thousands: {
    type: String,
    default: '.',
  },
  decimal: {
    type: String,
    default: ',',
  },
  min: {
    type: Number,
    default: null,
  },
  max: {
    type: Number,
    default: null,
  },
  disableNegative: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])
const attrs = useAttrs()
const isFocused = ref(false)

// Remove 'rules' dos attrs para não duplicar
const inputAttrs = computed(() => {
  const { rules, ...rest } = attrs
  return rest
})

// Wrapa as rules para passar o valor numérico em vez da string formatada
const wrappedRules = computed(() => {
  return props.rules.map(rule => {
    return () => rule(toNumber(props.modelValue))
  })
})

const toNumber = (val) => {
  if (val === null || val === undefined || val === '') return 0
  if (typeof val === 'number') return val
  let clean = String(val).replace(/[^\d,.\-]/g, '')
  if (clean.includes(',')) {
    clean = clean.replace(/\./g, '').replace(',', '.')
  }
  const num = parseFloat(clean)
  return isNaN(num) ? 0 : num
}

const formatNumber = (num) => {
  const value = Number(num) || 0
  const fixed = value.toFixed(props.precision)
  const [intPart, decPart] = fixed.split('.')

  const intFormatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, props.thousands)

  return props.precision > 0
    ? `${intFormatted}${props.decimal}${decPart}`
    : intFormatted
}

const displayValue = computed(() => {
  const num = toNumber(props.modelValue)
  return formatNumber(num)
})

const onInput = (val) => {
  if (!val && val !== 0) {
    emit('update:modelValue', 0)
    return
  }

  let raw = String(val).replace(/[^\d\-]/g, '')

  if (props.disableNegative) {
    raw = raw.replace(/-/g, '')
  }

  if (!raw || raw === '-') {
    emit('update:modelValue', 0)
    return
  }

  let num = parseInt(raw, 10) / Math.pow(10, props.precision)

  if (props.min !== null && num < props.min) num = props.min
  if (props.max !== null && num > props.max) num = props.max

  emit('update:modelValue', parseFloat(num.toFixed(props.precision)))
}

const onFocus = () => {
  isFocused.value = true
}

const onBlur = () => {
  isFocused.value = false
  const num = toNumber(props.modelValue)
  let value = parseFloat(num.toFixed(props.precision))
  if (props.min !== null && value < props.min) value = props.min
  if (props.max !== null && value > props.max) value = props.max
  emit('update:modelValue', value)
}
</script>

<style lang="scss" scoped>
.v-money-addon {
  font-size: 0.875rem;
  color: #495057;
  background-color: #e9ecef;
  padding: 0 10px;
  margin: -0.375rem -0.75rem;
  display: flex;
  align-items: center;
  align-self: stretch;
  user-select: none;
}

// Prefix — borda direita + espaço para o texto do input
:deep(.q-field__prepend) .v-money-addon {
  border-right: 1px solid #ced4da;
  margin-right: 0;
}

// Suffix — borda esquerda
:deep(.q-field__append) .v-money-addon {
  border-left: 1px solid #ced4da;
  margin-left: 0;
}
</style>
