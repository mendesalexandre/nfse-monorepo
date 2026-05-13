<template>
  <div class="v-data-table">
    <!-- Search bar -->
    <div v-if="searchable" class="v-data-table__search q-mb-sm">
      <q-input
        v-model="search"
        dense
        outlined
        :placeholder="searchPlaceholder"
        clearable
      >
        <template #prepend>
          <q-icon name="fa-light fa-magnifying-glass" size="14px" />
        </template>
      </q-input>
    </div>

    <q-table
      v-bind="$attrs"
      :rows="filteredRows"
      :columns="columns"
      :loading="loading"
      :rows-per-page-options="rowsPerPageOptions"
      :pagination="pagination"
      flat
      bordered
      @update:pagination="$emit('update:pagination', $event)"
    >
      <!-- Empty state -->
      <template #no-data>
        <v-empty-state
          :mensagem="emptyMessage"
          :icon="emptyIcon"
        />
      </template>

      <!-- Loading -->
      <template #loading>
        <q-inner-loading showing>
          <q-spinner-dots size="40px" :color="spinnerColor" />
        </q-inner-loading>
      </template>

      <!-- Pass through all slots -->
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot :name="name" v-bind="slotData || {}" />
      </template>
    </q-table>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

defineOptions({ name: 'VDataTable', inheritAttrs: false })

const props = defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
  columns: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: false,
  },
  searchPlaceholder: {
    type: String,
    default: 'Buscar...',
  },
  searchFields: {
    type: Array,
    default: () => [],
  },
  emptyMessage: {
    type: String,
    default: 'Nenhum registro encontrado',
  },
  emptyIcon: {
    type: String,
    default: 'fa-light fa-inbox',
  },
  spinnerColor: {
    type: String,
    default: 'primary',
  },
  rowsPerPageOptions: {
    type: Array,
    default: () => [10, 25, 50],
  },
  pagination: {
    type: Object,
    default: () => ({ rowsPerPage: 10 }),
  },
})

defineEmits(['update:pagination'])

const search = ref('')

const filteredRows = computed(() => {
  if (!search.value || !props.searchable) return props.rows

  const term = search.value.toLowerCase()
  const fields = props.searchFields.length
    ? props.searchFields
    : props.columns.map((c) => c.field).filter(Boolean)

  return props.rows.filter((row) =>
    fields.some((field) => {
      const val = row[field]
      return val != null && String(val).toLowerCase().includes(term)
    })
  )
})
</script>

<style scoped>
.v-data-table__search {
  max-width: 320px;
}
</style>
