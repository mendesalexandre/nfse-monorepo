<template>
  <div class="login-container">
    <div class="row no-wrap full-height">
      <!-- Coluna esquerda — branding -->
      <div class="col-md-7 col-sm-12 login-left gt-sm">
        <div class="left-content">
          <div class="brand">
            <q-icon name="fa-light fa-cube" size="40px" color="white" />
            <h1 class="brand-title">Meu Projeto</h1>
            <p class="brand-subtitle">Plataforma completa para gestão</p>
          </div>

          <div class="features">
            <div class="feature-item">
              <q-icon name="fa-light fa-rocket" size="20px" />
              <div>
                <h3>Rápido</h3>
                <p>Comece a usar em poucos minutos</p>
              </div>
            </div>

            <div class="feature-item">
              <q-icon name="fa-light fa-shield-check" size="20px" />
              <div>
                <h3>Seguro</h3>
                <p>Seus dados protegidos com criptografia</p>
              </div>
            </div>

            <div class="feature-item">
              <q-icon name="fa-light fa-headset" size="20px" />
              <div>
                <h3>Suporte</h3>
                <p>Equipe disponível para ajudar</p>
              </div>
            </div>
          </div>

          <p class="footer-text">© {{ new Date().getFullYear() }} Meu Projeto. Todos os direitos reservados.</p>
        </div>
      </div>

      <!-- Coluna direita — formulário -->
      <div class="col-md-5 col-sm-12 login-right">
        <div class="login-form-wrapper">
          <!-- Header -->
          <div class="login-header">
            <q-icon name="fa-light fa-cube" size="32px" color="primary" class="lt-md q-mb-md" />
            <h2>Crie sua conta</h2>
            <p>Preencha os dados para começar</p>
          </div>

          <!-- Formulário -->
          <q-form @submit="handleRegister" class="register-form">
            <div>
              <v-label label="Nome completo" obrigatorio />
              <q-input v-model="form.nome" outlined dense placeholder="Seu nome"
                :rules="[val => !!val || 'Nome é obrigatório']" />
            </div>

            <div>
              <v-label label="E-mail" obrigatorio />
              <q-input v-model="form.email" type="email" outlined dense placeholder="seu@email.com"
                :rules="[val => !!val || 'E-mail é obrigatório']" />
            </div>

            <div>
              <v-label label="Senha" obrigatorio />
              <v-password v-model="form.password" placeholder="Mínimo 8 caracteres"
                :rules="[val => !!val || 'Senha é obrigatória', val => val.length >= 8 || 'Mínimo 8 caracteres']" />
            </div>

            <div>
              <v-label label="Confirmar senha" obrigatorio />
              <v-password v-model="form.passwordConfirm" placeholder="Repita a senha"
                :rules="[val => !!val || 'Confirmação é obrigatória', val => val === form.password || 'Senhas não conferem']" />
            </div>

            <q-checkbox v-model="form.termos" dense>
              <template #default>
                <span class="termos-text">
                  Li e aceito os <a href="#" class="termos-link" @click.stop.prevent>termos de uso</a>
                  e <a href="#" class="termos-link" @click.stop.prevent>política de privacidade</a>
                </span>
              </template>
            </q-checkbox>

            <q-btn type="submit" label="Criar conta" color="primary" unelevated class="full-width"
              :loading="isLoading" :disable="!form.termos" />
          </q-form>

          <!-- Divider -->
          <div class="divider">
            <span>ou continue com</span>
          </div>

          <!-- Social -->
          <div class="row q-gutter-sm">
            <q-btn outline class="col" color="grey-7" @click="loginSocial('Google')">
              <q-icon name="fa-brands fa-google" size="14px" class="q-mr-sm" />
              Google
            </q-btn>
            <q-btn outline class="col" color="grey-7" @click="loginSocial('Microsoft')">
              <q-icon name="fa-brands fa-microsoft" size="14px" class="q-mr-sm" />
              Microsoft
            </q-btn>
          </div>

          <!-- Login -->
          <p class="register-text">
            Já tem uma conta?
            <a href="#" class="register-link" @click.prevent="router.push({ name: 'login' })">Entrar</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "src/stores/auth";
import { useNotify } from "src/composables/useNotify";

defineOptions({ name: "CriarContaPage" });

const router = useRouter();
const auth = useAuthStore();
const { success, error: notifyError } = useNotify();

const form = ref({
  nome: "",
  email: "",
  password: "",
  passwordConfirm: "",
  termos: false,
});

const isLoading = ref(false);

const handleRegister = async () => {
  isLoading.value = true;
  try {
    await auth.register({
      nome: form.value.nome,
      email: form.value.email,
      senha: form.value.password,
      senha_confirmation: form.value.passwordConfirm,
    });

    success("Conta criada com sucesso!");
    router.push({ name: "home" });
  } catch (err) {
    const msg = err?.response?.data?.message || "Erro ao criar conta. Tente novamente.";
    notifyError(msg);
  } finally {
    isLoading.value = false;
  }
};

const loginSocial = (provider) => {
  $q.notify({
    type: "info",
    message: `Cadastro com ${provider} em desenvolvimento`,
    position: "top-right",
    timeout: 2000,
  });
};
</script>

<style lang="scss" scoped>
.login-container {
  height: 100vh;
  width: 100%;
  overflow: hidden;
}

// === Esquerda ===
.login-left {
  background: linear-gradient(135deg, #312e81 0%, #4f46e5 50%, #6366f1 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.left-content {
  max-width: 420px;
  padding: 3rem 2rem;
}

.brand-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 1rem 0 0.5rem;
  letter-spacing: 0.5px;
}

.brand-subtitle {
  font-size: 0.9375rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
}

.features {
  margin-top: 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;

  h3 {
    font-size: 0.9375rem;
    font-weight: 500;
    margin: 0 0 2px;
  }

  p {
    font-size: 0.8125rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
  }
}

.footer-text {
  margin-top: 3rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.4);
}

// === Direita ===
.login-right {
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  overflow-y: auto;
}

.login-form-wrapper {
  width: 100%;
  max-width: 380px;
}

.login-header {
  margin-bottom: 2rem;

  h2 {
    font-size: 1.375rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0 0 0.375rem;
  }

  p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
  }
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.termos-text {
  font-size: 13px;
  color: #64748b;
}

.termos-link {
  color: #4f46e5;
  text-decoration: none;

  &:hover {
    text-decoration: underline;
  }
}

// === Divider ===
.divider {
  position: relative;
  text-align: center;
  margin: 1.5rem 0;

  &::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e2e8f0;
  }

  span {
    background: #fff;
    color: #94a3b8;
    padding: 0 0.75rem;
    font-size: 13px;
    position: relative;
  }
}

// === Login link ===
.register-text {
  text-align: center;
  font-size: 13px;
  color: #64748b;
  margin-top: 1.5rem;
}

.register-link {
  color: #4f46e5;
  text-decoration: none;
  font-weight: 500;

  &:hover {
    text-decoration: underline;
  }
}

// === Responsivo ===
@media (max-width: 1023px) {
  .login-right {
    padding: 1.5rem;
  }
}

@media (max-width: 599px) {
  .login-container {
    height: auto;
    min-height: 100vh;
  }

  .login-right {
    min-height: 100vh;
  }
}
</style>
