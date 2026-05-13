# Autenticação Sanctum SPA

## Como funciona

O projeto usa **Laravel Sanctum** no modo SPA (session-based, HTTP-only cookies).
O frontend faz requests para o backend, que autentica via session cookies — sem tokens Bearer.

## Fluxo de autenticação

1. `GET /sanctum/csrf-cookie` — Laravel seta o cookie `XSRF-TOKEN`
2. `POST /login` — Axios envia o XSRF-TOKEN automaticamente no header `X-XSRF-TOKEN`
3. Laravel valida o CSRF, autentica e inicia a session
4. `GET /api/user` — retorna o usuário autenticado via session cookie

## Desenvolvimento local (domínios diferentes)

No ambiente de desenvolvimento, frontend e backend rodam em domínios diferentes:

- **Frontend**: `http://localhost:9000`
- **Backend**: `http://api.sistemaoslo.local`

Como os domínios são diferentes, os cookies do Sanctum **não transitam** entre eles.
Por isso, usamos o **proxy do webpack-dev-server** para que todas as requests
saiam do mesmo domínio (`localhost:9000`).

### Configuração do proxy (`quasar.config.js`)

```js
devServer: {
  proxy: [
    {
      context: ["/api", "/sanctum", "/login", "/logout", "/register", "/forgot-password"],
      target: process.env.BACKEND_URL || "http://localhost:8000",
      changeOrigin: true,
    },
  ],
},
```

### Axios (`src/boot/axios.js`)

```js
const api = axios.create({
  baseURL: '/', // Usa o proxy — requests saem de localhost:9000
  withCredentials: true,
})
```

### Backend `.env`

```
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:9000,localhost:8000,127.0.0.1:9000,api.sistemaoslo.local
```

## Produção / domínios iguais (sem proxy)

Quando frontend e backend compartilham o **mesmo domínio pai**, o proxy **não é necessário**.
Os cookies transitam naturalmente entre subdomínios.

Exemplo:
- **Frontend**: `https://app.meudominio.com.br`
- **Backend**: `https://api.meudominio.com.br`

### Axios em produção

```js
const api = axios.create({
  baseURL: 'https://api.meudominio.com.br', // Request direto, sem proxy
  withCredentials: true,
})
```

### Backend `.env` em produção

```
APP_URL=https://api.meudominio.com.br
SESSION_DOMAIN=.meudominio.com.br
SANCTUM_STATEFUL_DOMAINS=app.meudominio.com.br,api.meudominio.com.br
SESSION_SECURE_COOKIE=true
```

O ponto-chave é o `SESSION_DOMAIN=.meudominio.com.br` (com ponto na frente) —
isso permite que o cookie da session seja compartilhado entre todos os subdomínios.

### CORS em produção (`config/cors.php` ou middleware)

```php
'allowed_origins' => ['https://app.meudominio.com.br'],
'supports_credentials' => true,
```

## Resumo

| Cenário | Proxy | baseURL do Axios | SESSION_DOMAIN |
|---------|-------|------------------|----------------|
| Dev (domínios diferentes) | Sim | `/` | `localhost` |
| Produção (mesmo domínio pai) | Nao | `https://api.dominio.com.br` | `.dominio.com.br` |
