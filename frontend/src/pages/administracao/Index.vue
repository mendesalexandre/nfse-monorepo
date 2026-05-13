<template>
  <q-page padding>
    <!-- Header -->
    <div class="admin-header">
      <div>
        <h1 class="admin-title">Administração</h1>
        <p class="admin-subtitle">Gerencie as configurações e módulos do sistema</p>
      </div>
    </div>

    <!-- Seções -->
    <div v-for="(secao, idx) in menus" :key="idx" class="admin-section">
      <div class="admin-section-header">
        <span class="admin-section-label">{{ secao.title }}</span>
        <span class="admin-section-count">{{ secao.children.length }}</span>
      </div>

      <div class="admin-grid">
        <router-link
          v-for="(item, index) in secao.children"
          :key="index"
          :to="item.to"
          class="admin-card"
        >
          <q-icon :name="item.icon" size="20px" class="admin-card__icon" />
          <span class="admin-card__label">{{ item.label }}</span>
          <q-icon name="fa-light fa-chevron-right" size="14px" class="admin-card__arrow" />
        </router-link>
      </div>
    </div>
  </q-page>
</template>

<script setup>
defineOptions({ name: 'AdministracaoIndex' })

const menus = [
  {
    title: 'Sistema',
    children: [
      { label: 'Usuários',   icon: 'fa-light fa-users',       to: { name: 'administracao' } },
      { label: 'Permissões', icon: 'fa-light fa-shield',      to: { name: 'administracao' } },
      { label: 'Grupos',     icon: 'fa-light fa-users-gear',  to: { name: 'administracao' } },
      { label: 'Auditoria',  icon: 'fa-light fa-clipboard-check', to: { name: 'administracao' } },
    ],
  },
  {
    title: 'Financeiro',
    children: [
      { label: 'Formas de Pagamento', icon: 'fa-light fa-credit-card',      to: { name: 'administracao' } },
      { label: 'Categorias',          icon: 'fa-light fa-tag',              to: { name: 'administracao' } },
      { label: 'Caixas',              icon: 'fa-light fa-cash-register',    to: { name: 'administracao' } },
    ],
  },
  {
    title: 'Localidades',
    children: [
      { label: 'Estados', icon: 'fa-light fa-map',     to: { name: 'administracao' } },
      { label: 'Cidades', icon: 'fa-light fa-map-pin', to: { name: 'administracao' } },
    ],
  },
]
</script>

<style lang="scss" scoped>
.admin-header {
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 1.5rem;
}

.admin-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  line-height: 1.3;
}

.admin-subtitle {
  font-size: 0.875rem;
  color: #64748b;
  margin: 4px 0 0 0;
}

.admin-section {
  margin-bottom: 2rem;
}

.admin-section-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f1f5f9;
}

.admin-section-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
}

.admin-section-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border-radius: 9999px;
  background-color: #f1f5f9;
  font-size: 0.6875rem;
  font-weight: 600;
  color: #94a3b8;
}

.admin-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;

  @media (max-width: 1200px) { grid-template-columns: repeat(3, 1fr); }
  @media (max-width: 768px)  { grid-template-columns: repeat(2, 1fr); }
  @media (max-width: 480px)  { grid-template-columns: 1fr; }
}

.admin-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  cursor: pointer;
  text-decoration: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;

  &:hover {
    border-color: var(--q-primary);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);

    .admin-card__arrow { opacity: 1; color: var(--q-primary); }
    .admin-card__icon  { color: var(--q-primary); }
  }

  &__icon  { color: #64748b; flex-shrink: 0; transition: color 0.15s ease; }
  &__label { flex: 1; font-size: 0.875rem; font-weight: 500; color: #0f172a; }
  &__arrow { color: #94a3b8; opacity: 0; transition: opacity 0.15s ease, color 0.15s ease; flex-shrink: 0; }
}
</style>
