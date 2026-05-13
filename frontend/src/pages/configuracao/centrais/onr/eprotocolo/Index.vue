<template>
  <q-card bordered>
    <q-card-section><label class="text-uppercase">ONR - Operador Nacional do Sistema de Registro Eletrônico de
        Imóveis</label></q-card-section>
  </q-card>

  <q-card>
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-6 col-sm-12 col-xs-12">
          <label for="">Cliente ID</label>
          <v-label label="Chave WS da ONR" :obrigatorio="true" :ajuda="'Chave WS da ONR'"></v-label>
          <q-input outlined dense />
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">
          <v-label label="Chave WS da ONR" :obrigatorio="true" :ajuda="'Chave WS da ONR'"></v-label>
          <q-input outlined dense />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>