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
              <q-icon name="fa-light fa-shield-check" size="20px" />
              <div>
                <h3>Segurança</h3>
                <p>Proteção de dados com criptografia</p>
              </div>
            </div>

            <div class="feature-item">
              <q-icon name="fa-light fa-chart-mixed" size="20px" />
              <div>
                <h3>Relatórios</h3>
                <p>Insights e análises em tempo real</p>
              </div>
            </div>

            <div class="feature-item">
              <q-icon name="fa-light fa-users" size="20px" />
              <div>
                <h3>Multiusuário</h3>
                <p>Controle de usuários e permissões</p>
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
            <h2>Bem-vindo de volta</h2>
            <p>Acesse sua conta para continuar</p>
          </div>

          <!-- Formulário -->
          <q-form @submit="handleLogin" class="login-form">
            <!-- Group: credenciais -->
            <div class="login-group">
              <div>
                <v-label label="E-mail" obrigatorio />
                <q-input
                  v-model="form.email"
                  type="email"
                  outlined
                  dense
                  no-error-icon
                  placeholder="seu@email.com"
                  :class="{ 'input-valid': emailValido }"
                  :rules="[val => !!val || 'E-mail é obrigatório']"
                >
                  <template v-slot:append>
                    <q-icon
                      v-if="emailValido"
                      name="fa-light fa-circle-check"
                      class="input-valid-icon"
                    />
                  </template>
                </q-input>
              </div>

              <div>
                <v-label label="Senha" obrigatorio />
                <v-password v-model="form.password" placeholder="Digite sua senha"
                  no-error-icon :rules="[val => !!val || 'Senha é obrigatória']" />
              </div>

              <div class="row items-center justify-between">
                <q-checkbox v-model="form.remember" label="Lembrar de mim" dense />
                <a href="#" class="forgot-link" @click.prevent="navigateToForgotPassword">Esqueci minha senha</a>
              </div>
            </div>

            <!-- Action -->
            <div class="login-group">
              <q-btn
                type="submit"
                unelevated
                color="primary"
                class="full-width login-btn"
                :loading="isLoading"
              >
                Entrar
                <q-icon name="fa-light fa-arrow-right" size="16px" class="q-ml-sm" />
              </q-btn>
            </div>
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

          <!-- Registro -->
          <p class="register-text">
            Não tem uma conta?
            <a href="#" class="register-link" @click.prevent="navigateToRegister">Criar conta</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "src/stores/auth";
import { useNotify } from "src/composables/useNotify";

defineOptions({ name: "LoginPage" });

const router = useRouter();
const auth = useAuthStore();
const { success, error: notifyError } = useNotify();

const form = ref({
  email: "",
  password: "",
  remember: false,
});

const isLoading = ref(false);

const emailValido = computed(() => {
  const email = form.value.email;
  if (!email) return false;
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
});

const handleLogin = async () => {
  isLoading.value = true;
  try {
    await auth.login({
      email: form.value.email,
      senha: form.value.password,
      remember: form.value.remember,
    });

    success("Login realizado com sucesso!");
    router.push({ name: "home" });
  } catch (err) {
    const msg = err?.response?.data?.message || "E-mail ou senha incorretos";
    notifyError(msg);
  } finally {
    isLoading.value = false;
  }
};

const navigateToForgotPassword = () => {
  router.push({ name: "esqueci-senha" });
};

const navigateToRegister = () => {
  router.push({ name: "criar-conta" });
};

const loginSocial = (provider) => {
  console.log(`Login com ${provider} em desenvolvimento`);
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
  background: var(--q-primary);
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
    color: var(--q-dark);
    margin: 0 0 0.375rem;
  }

  p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
  }
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 0;
}

// === Group dividers (como na imagem) ===
.login-group {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px 0;
  border-top: 2px solid rgba(var(--q-primary-rgb, 79, 70, 229), 0.08);

  &:first-child {
    padding-top: 0;
    border-top: none;
  }
}

// === Input válido (borda verde + ícone check) ===
.input-valid {
  :deep(.q-field__control) {
    border-color: var(--q-positive) !important;
    background-color: rgba(22, 163, 74, 0.03);

    &::before {
      border-color: var(--q-positive) !important;
      border-width: 2px;
    }

    &:hover::before {
      border-color: var(--q-positive) !important;
    }
  }

  :deep(.q-field__control.q-field__control--focused) {
    &::before {
      border-color: var(--q-positive) !important;
    }
  }
}

.input-valid-icon {
  color: var(--q-positive);
  font-size: 16px;
}

// === Botão login com seta ===
.login-btn {
  font-weight: 500;
  letter-spacing: 0.3px;
}

// === Links ===
.forgot-link {
  font-size: 13px;
  color: var(--q-primary);
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

// === Registro ===
.register-text {
  text-align: center;
  font-size: 13px;
  color: #64748b;
  margin-top: 1.5rem;
}

.register-link {
  color: var(--q-primary);
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
