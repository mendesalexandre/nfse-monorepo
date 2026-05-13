<template>
  <div>
    <q-card bordered>
      <q-card-section> ONR - Dados para emissão de Certidão </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="row q-col-gutter-sm">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <v-label label="ID Parceiro WS da ONR" :obrigatorio="true" :ajuda="'ID Parceiro WS da ONR'"></v-label>
            <q-input outlined dense />
          </div>
          <div class="col-md-12 col-sm-12 col-xs-12">
            <v-label label="Chave WS da ONR" :obrigatorio="true" :ajuda="'Chave WS da ONR'"></v-label>
            <q-input outlined dense />
          </div>

          <div class="col-md-12 col-sm-12 col-xs-12">
            <v-label label="Chave do Assinador Web ONR" :obrigatorio="true"
              :ajuda="'Chave do Assinador Web ONR'"></v-label>
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
  </div>
</template>

<script setup></script>