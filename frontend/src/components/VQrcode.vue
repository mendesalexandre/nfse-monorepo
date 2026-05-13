<template>
  <canvas ref="canvasRef" />
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import QRCode from 'qrcode'

const props = defineProps({
  value: {
    type: String,
    default: '',
  },
  size: {
    type: Number,
    default: 200,
  },
  color: {
    type: String,
    default: '#000000',
  },
  background: {
    type: String,
    default: '#ffffff',
  },
  margin: {
    type: Number,
    default: 2,
  },
  errorCorrectionLevel: {
    type: String,
    default: 'M',
    validator: (v) => ['L', 'M', 'Q', 'H'].includes(v),
  },
})

const canvasRef = ref(null)

const render = () => {
  if (!canvasRef.value || !props.value) return

  QRCode.toCanvas(canvasRef.value, props.value, {
    width: props.size,
    margin: props.margin,
    errorCorrectionLevel: props.errorCorrectionLevel,
    color: {
      dark: props.color,
      light: props.background,
    },
  })
}

onMounted(render)
watch(() => [props.value, props.size, props.color, props.background, props.margin, props.errorCorrectionLevel], render)
</script>
