<template>
  <modal-protocolo
    v-model="model"
    :titulo="titulo"
    :largura="80"
    :altura="70"
    @close="fecharModal"
  >
    <template v-slot:menu>
      <div class="q-ml-sm">
        <q-btn
          icon="fa-solid fa-pen-to-square"
          flat
          unelevated
          round
          @click="onEditar"
          size="sm"
        />
        <q-btn
          icon="fa-solid fa-pen-to-square"
          flat
          unelevated
          round
          @click="onEditar"
          size="sm"
        />
      </div>
      <q-tabs
        dense
        v-model="tab"
        active-class="text-warning"
        align="left"
        class="q-ml-lg"
      >
        <q-tab name="geral" label="Informações Gerais" />
        <q-tab name="ato_registro" label="Atos & Registros" />
        <q-tab name="financeiro" label="Financeiro" />
      </q-tabs>
    </template>

    <q-tab-panels v-model="tab" keep-alive class="bg-grey-2">
      <q-tab-panel name="geral" class="no-padding">
        <protocolo-geral />
      </q-tab-panel>

      <q-tab-panel name="ato_registro" class="no-padding">
        <protocolo-ato></protocolo-ato>
      </q-tab-panel>

      <q-tab-panel name="financeiro" class="no-padding">
        <protocolo-financeiro />
      </q-tab-panel>
      <!--
      <q-tab-panel name="partes">
        <Parte />
      </q-tab-panel> -->
    </q-tab-panels>

    <template v-slot:rodape>
      <q-card-section class="flex justify-end">
        <div class="q-gutter-md">
          <q-btn
            label="Cancelar"
            color="negative"
            @click="model = false"
            outline
            icon="close"
            class="shadcn-btn"
          />
          <q-btn
            label="Salvar"
            class="shadcn-btn"
            color="blue-8"
            icon="check"
            @click="salvar"
          />
        </div>
      </q-card-section>
    </template>
  </modal-protocolo>
</template>
<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useQuasar } from "quasar";
import { storeToRefs } from "pinia";
import { useProtocoloStore } from "src/stores/protocolo";
import ProtocoloGeral from "src/pages/protocolo/geral/Index.vue";
import ProtocoloAto from "src/pages/protocolo/atos/Index.vue";
import ProtocoloFinanceiro from "src/pages/protocolo/financeiro/Index.vue";

defineOptions({
  name: "ProtocoloIndex",
});
const $q = useQuasar();
const model = defineModel({ default: false });

const tab = ref("geral");
const $router = useRouter();
const $route = useRoute();

const protocoloStore = useProtocoloStore();
const { protocolo } = storeToRefs(protocoloStore);

const titulo = computed(() =>
  protocolo.value?.id
    ? `Protocolo ${protocolo.value.numero_protocolo_formatado}`
    : "Novo Protocolo"
);

const salvar = async () => {
  const response = await protocoloStore.update(
    protocolo.value.uuid,
    protocolo.value
  );
  if (response) {
    $q.notify({
      message: "Protocolo salvo com sucesso!",
      color: "positive",
      position: "top",
      timeout: 2000,
    });
    model.value = false;
  }
};

const fecharModal = () => {
  tab.value = "geral";
  model.value = !model.value;
};
</script>
