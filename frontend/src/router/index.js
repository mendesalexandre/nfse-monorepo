import { defineRouter } from "#q-app/wrappers";
import {
  createRouter,
  createMemoryHistory,
  createWebHistory,
  createWebHashHistory,
} from "vue-router";
import routes from "./routes.js";

// Rotas públicas que não precisam de autenticação
const rotasPublicas = [
  "login",
  "esqueci-senha",
  "criar-conta",
  "erro-403",
  "erro-500",
];

export default defineRouter(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === "history"
      ? createWebHistory
      : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });

  Router.beforeEach((to, from, next) => {
    const user = localStorage.getItem('user')
    const isPublica = rotasPublicas.includes(to.name) || !to.name

    // Não autenticado tentando acessar rota protegida
    if (!user && !isPublica) {
      return next({ name: 'login' })
    }

    // Autenticado tentando acessar login/register
    if (user && ['login', 'esqueci-senha', 'criar-conta'].includes(to.name)) {
      return next({ name: 'home' })
    }

    next()
  })

  return Router;
});
