<template>
  <q-card bordered class>
    <q-card-section> Informações do Financeiro </q-card-section>
    <q-separator />
  </q-card>

  <q-card bordered class="q-mt-sm">
    <q-card-section> Conta para Recebimento de <b>PIX</b> </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Nome do Caixa</label>
          <q-select
            v-model="caixa.receber_cartao_credito_id"
            outlined
            :options="contasCaixa"
            option-value="idcontacaixa"
            :option-label="
              (option) => `${option.idcontacaixa} - ${option.descricao}`
            "
          />
        </div>

        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Prazo para pagamento do QRCode (em minutos)</label>
          <q-input outlined />
        </div>
      </div>
    </q-card-section>
  </q-card>

  <q-card bordered class="q-mt-sm">
    <q-card-section> Conta para Recebimento de <b>Boleto</b> </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <q-select
            v-model="caixa.receber_cartao_credito_id"
            outlined
            :options="contasCaixa"
            option-value="idcontacaixa"
            :option-label="
              (option) => `${option.idcontacaixa} - ${option.descricao}`
            "
          />
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Prazo para pagamento do Boleto (em dias)</label>
          <q-input outlined />
        </div>
      </div>
    </q-card-section>
  </q-card>

  <q-card bordered class="q-mt-sm">
    <q-card-section> Conta para Recebimento de <b>Dinheiro</b> </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Conta/Caixa para recebimento em <b>Dinheiro</b></label>
          <q-select
            v-model="caixa.receber_cartao_credito_id"
            outlined
            :options="contasCaixa"
            option-value="idcontacaixa"
            :option-label="
              (option) => `${option.idcontacaixa} - ${option.descricao}`
            "
          />
        </div>
      </div>
    </q-card-section>
  </q-card>

  <!-- PARCELA EXPRESS -->
  <q-card bordered class="q-mt-sm">
    <q-card-section>Parcela Express</q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Ativar Recibemento por Parcela Express</label>
          <v-input-check></v-input-check>
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Conta para Recebimento para Cartão de Crédito </label>
          <q-select
            v-model="caixa.receber_cartao_credito_id"
            outlined
            :options="contasCaixa"
            option-value="idcontacaixa"
            :option-label="
              (option) => `${option.idcontacaixa} - ${option.descricao}`
            "
          />
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
          <label for="">Conta para Recebimento para Cartão de Débito </label>
          <q-select outlined />
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { useQuasar } from "quasar";
import { useIntegradoContaCaixaStore } from "src/stores/integrado/conta-caixa";
import { onMounted, ref } from "vue";

defineOptions({
  name: "ConfiguracaoFinanceiro",
});

const $q = useQuasar();
const integradoContaCaixa = useIntegradoContaCaixaStore();
const { contasCaixa } = storeToRefs(integradoContaCaixa);

const caixa = ref({});
onMounted(async () => {
  $q.loading.show();
  try {
    const response = await integradoContaCaixa.index();
  } catch (e) {
    console.error(e);
    $q.notify({
      type: "negative",
      message: e?.message,
    });
  } finally {
    $q.loading.hide();
  }
});
</script>
