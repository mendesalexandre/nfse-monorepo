<template>
  <q-dialog v-model="model">
    <q-card class="w-full">
      <q-card-section> </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
const model = defineModel({ default: false });
</script>
