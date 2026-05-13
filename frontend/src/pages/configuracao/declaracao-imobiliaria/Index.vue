<template>
  <q-card bordered>
    <q-card-section> ONR - Mapa e Estatísticas </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <v-label label="Chave para envio das estatísticas" :obrigatorio="true"
            :ajuda="'Chave para envio das estatísticas para o ONR'"></v-label>
          <q-input outlined dense />
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
defineOptions({
  name: "ConfiguracaoEstatisticaIndex",
});
</script>