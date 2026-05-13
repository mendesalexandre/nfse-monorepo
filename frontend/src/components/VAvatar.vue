<template>
  <div class="v-avatar" :style="avatarStyle">
    <img v-if="src && !imgError" :src="src" :alt="nome" class="v-avatar__img" @error="imgError = true" />
    <span v-else class="v-avatar__iniciais" :style="{ fontSize: inicialFontSize }">{{ iniciais }}</span>
    <span v-if="status" class="v-avatar__status" :class="`v-avatar__status--${status}`"></span>
  </div>
</template>

<script setup>
import { computed, ref } from "vue";

defineOptions({ name: "VAvatar" });

const props = defineProps({
  nome: { type: String, default: "" },
  src: { type: String, default: "" },
  tamanho: { type: [Number, String], default: 40 },
  cor: { type: String, default: "#4f46e5" },
  status: { type: String, default: "", validator: (v) => ["", "online", "offline", "ausente"].includes(v) },
  rounded: { type: Boolean, default: true },
});

const imgError = ref(false);

const tamanhoNum = computed(() => {
  return typeof props.tamanho === "string" ? parseInt(props.tamanho) : props.tamanho;
});

const avatarStyle = computed(() => ({
  width: `${tamanhoNum.value}px`,
  height: `${tamanhoNum.value}px`,
  borderRadius: props.rounded ? "50%" : "8px",
  backgroundColor: props.src && !imgError.value ? "transparent" : props.cor + "1A",
  color: props.cor,
}));

const iniciais = computed(() => {
  if (!props.nome) return "?";
  const partes = props.nome.trim().split(/\s+/);
  if (partes.length === 1) return partes[0][0].toUpperCase();
  return (partes[0][0] + partes[partes.length - 1][0]).toUpperCase();
});

const inicialFontSize = computed(() => {
  return `${tamanhoNum.value * 0.38}px`;
});
</script>

<style lang="scss" scoped>
.v-avatar {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;

  &__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: inherit;
  }

  &__iniciais {
    font-weight: 600;
    line-height: 1;
    user-select: none;
  }

  &__status {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 25%;
    height: 25%;
    min-width: 8px;
    min-height: 8px;
    border-radius: 50%;
    border: 2px solid #fff;

    &--online {
      background-color: #16a34a;
    }

    &--offline {
      background-color: #94a3b8;
    }

    &--ausente {
      background-color: #d97706;
    }
  }
}
</style>
