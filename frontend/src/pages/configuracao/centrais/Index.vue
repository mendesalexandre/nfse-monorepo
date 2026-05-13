<template>
  <q-card bordered>
    <q-card-section><label for="">ONR - Operador Nacional do Sistema de Registro Eletrônico de
        Imóveis</label></q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-6 col-sm-12 col-xs-12">
          <label for="">Cliente ID</label>
          <q-input outline />
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">
          <label for="">Cliente Secret</label>
          <q-input outline />
        </div>
      </div>
    </q-card-section>
  </q-card>

  <q-card bordered class="q-mt-md">
    <q-card-section><label for="">
        CEI - Central Eletrônica de Integração e Informações dos Serviços
        Notariais e Registrais do Estado de Mato Grosso</label></q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-6 col-sm-12 col-xs-12">
          <label for="">Usuário (E-mail)</label>
          <q-input outline />
        </div>

        <div class="col-md-6 col-sm-12 col-xs-12">
          <label for="">Senha</label>
          <q-input outline type="password" />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>
<script setup>
import { storeToRefs } from "pinia";
import { useQuasar } from "quasar";
// import { useIntegradoContaCaixaStore } from "src/stores/integrado/conta-caixa";
import { onMounted } from "vue";

defineOptions({
  name: "ConfiguracaoCentraisIndex",
});

const $q = useQuasar();
</script>