const emissoesRoutes = [
  {
    path: '',
    name: 'emissoes',
    component: () => import('pages/emissoes/Index.vue'),
  },
  {
    path: ':id',
    name: 'emissoes-detalhe',
    component: () => import('pages/emissoes/Detalhe.vue'),
  },
]

export default emissoesRoutes
