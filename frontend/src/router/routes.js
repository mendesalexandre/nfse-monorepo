import administracaoRoutes from "./administracao/index.js";

const routes = [
  {
    path: "/",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("pages/IndexPage.vue"),
        name: "home",
      },
      {
        path: "posts",
        component: () => import("pages/posts/Index.vue"),
        name: "posts",
      },
    ],
  },

  {
    path: "/administracao",
    component: () => import("layouts/MainLayout.vue"),
    children: administracaoRoutes,
  },

  {
    path: "/auth",
    component: () => import("layouts/AuthLayout.vue"),
    children: [
      {
        path: "login",
        component: () => import("pages/autenticacao/login/Index.vue"),
        name: "login",
      },
      {
        path: "esqueci-senha",
        component: () => import("pages/autenticacao/esqueci-senha/Index.vue"),
        name: "esqueci-senha",
      },
      {
        path: "criar-conta",
        component: () => import("pages/autenticacao/criar-conta/Index.vue"),
        name: "criar-conta",
      },
    ],
  },

  {
    path: "/erro/403",
    name: "erro-403",
    component: () => import("pages/Error403.vue"),
  },
  {
    path: "/erro/500",
    name: "erro-500",
    component: () => import("pages/Error500.vue"),
  },
  {
    path: "/:catchAll(.*)*",
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
