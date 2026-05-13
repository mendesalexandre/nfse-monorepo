<template>
  <modal v-model="model" titulo="Adicionar nova saída">
    <q-card class="w-full">
      <q-card-section> </q-card-section>
    </q-card>
  </modal>
</template>

<script setup>
defineOptions({
  name: "ProtocoloFinanceiroModal",
});

const model = defineModel({ default: false });
</script>
