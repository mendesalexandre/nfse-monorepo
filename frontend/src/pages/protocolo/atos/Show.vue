<template>
  <modal v-model="model"></modal>
</template>
<script setup>
defineOptions({
  name: "ProtocoloAtosShow",
});

const model = defineModel({ default: false });
</script>
