<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <q-icon name="fa-light fa-lock-keyhole" size="40px" color="primary" />
        <h2>Esqueceu sua senha?</h2>
        <p>Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>
      </div>

      <q-form @submit="handleSubmit" class="forgot-form">
        <div>
          <v-label label="E-mail" obrigatorio />
          <q-input v-model="form.email" type="email" outlined dense placeholder="seu@email.com"
            :rules="[val => !!val || 'E-mail é obrigatório']" />
        </div>

        <q-btn type="submit" label="Enviar link de recuperação" color="primary" unelevated class="full-width"
          :loading="isLoading" />
      </q-form>

      <p class="voltar-text">
        <a href="#" class="voltar-link" @click.prevent="router.push({ name: 'login' })">
          <q-icon name="fa-light fa-arrow-left" size="12px" class="q-mr-xs" />
          Voltar para o login
        </a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "src/stores/auth";
import { useNotify } from "src/composables/useNotify";

defineOptions({ name: "EsqueciSenhaPage" });

const router = useRouter();
const auth = useAuthStore();
const { success, error: notifyError } = useNotify();

const form = ref({ email: "" });
const isLoading = ref(false);

const handleSubmit = async () => {
  isLoading.value = true;
  try {
    await auth.forgotPassword(form.value.email);
    success("Link de recuperação enviado para seu e-mail!");
    router.push({ name: "login" });
  } catch (err) {
    const msg = err?.response?.data?.message || "Erro ao enviar e-mail. Tente novamente.";
    notifyError(msg);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style lang="scss" scoped>
.auth-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  padding: 1.5rem;
}

.auth-card {
  width: 100%;
  max-width: 400px;
  background: #fff;
  border-radius: 12px;
  padding: 2.5rem 2rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;

  h2 {
    font-size: 1.375rem;
    font-weight: 600;
    color: #0f172a;
    margin: 1rem 0 0.5rem;
  }

  p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
  }
}

.forgot-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.voltar-text {
  text-align: center;
  margin-top: 1.5rem;
  margin-bottom: 0;
}

.voltar-link {
  font-size: 13px;
  color: #4f46e5;
  text-decoration: none;
  font-weight: 500;

  &:hover {
    text-decoration: underline;
  }
}
</style>
