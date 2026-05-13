<template>
  <q-card-section> Configuração de e-mail </q-card-section>
  <q-separator />
  <q-card-section>
    <div class="row q-col-gutter-sm">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <label for="">Nome: </label>
        <q-input label="E-mail" outlined dense />
      </div>
      <div class="col-md-12 col-sm-12 col-xs-12">
        <label for="">Porta: </label>
        <q-input outlined dense />
      </div>
      <div class="col-md-12 col-sm-12 col-xs-12">
        <label for="">Host: </label>
        <q-input outlined dense />
      </div>
      <div class="col-md-12 col-sm-12 col-xs-12">
        <q-editor v-model="email.content" outlined dense />
      </div>
    </div>
  </q-card-section>
</template>

<script setup>
import { storeToRefs } from "pinia";
// import { useSettingStore } from "src/stores/settings";
defineOptions({
  name: "SettingsEmailIndex",
});

// const settingStore = useSettingStore();
// const { email } = storeToRefs(settingStore);
</script>

<style>
</style>
