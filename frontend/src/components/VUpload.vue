<template>
  <div class="v-upload" :class="{ 'v-upload--disabled': disable }">
    <!-- Zona de drop -->
    <div class="v-upload__dropzone" :class="{
      'v-upload__dropzone--dragging': dragging,
      'v-upload__dropzone--error': error,
    }" @dragenter.prevent="onDragEnter" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
      @drop.prevent="onDrop" @click="openFilePicker">
      <input ref="inputRef" type="file" class="v-upload__input" :multiple="multiple" :accept="accept"
        @change="onFileSelect" />

      <slot name="content" :files="files" :dragging="dragging">
        <div class="v-upload__placeholder">
          <q-icon name="fa-regular fa-cloud-arrow-up" size="32px" :color="dragging ? 'primary' : 'grey-5'" />
          <div class="text-body2 q-mt-sm" :class="dragging ? 'text-primary' : 'text-grey-6'">
            Arraste arquivos aqui ou <span class="text-primary cursor-pointer text-weight-medium">clique para
              selecionar</span>
          </div>
          <div v-if="hint" class="text-caption text-grey-5 q-mt-xs">{{ hint }}</div>
        </div>
      </slot>
    </div>

    <!-- Lista de arquivos -->
    <div v-if="files.length" class="v-upload__list q-mt-sm">
      <div v-for="(file, index) in files" :key="index" class="v-upload__file">
        <div class="v-upload__file-info">
          <q-icon :name="fileIcon(file)" size="18px" color="grey-6" class="q-mr-sm" />
          <span class="v-upload__file-name">{{ file.name }}</span>
          <span class="v-upload__file-size text-grey-5">{{ formatSize(file.size) }}</span>
        </div>
        <q-btn icon="fa-regular fa-xmark" flat round dense size="xs" color="grey-6" @click.stop="removeFile(index)">
          <q-tooltip>Remover</q-tooltip>
        </q-btn>
      </div>
    </div>

    <!-- Erro -->
    <div v-if="error" class="v-upload__error text-negative text-caption q-mt-xs q-ml-xs">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  multiple: {
    type: Boolean,
    default: false,
  },
  accept: {
    type: String,
    default: '',
  },
  maxFileSize: {
    type: Number,
    default: 0, // 0 = sem limite
  },
  maxFiles: {
    type: Number,
    default: 0, // 0 = sem limite
  },
  disable: {
    type: Boolean,
    default: false,
  },
  hint: {
    type: String,
    default: '',
  },
  error: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update', 'rejected'])

const inputRef = ref(null)
const files = ref([])
const dragging = ref(false)
let dragCounter = 0

const openFilePicker = () => {
  if (props.disable) return
  inputRef.value?.click()
}

const onDragEnter = () => {
  if (props.disable) return
  dragCounter++
  dragging.value = true
}

const onDragOver = () => {
  if (props.disable) return
  dragging.value = true
}

const onDragLeave = () => {
  if (props.disable) return
  dragCounter--
  if (dragCounter <= 0) {
    dragCounter = 0
    dragging.value = false
  }
}

const onDrop = (event) => {
  if (props.disable) return
  dragCounter = 0
  dragging.value = false
  addFiles(Array.from(event.dataTransfer.files))
}

const onFileSelect = (event) => {
  addFiles(Array.from(event.target.files))
  inputRef.value.value = ''
}

const addFiles = (newFiles) => {
  const accepted = []
  const rejected = []

  for (const file of newFiles) {
    if (props.accept && !matchAccept(file, props.accept)) {
      rejected.push({ file, reason: 'type' })
      continue
    }
    if (props.maxFileSize && file.size > props.maxFileSize) {
      rejected.push({ file, reason: 'size' })
      continue
    }
    accepted.push(file)
  }

  if (rejected.length) emit('rejected', rejected)

  if (!accepted.length) return

  if (props.multiple) {
    const combined = [...files.value, ...accepted]
    files.value = props.maxFiles ? combined.slice(0, props.maxFiles) : combined
  } else {
    files.value = [accepted[0]]
  }

  emit('update', files.value)
}

const removeFile = (index) => {
  files.value.splice(index, 1)
  emit('update', files.value)
}

const matchAccept = (file, accept) => {
  const types = accept.split(',').map((t) => t.trim())
  return types.some((type) => {
    if (type.startsWith('.')) return file.name.toLowerCase().endsWith(type.toLowerCase())
    if (type.endsWith('/*')) return file.type.startsWith(type.replace('/*', '/'))
    return file.type === type
  })
}

const formatSize = (bytes) => {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
}

const fileIcon = (file) => {
  if (file.type.startsWith('image/')) return 'fa-regular fa-image'
  if (file.type === 'application/pdf') return 'fa-regular fa-file-pdf'
  if (file.type.includes('spreadsheet') || file.type.includes('excel')) return 'fa-regular fa-file-excel'
  if (file.type.includes('word') || file.type.includes('document')) return 'fa-regular fa-file-word'
  return 'fa-regular fa-file'
}

const clear = () => {
  files.value = []
  emit('update', files.value)
}

defineExpose({ clear, files })
</script>

<style lang="scss" scoped>
.v-upload {
  &--disabled {
    opacity: 0.5;
    pointer-events: none;
  }

  &__input {
    display: none;
  }

  &__dropzone {
    border: 2px dashed #d4d4d8;
    border-radius: 6px;
    padding: 24px;
    cursor: pointer;
    transition: border-color 0.2s, background-color 0.2s;

    &:hover {
      border-color: #a1a1aa;
      background-color: #fafafa;
    }

    &--dragging {
      border-color: var(--q-primary, #1976d2);
      background-color: rgba(var(--q-primary-rgb, 25, 118, 210), 0.04);
    }

    &--error {
      border-color: var(--q-negative, #c10015);
    }
  }

  &__placeholder {
    text-align: center;
  }

  &__list {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  &__file {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 10px;
    border-radius: 4px;
    background: #f9f9f9;
    border: 1px solid #e4e4e7;
  }

  &__file-info {
    display: flex;
    align-items: center;
    min-width: 0;
    overflow: hidden;
  }

  &__file-name {
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  &__file-size {
    font-size: 12px;
    margin-left: 8px;
    flex-shrink: 0;
  }
}
</style>
