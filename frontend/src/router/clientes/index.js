const clientesRoutes = [
  {
    path: '',
    name: 'clientes',
    component: () => import('pages/clientes/Index.vue'),
  },
  {
    path: 'criar',
    name: 'clientes-criar',
    component: () => import('pages/clientes/Form.vue'),
  },
  {
    path: ':id',
    name: 'clientes-detalhe',
    component: () => import('pages/clientes/Detalhe.vue'),
  },
  {
    path: ':id/editar',
    name: 'clientes-editar',
    component: () => import('pages/clientes/Form.vue'),
  },
]

export default clientesRoutes
