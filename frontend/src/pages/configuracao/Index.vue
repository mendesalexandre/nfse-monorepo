<template>
  <q-page padding>
    <!-- Header da página -->
    <q-card bordered class="q-mb-md">
      <q-card-section class="q-pa-none">
        <div class="page-header">
          <q-icon name="fa-duotone fa-gear" size="20px" class="q-mr-sm page-header-icon" />
          <span class="page-title">Configuração do Sistema</span>
        </div>
      </q-card-section>
    </q-card>

    <div class="row q-col-gutter-md">
      <!-- Menu lateral -->
      <div class="col-md-3 col-sm-12 col-xs-12">
        <q-card bordered>
          <q-card-section class="q-pa-none">
            <q-list class="config-menu">
              <q-item clickable v-ripple :to="{ name: 'configuracao.cartorio' }" active-class="config-item-active"
                class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-briefcase" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Cartório</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple to="/config/email" active-class="config-item-active" class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-envelope" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>E-mail & WhatsApp</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple to="/config/selo" active-class="config-item-active" class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-award" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Selo</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple to="/config/indisponibilidade" active-class="config-item-active"
                class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-clock" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Indisponibilidade</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple :to="{ name: 'configuracao.centrais' }" active-class="config-item-active"
                class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-globe" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Centrais</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple to="/config/atendimento" active-class="config-item-active" class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-phone" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Atendimento</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple :to="{ name: 'configuracao.financeiro' }" active-class="config-item-active"
                class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-credit-card" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Financeiro</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple :to="{ name: 'configuracao.estatistica' }" active-class="config-item-active"
                class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-chart-column" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>Portal de Estatísticas</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple :to="{ name: 'configuracao.onr.certidao.index' }"
                active-class="config-item-active" class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-file-lines" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>ONR - Certidão</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple :to="{ name: 'configuracao.centrais.onr.eprotocolo.index' }"
                active-class="config-item-active" class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-file-check" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>ONR - E-Protocolo</q-item-section>
              </q-item>
              <q-separator />

              <q-item clickable v-ripple :to="{ name: 'configuracao.doi' }" active-class="config-item-active"
                class="config-item">
                <q-item-section avatar>
                  <q-icon name="fa-duotone fa-file-signature" size="18px" class="config-icon" />
                </q-item-section>
                <q-item-section>DOI - Declaração sobre Operações Imobiliárias</q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Área de conteúdo dinâmica -->
      <div class="col-md-9 col-sm-12 col-xs-12">
        <router-view />
      </div>
    </div>
  </q-page>
</template>

<script setup>
// Nenhuma lógica complexa necessária
</script>

<style lang="scss" scoped>
// Header da página
.page-header {
  display: flex;
  align-items: center;
  padding: 16px;
  border-radius: 2px;
  background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);

  .page-header-icon {
    --fa-primary-color: #374151;
    --fa-secondary-color: #6b7280;
    --fa-secondary-opacity: 0.6;
  }

  .page-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
}

// Menu de configurações
.config-menu {
  .config-item {
    transition: all 0.3s ease;
    border-radius: 0;
    padding: 12px 16px;

    .config-icon {
      --fa-primary-color: #6b7280;
      --fa-secondary-color: #9ca3af;
      --fa-secondary-opacity: 0.6;
      transition: all 0.3s ease;
    }

    &:hover {
      background-color: #f8f9fa;

      .config-icon {
        --fa-primary-color: #374151;
        --fa-secondary-color: #6b7280;
      }
    }

    &.config-item-active {
      background-color: #dbeafe;

      .q-item-section {
        color: #1976d2;
        font-weight: 600;
      }

      .config-icon {
        --fa-primary-color: #1976d2;
        --fa-secondary-color: #3b82f6;
        --fa-secondary-opacity: 0.7;
      }
    }
  }

  .q-separator {
    background-color: #e5e7eb;
  }
}

// Responsividade
@media (max-width: 768px) {
  .page-header {
    padding: 12px 16px;

    .page-title {
      font-size: 0.9rem;
    }
  }

  .config-item {
    padding: 10px 12px !important;
  }
}

// Estilo adicional para melhor visual
.q-card {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
}

.q-page {
  background-color: #f9fafb;
}
</style>