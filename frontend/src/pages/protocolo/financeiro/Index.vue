<template>
  <q-page class="financial-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">Gestão Financeira</h1>
    </div>

    <!-- Cards Financeiros -->
    <div class="financial-cards">
      <!-- Despesa -->
      <div class="financial-card card-despesa" @click="navigateTo('despesa')">
        <div class="card-icon">
          <q-icon name="eva-trending-down-outline" size="32px" />
        </div>
        <div class="card-label">DESPESA</div>
      </div>

      <!-- Receita -->
      <div class="financial-card card-receita" @click="navigateTo('receita')">
        <div class="card-icon">
          <q-icon name="eva-trending-up-outline" size="32px" />
        </div>
        <div class="card-label">RECEITA</div>
      </div>

      <!-- Transferências -->
      <div
        class="financial-card card-transferencia"
        @click="navigateTo('transferencia')"
      >
        <div class="card-icon">
          <q-icon name="eva-swap-outline" size="32px" />
        </div>
        <div class="card-label">TRANSF.</div>
      </div>

      <!-- Cheques -->
      <div class="financial-card card-cheques" @click="navigateTo('cheques')">
        <div class="card-icon">
          <q-icon name="eva-credit-card-outline" size="32px" />
        </div>
        <div class="card-label">CHEQUES</div>
      </div>

      <!-- Extrato Geral -->
      <div class="financial-card card-extrato" @click="navigateTo('extrato')">
        <div class="card-icon">
          <q-icon name="eva-file-text-outline" size="32px" />
        </div>
        <div class="card-label">EXTRATO GERAL</div>
      </div>

      <!-- Notas Fiscais -->
      <div
        class="financial-card card-notas"
        @click="navigateTo('notas-fiscais')"
      >
        <div class="card-icon">
          <q-icon name="eva-archive-outline" size="32px" />
        </div>
        <div class="card-label">NOTAS FISCAIS</div>
      </div>

      <!-- Caixas Inativos -->
      <div
        class="financial-card card-caixas"
        @click="navigateTo('caixas-inativos')"
      >
        <div class="card-icon">
          <q-icon name="eva-cube-outline" size="32px" />
        </div>
        <div class="card-label">CAIXAS INATIVOS</div>
      </div>

      <!-- Valores a Receber/Devolver -->
      <div class="financial-card card-valores" @click="navigateTo('valores')">
        <div class="card-icon">
          <q-icon name="eva-refresh-outline" size="32px" />
        </div>
        <div class="card-label">VALORES A RECEBER/DEVOLVER</div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { useRouter } from "vue-router";

const $router = useRouter();

const navigateTo = (module) => {
  console.log(`Navegando para: ${module}`);
  // Implementar navegação específica para cada módulo
  // $router.push({ name: `financeiro.${module}` });
};
</script>

<style lang="scss" scoped>
.financial-dashboard {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  min-height: 100vh;
  padding: 0;
}

// Header
.dashboard-header {
  padding: 20px 24px;
  background: rgba(0, 0, 0, 0.3);
}

.dashboard-title {
  color: #ffffff;
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

// Cards Container
.financial-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 16px;
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

// Card Base
.financial-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 24px 16px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  position: relative;
  overflow: hidden;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 12px;

  &::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    transition: height 0.3s ease;
  }

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);

    &::before {
      height: 100%;
      opacity: 0.1;
    }

    .card-icon {
      transform: scale(1.1);
    }
  }
}

// Ícones
.card-icon {
  color: #ffffff;
  transition: transform 0.3s ease;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

// Labels
.card-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  line-height: 1.2;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

// Cores específicas para cada card
.card-despesa {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);

  &::before {
    background: #e74c3c;
  }
}

.card-receita {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);

  &::before {
    background: #27ae60;
  }
}

.card-transferencia {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);

  &::before {
    background: #3498db;
  }
}

.card-cheques {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);

  &::before {
    background: #f39c12;
  }
}

.card-extrato {
  background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);

  &::before {
    background: #e67e22;
  }
}

.card-notas {
  background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);

  &::before {
    background: #9b59b6;
  }
}

.card-caixas {
  background: linear-gradient(135deg, #795548 0%, #5d4037 100%);

  &::before {
    background: #795548;
  }
}

.card-valores {
  background: linear-gradient(135deg, #673ab7 0%, #512da8 100%);

  &::before {
    background: #673ab7;
  }
}

// Responsividade
@media (max-width: 768px) {
  .financial-cards {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
    padding: 16px;
  }

  .financial-card {
    padding: 16px 12px;
    min-height: 100px;
    gap: 8px;
  }

  .card-icon {
    .q-icon {
      font-size: 24px !important;
    }
  }

  .card-label {
    font-size: 0.7rem;
  }

  .dashboard-header {
    padding: 16px 20px;
  }

  .dashboard-title {
    font-size: 1.25rem;
  }
}

@media (max-width: 480px) {
  .financial-cards {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    padding: 12px;
  }

  .financial-card {
    padding: 12px 8px;
    min-height: 90px;
    gap: 6px;
  }

  .card-icon {
    .q-icon {
      font-size: 20px !important;
    }
  }

  .card-label {
    font-size: 0.65rem;
  }
}

// Animação de entrada
.financial-card {
  animation: slideInUp 0.6s ease-out;
  animation-fill-mode: both;
}

@for $i from 1 through 8 {
  .financial-card:nth-child(#{$i}) {
    animation-delay: #{$i * 0.1}s;
  }
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
