<template>
  <template>
    <q-dialog v-model="model" maximized data-allow-mismatch>
      <q-card>
        <q-layout>
          <q-header class="text-white no-padding" style="background-color: #b02b37">
            <q-toolbar class="h-full no-border" style="min-height: 100%">
              <div class="flex justify-start items-center w-full">
                <div class="text-uppercase text-h6 text-lg">
                  {{ titulo }}
                </div>
                <q-space />
                <div class="h-full">
                  <q-tabs v-model="tab" mobile-arrows no-caps active-class="text-warning">
                    <q-tab name="geral" label="Informações Gerais" />
                    <q-tab name="ato_registro" label="Atos & Registros" />
                    <q-tab name="financeiro" label="Financeiro" />
                    <!-- <q-tab
                    name="ordem_servico"
                    label="Ordem de Serviço"
                    class="text-dark text-bold"
                  /> -->
                    <!-- <q-tab name="partes" label="Partes no Documento" class="text-dark text-bold" /> -->
                  </q-tabs>
                </div>
              </div>
              <q-space />
              <div class="q-gutter-sm flex justify-center items-center">
                <q-btn icon="close" round dense unelevated @click="onCancelar" />
              </div>
            </q-toolbar>
          </q-header>

          <q-page-container class="q-pa-xl shadcn-page-container bg-slate-50">
            <q-page padding class="q-pa-xl">
              <!-- GERAL -->
              <!-- <Geral /> -->
              <div class="row q-col-gutter-md">
                <div class="col-md-8">
                  <div class="alert alert-warning" role="alert">
                    This is a danger alert with
                    <a href="#" class="alert-link">an example link</a>. Give it
                    a click if you like.
                  </div>
                  <div class="alert alert-danger" role="alert">
                    This is a danger alert with
                    <a href="#" class="alert-link">an example link</a>. Give it
                    a click if you like.
                  </div>

                  <q-card bordered>
                    <q-card-section class="text-sm text-slate-500">
                      <div class="row items-center">
                        <div>Dados Gerais</div>
                        <q-space />
                        <q-badge color="green-8" text-color="white" label="VIGENTE" outline
                          class="q-pa-sm bg-green-2 no-border" />
                      </div>
                    </q-card-section>
                    <!-- {{ protocolo }} -->
                    <q-separator />
                    <q-card-section class="no-padding">
                      <q-markup-table class="q-mt-sm">
                        <tr>
                          <td style="width: 50%">Serviço Principal</td>
                          <td class="text-left">
                            {{ protocolo?.natureza?.nome }}
                          </td>
                        </tr>
                        <tr>
                          <td style="width: 50%">Cadastro</td>
                          <td class="text-left">
                            {{ dataHora(protocolo?.data_cadastro) }}
                          </td>
                        </tr>
                        <tr>
                          <td style="width: 50%">Vencimento da Prenotação</td>
                          <td class="text-left">
                            {{ data(protocolo?.data_cadastro) }}
                          </td>
                        </tr>
                        <tr>
                          <td style="width: 50%">Solicitante</td>
                          <td class="text-left">
                            {{ protocolo?.cliente?.nome }}
                          </td>
                        </tr>

                        <tr>
                          <td style="width: 50%">Data Pedido</td>
                          <td class="text-left"></td>
                        </tr>
                        <tr>
                          <td style="width: 50%">Protocolo Livro 01</td>
                          <td class="text-left"></td>
                        </tr>

                        <tr>
                          <td style="width: 50%">Venc. Protocolo Livro 01</td>
                          <td class="text-left"></td>
                        </tr>
                      </q-markup-table>
                    </q-card-section>
                  </q-card>
                </div>
                <div class="col-md-4">
                  <!-- Processo atual -->
                  <q-card bordered>
                    <q-card-section>
                      <div class="row items-center">
                        <div>Processo Atual</div>
                        <q-space />
                        <div>
                          <q-btn flat label="Encaminhar" unelevated color="grey-8" />
                        </div>
                      </div>
                    </q-card-section>

                    <q-separator />
                    <q-card-section class="no-padding">
                      <q-markup-table flat>
                        <thead>
                          <tr>
                            <th>Parte</th>
                            <th>Qualificação</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </q-markup-table> </q-card-section></q-card>

                  <!-- PARTES NO DOCUMENTO -->
                  <q-card bordered class="q-mt-sm">
                    <q-card-section>Partes no Documento</q-card-section>
                    <q-separator />
                    <q-card-section class="no-padding">
                      <q-markup-table flat>
                        <thead>
                          <tr>
                            <th>Parte</th>
                            <th>Qualificação</th>
                          </tr>
                        </thead>
                        <tbody></tbody>
                      </q-markup-table> </q-card-section></q-card>

                  <!-- AÇÕES -->
                  <q-card class="q-mt-sm" bordered>
                    <q-card-section>Ações</q-card-section>
                    <q-separator />
                    <q-card-section class="">
                      <div class="q-gutter-xs">
                        <q-btn no-caps label="Voltar a contagem do vencimento"
                          class="full-width btn btn-outline-success btn-sm" />
                        <q-btn no-caps label="Inconformidades" class="full-width btn btn-outline-danger btn-sm" />

                        <q-btn no-caps label="Nova Notificação" class="full-width btn btn-outline-warning btn-sm" />

                        <q-btn no-caps label="Novo Ofício" class="full-width btn btn-outline-info btn-sm" />
                      </div>
                    </q-card-section></q-card>
                </div>
              </div>
            </q-page>
          </q-page-container>

          <q-footer class="bg-white text-dark" elevated>
            <q-card-section class="q-mx-sm">
              <div class="flex justify-end">
                <q-btn color="primary" label="Salvar" />
              </div>
            </q-card-section>
          </q-footer>
        </q-layout>
      </q-card>
    </q-dialog>
  </template>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useQuasar } from "quasar";
import { storeToRefs } from "pinia";
import { useProtocoloStore } from "src/stores/protocolo";
import { hideLoading, showLoading } from "src/composables/loading";
import { data, dataHora } from "src/Utils/DataHora";

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
  protocolo.value?.id ? `Protocolo ${protocolo.value.codigo}` : "Novo Protocolo"
);

const onCancelar = () => {
  model.value = !model.value;
};
</script>