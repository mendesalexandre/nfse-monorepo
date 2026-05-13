<template>
  <q-card bordered>
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-6 col-sm-12 col-xs-12">
          <v-label label="Estado" obrigatorio />
          <q-select outline :options="estados" emit-value map-options option-label="nome" option-value="id"
            v-model="configuracao.estado_id" outlined dense />
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <v-label label="Cidade" obrigatorio />
          <q-select outline v-model="configuracao.cidade_id" outlined dense />
        </div>
      </div>
    </q-card-section>
  </q-card>

  <q-card bordered class="q-mt-md">
    <q-card-section> Dados do Cartório </q-card-section>
    <q-separator />
    <q-card-section>
      <div class="row q-col-gutter-sm">
        <div class="col-md-10 col-sm-12 col-xs-12">
          <v-label label="Nome da Serventia (Vai ser usado nos Livros e Recibos)" obrigatorio />
          <q-input outline v-model="configuracao.nome" outlined dense />
        </div>
        <div class="col-md-2 col-sm-12 col-xs-12">
          <v-label label="CNS" obrigatorio />
          <q-input outline mask="##.###-#" unmasked-value fill-mask="__.___-_" v-model="configuracao.cns" outlined
            dense />
        </div>
        <div class="col-md-12 col-sm-12 col-xs-12">
          <v-label label="Nome do Oficial" obrigatorio />
          <!-- <q-select
            outline
            :options="oficiais"
            emit-value
            map-options
            option-label="nome"
            option-value="id"
            v-model="configuracao.oficial_id"
          /> -->
          <q-select v-model="configuracao.oficial_id" :options="oficiais" option-value="id" option-label="nome"
            clearable input-debounce="500" :loading="loading" outlined dense>
            <!-- <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    Nenhum cliente encontrado
                  </q-item-section>
                </q-item>
              </template> -->

            <template v-slot:selected-item="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section>
                  <q-item-label>
                    <!-- <q-badge
                        color="primary"
                        outline
                        :label="scope.opt.nome"
                      /> -->
                    <q-chip icon-remove="none">
                      {{ scope.opt.nome }}
                    </q-chip>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>

            <template v-slot:option="scope">
              <q-item v-bind="scope.itemProps">
                <!-- <q-item-section avatar>
                    <q-icon :name="scope.opt.icon" />
                  </q-item-section> -->
                <q-item-section>
                  <q-item-label>{{ scope.opt.nome }}</q-item-label>
                  <q-item-label caption>{{
                    formatarCpfCnpj(scope.opt.cpf)
                    }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>

            <template v-slot:after>
              <q-btn class="shadcn-btn" outline dense icon="add" />
            </template>
          </q-select>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { storeToRefs } from "pinia";
// import { useEstadoStore } from "src/stores/estado";
// import { useOficialStore } from "src/stores/oficial";
// import { formatarCpfCnpj } from "src/util";
import { onMounted, ref } from "vue";
defineOptions({
  name: "ConfiguracaoCartorioIndex",
});

onMounted(async () => {
  // Carregar os estados ao montar o componente
  // await estadoStore.index();
  // Carregar os oficiais ao montar o componente
  // await oficialStore.index();
});

// const estadoStore = useEstadoStore();
// const { estados } = storeToRefs(estadoStore);

// const oficialStore = useOficialStore();
// const { oficiais } = storeToRefs(oficialStore);

const configuracao = ref({
  estado_id: null,
  cidade_id: null,
  nome: null,
  cns: null,
  oficial_id: null,
});
</script>
