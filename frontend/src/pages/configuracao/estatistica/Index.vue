<template>
  <q-card bordered>
    <q-card-section> DOI - Declaração Imobiliária </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <q-toggle v-model="toggle" label="Habilitar envio das estatísticas para o ONR" color="primary" />
        <q-checkbox v-model="toggle" label="Habilitar envio das estatísticas para o ONR" color="primary" />
        <div class="col-md-6 col-sm-12 col-xs-12">
          <v-label label="Chave para envio das estatísticas" :obrigatorio="true"
            :ajuda="'Chave para envio das estatísticas para o ONR'"></v-label>
          <q-input outlined dense />
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <v-label label="Chave para envio das estatísticas" :obrigatorio="true"
            :ajuda="'Chave para envio das estatísticas para o ONR'"></v-label>
          <q-select outlined dense />
        </div>
      </div>
    </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-gutter-md justify-end">
        <q-btn class="q-mt-md" label="Salvar" color="primary" @click="onSalvar" icon="eva-checkmark-outline" />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({
  name: "ConfiguracaoEstatisticaIndex",
});
const toggle = ref(false);
</script>