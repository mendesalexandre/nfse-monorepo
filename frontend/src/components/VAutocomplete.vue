<template>
  <div class="v-select-search" :class="{ 'v-select-search--dense': dense, 'v-select-search--disabled': disable }">
    <!-- Trigger que parece um select -->
    <div ref="triggerRef" class="v-select-search__trigger" :class="{
      'v-select-search__trigger--outlined': outlined,
      'v-select-search__trigger--filled': filled,
      'v-select-search__trigger--focused': menuVisible,
      'v-select-search__trigger--error': error || hasValidationError,
      'v-select-search__trigger--disabled': disable,
      'v-select-search__trigger--readonly': readonly,
    }" tabindex="0" @click="openMenu" @keydown.enter.prevent="openMenu" @keydown.space.prevent="openMenu"
      @keydown.down.prevent="openMenu">
      <!-- Prepend slot -->
      <div v-if="$slots.prepend" class="v-select-search__prepend">
        <slot name="prepend" />
      </div>

      <!-- Valor selecionado ou placeholder -->
      <div class="v-select-search__value">
        <template v-if="selectedOption">
          <slot name="selected" :opt="selectedOption">
            <div class="v-select-search__selected-content">
              <q-avatar v-if="selectedOption.img" size="22px" class="q-mr-sm">
                <img :src="selectedOption.img" :alt="getOptionLabel(selectedOption)">
              </q-avatar>
              <q-icon v-else-if="selectedOption.icon" :name="selectedOption.icon" size="20px" class="q-mr-sm" />
              <span class="v-select-search__selected-text">
                {{ getOptionLabel(selectedOption) }}
              </span>
            </div>
          </slot>
        </template>
        <span v-else class="v-select-search__placeholder">
          {{ placeholder }}
        </span>
      </div>

      <!-- Botões de ação -->
      <div class="v-select-search__actions">
        <q-icon v-if="clearable && selectedOption && !disable && !readonly" name="fa-light fa-xmark"
          class="v-select-search__clear" size="14px" @click.stop="clearSelection" />
        <q-spinner v-if="loading" size="sm" color="grey-6" />
        <q-icon v-else :name="menuVisible ? 'fa-light fa-chevron-up' : 'fa-light fa-chevron-down'" size="14px"
          class="v-select-search__arrow" />
      </div>

      <!-- Label flutuante -->
      <label v-if="label" class="v-select-search__label"
        :class="{ 'v-select-search__label--float': selectedOption || menuVisible }">
        {{ label }}
      </label>
    </div>

    <!-- Mensagem de erro -->
    <div v-if="!hideBottomSpace" class="v-select-search__bottom">
      <div v-if="hasValidationError || error" class="v-select-search__error-msg">
        {{ validationMessage || errorMessage }}
      </div>
      <div v-else-if="hint" class="v-select-search__hint">
        {{ hint }}
      </div>
    </div>

    <!-- Menu dropdown -->
    <q-menu v-model="menuVisible" :target="triggerRef" no-parent-event fit no-focus :offset="[0, 4]"
      class="v-select-search__menu" @before-show="onMenuBeforeShow" @hide="onMenuHide">
      <div class="v-select-search__dropdown">
        <!-- Campo de busca -->
        <div class="v-select-search__search-wrapper">
          <q-input ref="searchInputRef" v-model="searchText" :placeholder="searchPlaceholder" dense borderless
            class="v-select-search__search-input" @keydown.down.prevent="focusNextItem"
            @keydown.up.prevent="focusPrevItem" @keydown.enter.prevent="selectFocusedItem"
            @keydown.esc.prevent="closeMenu">
            <template v-slot:prepend>
              <q-icon name="fa-light fa-magnifying-glass" size="14px" color="grey-6" />
            </template>
            <template v-slot:append>
              <q-spinner v-if="loading" size="sm" color="grey-5" />
              <q-icon v-else-if="searchText" name="fa-light fa-xmark" size="14px" class="cursor-pointer" color="grey-5"
                @click="searchText = ''" />
            </template>
          </q-input>
        </div>

        <q-separator />

        <!-- Lista de opções -->
        <q-list class="v-select-search__list" ref="listRef">
          <!-- Loading state -->
          <q-item v-if="loading && displayOptions.length === 0">
            <q-item-section class="text-grey text-center q-pa-md">
              <q-spinner size="24px" color="primary" class="q-mb-sm" />
              <span>Buscando...</span>
            </q-item-section>
          </q-item>

          <!-- Mensagem de caracteres mínimos (busca remota) -->
          <q-item v-else-if="useRemoteSearch && searchText.length < minSearchLength && displayOptions.length === 0">
            <slot name="min-search" :min="minSearchLength">
              <q-item-section class="text-grey text-center q-pa-md">
                Digite pelo menos {{ minSearchLength }} caracteres para buscar
              </q-item-section>
            </slot>
          </q-item>

          <!-- Opções -->
          <template v-else-if="displayOptions.length > 0">
            <q-item v-for="(opt, index) in displayOptions" :key="getOptionValue(opt)" clickable
              :active="isSelected(opt)" active-class="v-select-search__item--active"
              :class="{ 'v-select-search__item--focused': focusedIndex === index }" @click="selectOption(opt)"
              @mouseenter="focusedIndex = index">
              <slot name="option" :opt="opt" :selected="isSelected(opt)" :index="index">
                <q-item-section v-if="opt.img" avatar>
                  <q-avatar size="28px">
                    <img :src="opt.img" :alt="getOptionLabel(opt)">
                  </q-avatar>
                </q-item-section>
                <q-item-section v-else-if="opt.icon" avatar>
                  <q-icon :name="opt.icon" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ getOptionLabel(opt) }}</q-item-label>
                  <q-item-label v-if="opt.description" caption>
                    {{ opt.description }}
                  </q-item-label>
                </q-item-section>
                <q-item-section v-if="isSelected(opt)" side>
                  <q-icon name="fa-light fa-check" color="primary" size="14px" />
                </q-item-section>
              </slot>
            </q-item>
          </template>

          <!-- Sem resultados -->
          <q-item v-else>
            <slot name="no-option" :search="searchText">
              <q-item-section class="text-grey text-center q-pa-md">
                {{ noOptionText }}
              </q-item-section>
            </slot>
          </q-item>
        </q-list>
      </div>
    </q-menu>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'

defineOptions({
  name: 'VAutocomplete'
})

const props = defineProps({
  modelValue: {
    type: [String, Number, Object, Array],
    default: null
  },
  options: {
    type: Array,
    default: () => []
  },
  optionLabel: {
    type: [String, Function],
    default: 'nome'
  },
  optionValue: {
    type: [String, Function],
    default: 'id'
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Selecione...'
  },
  searchPlaceholder: {
    type: String,
    default: 'Buscar...'
  },
  clearable: {
    type: Boolean,
    default: true
  },
  emitValue: {
    type: Boolean,
    default: true
  },
  mapOptions: {
    type: Boolean,
    default: true
  },
  dense: {
    type: Boolean,
    default: false
  },
  outlined: {
    type: Boolean,
    default: false
  },
  filled: {
    type: Boolean,
    default: false
  },
  disable: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  rules: {
    type: Array,
    default: () => []
  },
  error: {
    type: Boolean,
    default: false
  },
  errorMessage: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  },
  hideBottomSpace: {
    type: Boolean,
    default: true
  },
  noOptionText: {
    type: String,
    default: 'Nenhuma opção encontrada'
  },
  // Props para busca remota
  loading: {
    type: Boolean,
    default: false
  },
  useRemoteSearch: {
    type: Boolean,
    default: false
  },
  minSearchLength: {
    type: Number,
    default: 2
  },
  inputDebounce: {
    type: Number,
    default: 300
  }
})

const emit = defineEmits(['update:modelValue', 'search', 'filter', 'clear'])

// Refs
const triggerRef = ref(null)
const searchInputRef = ref(null)
const listRef = ref(null)

// State
const menuVisible = ref(false)
const searchText = ref('')
const focusedIndex = ref(-1)
const hasValidationError = ref(false)
const validationMessage = ref('')
let debounceTimer = null

// Helpers
const getOptionLabel = (opt) => {
  if (opt === null || opt === undefined) return ''
  if (typeof opt === 'string' || typeof opt === 'number') return String(opt)
  if (typeof props.optionLabel === 'function') return props.optionLabel(opt)
  return opt[props.optionLabel] || opt.label || opt.nome || ''
}

const getOptionValue = (opt) => {
  if (opt === null || opt === undefined) return null
  if (typeof opt === 'string' || typeof opt === 'number') return opt
  if (typeof props.optionValue === 'function') return props.optionValue(opt)
  return opt[props.optionValue] || opt.id || opt
}

// Computed
const selectedOption = computed(() => {
  if (props.modelValue === null || props.modelValue === undefined) return null

  if (props.emitValue && props.mapOptions) {
    return props.options.find(opt => getOptionValue(opt) === props.modelValue) || null
  }

  if (typeof props.modelValue === 'object') return props.modelValue

  return props.options.find(opt => getOptionValue(opt) === props.modelValue) || null
})

// Filtro local (quando NÃO é busca remota)
const filteredOptions = computed(() => {
  if (!searchText.value) return props.options

  const query = searchText.value.toLowerCase().trim()
  return props.options.filter(opt => {
    const label = getOptionLabel(opt).toLowerCase()
    const desc = (opt?.description || '').toLowerCase()
    return label.includes(query) || desc.includes(query)
  })
})

// Opções exibidas: remoto usa options direto, local filtra internamente
const displayOptions = computed(() => {
  return props.useRemoteSearch ? props.options : filteredOptions.value
})

// Methods
const openMenu = () => {
  if (props.disable || props.readonly) return
  menuVisible.value = true
}

const closeMenu = () => {
  menuVisible.value = false
}

const onMenuBeforeShow = () => {
  searchText.value = ''
  focusedIndex.value = -1

  nextTick(() => {
    setTimeout(() => {
      searchInputRef.value?.focus()
    }, 50)
  })
}

const onMenuHide = () => {
  searchText.value = ''
  focusedIndex.value = -1
  if (debounceTimer) clearTimeout(debounceTimer)
  validate()
}

const selectOption = (opt) => {
  const value = props.emitValue ? getOptionValue(opt) : opt
  emit('update:modelValue', value)
  closeMenu()
  hasValidationError.value = false
  validationMessage.value = ''
}

const clearSelection = () => {
  emit('update:modelValue', null)
  emit('clear')
  hasValidationError.value = false
  validationMessage.value = ''
}

const isSelected = (opt) => {
  if (props.modelValue === null || props.modelValue === undefined) return false
  return getOptionValue(opt) === (props.emitValue ? props.modelValue : getOptionValue(props.modelValue))
}

// Navegação por teclado
const focusNextItem = () => {
  if (displayOptions.value.length === 0) return
  focusedIndex.value = (focusedIndex.value + 1) % displayOptions.value.length
  scrollToFocused()
}

const focusPrevItem = () => {
  if (displayOptions.value.length === 0) return
  focusedIndex.value = focusedIndex.value <= 0
    ? displayOptions.value.length - 1
    : focusedIndex.value - 1
  scrollToFocused()
}

const selectFocusedItem = () => {
  if (focusedIndex.value >= 0 && focusedIndex.value < displayOptions.value.length) {
    selectOption(displayOptions.value[focusedIndex.value])
  }
}

const scrollToFocused = () => {
  nextTick(() => {
    const list = listRef.value?.$el
    if (!list) return
    const items = list.querySelectorAll('.q-item')
    const item = items[focusedIndex.value]
    if (item) {
      item.scrollIntoView({ block: 'nearest' })
    }
  })
}

// Validação
const validate = () => {
  if (!props.rules.length) return true

  for (const rule of props.rules) {
    const result = rule(props.modelValue)
    if (result !== true) {
      hasValidationError.value = true
      validationMessage.value = typeof result === 'string' ? result : 'Campo inválido'
      return false
    }
  }

  hasValidationError.value = false
  validationMessage.value = ''
  return true
}

// Watch searchText com debounce para busca remota
watch(searchText, (val) => {
  focusedIndex.value = -1

  if (props.useRemoteSearch) {
    // Busca remota: emite @filter com debounce
    if (debounceTimer) clearTimeout(debounceTimer)

    if (val.length < props.minSearchLength) {
      emit('filter', val)
      return
    }

    debounceTimer = setTimeout(() => {
      emit('filter', val)
    }, props.inputDebounce)
  } else {
    // Busca local: emite @search (sem debounce, filtro é computed)
    emit('search', val)
  }
})

defineExpose({ validate })
</script>

<style lang="scss" scoped>
.v-select-search {
  position: relative;
  width: 100%;

  // Trigger — mesmo visual dos q-field--outlined
  &__trigger {
    display: flex;
    align-items: center;
    min-height: 38px;
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    background: #fff;
    position: relative;
    font-size: 1rem;

    &:hover:not(&--disabled):not(&--readonly) {
      border-color: #adb5bd;
    }

    &--focused {
      border-color: var(--q-primary, #1976d2) !important;
      box-shadow: 0 0 0 2px rgba(var(--q-primary-rgb, 25, 118, 210), 0.2);
    }

    &--outlined {
      border: 1px solid #ced4da;
    }

    &--filled {
      background: rgba(0, 0, 0, 0.05);
      border: none;
      border-bottom: 1px solid rgba(0, 0, 0, 0.42);
      border-radius: 0.25rem 0.25rem 0 0;
    }

    &--error {
      border-color: var(--q-negative, #c10015) !important;
    }

    &--disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    &--readonly {
      cursor: default;
    }
  }

  &--dense &__trigger {
    min-height: 38px;
  }

  // Label
  &__label {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: rgba(0, 0, 0, 0.6);
    transition: all 0.2s;
    pointer-events: none;
    background: white;
    padding: 0 4px;

    &--float {
      top: 0;
      font-size: 11px;
      color: var(--q-primary, #1976d2);
    }
  }

  .v-select-search__trigger--error .v-select-search__label {
    color: var(--q-negative, #c10015);
  }

  .v-select-search__trigger--filled .v-select-search__label {
    background: transparent;
  }

  // Valor
  &__value {
    flex: 1;
    min-width: 0;
    overflow: hidden;
  }

  &__selected-content {
    display: flex;
    align-items: center;
    min-width: 0;
    overflow: hidden;
  }

  &__selected-text {
    font-size: 1rem;
    color: #495057;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__placeholder {
    font-size: 1rem;
    color: #6c757d;
  }

  // Actions
  &__actions {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
  }

  &__clear {
    cursor: pointer;
    color: rgba(0, 0, 0, 0.54);
    transition: color 0.2s;

    &:hover {
      color: rgba(0, 0, 0, 0.7);
    }
  }

  &__arrow {
    color: rgba(0, 0, 0, 0.54);
  }

  // Prepend
  &__prepend {
    margin-right: 8px;
    display: flex;
    align-items: center;
  }

  // Bottom space
  &__bottom {
    min-height: 20px;
    padding: 4px 12px 0;
    font-size: 11px;
  }

  &__error-msg {
    color: var(--q-negative, #c10015);
  }

  &__hint {
    color: rgba(0, 0, 0, 0.54);
  }
}
</style>

<style lang="scss">
// Estilos globais para o menu (renderizado no body)
.v-select-search__menu {
  border-radius: 0.25rem !important;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12) !important;
  overflow: hidden;

  .v-select-search__dropdown {
    min-width: 200px;
  }

  .v-select-search__search-wrapper {
    padding: 8px 12px;

    .v-select-search__search-input {
      .q-field__control {
        min-height: 38px !important;
        height: auto !important;
      }
    }
  }

  .v-select-search__list {
    max-height: 280px;
    overflow-y: auto;
    padding: 4px 0;

    .q-item {
      min-height: 40px;
      padding: 6px 16px;
      border-radius: 0;
      transition: background-color 0.15s;

      &:hover {
        background: rgba(0, 0, 0, 0.04);
      }
    }

    .v-select-search__item--active {
      background: rgba(var(--q-primary-rgb, 25, 118, 210), 0.08);
      font-weight: 500;
    }

    .v-select-search__item--focused {
      background: rgba(0, 0, 0, 0.06);
    }
  }
}
</style>
