# Laravel Docker — Monorepo

Monorepo com Laravel 13 (backend API) + Quasar 2 / Vue 3 (frontend SPA) em Docker.

## Estrutura

```
laravel-docker/
├── docker-compose.yml          # Dev
├── docker-compose.prod.yml     # Produção
├── Makefile                    # Atalhos (make up, make build, etc.)
├── .dockerignore
├── .github/workflows/deploy.yml
├── backend/                    # Laravel 13 (PHP 8.4)
├── frontend/                   # Quasar 2 + Vue 3
└── docker/
    ├── Dockerfile              # App (PHP-FPM + Supervisor)
    ├── init-ssl.sh             # Primeiro certificado SSL
    ├── nginx/
    │   ├── dev.conf            # Nginx dev (porta 8080 → Laravel)
    │   └── prod.conf           # Nginx prod (HTTPS, SPA + API)
    ├── postgres/
    │   └── Dockerfile          # Postgres 18 com locale pt_BR
    └── supervisor/
        └── supervisord.conf    # PHP-FPM + Queue + Scheduler + Reverb
```

## Containers (dev)

| Container | Imagem | Porta host | Porta interna |
|---|---|---|---|
| app | PHP 8.4-FPM + Supervisor | 8085 (Reverb WS) | 9000 (FPM), 8085 (Reverb) |
| nginx | nginx:alpine | 8080 | 80 |
| frontend | node:20-alpine | 9100 | 9100 |
| postgres | Dockerfile customizado (postgres:18 + pt_BR) | 5433 | 5432 |
| redis | redis:7-alpine | 6380 | 6379 |

Portas 5433 e 6380 evitam conflito com Postgres e Redis locais do host.

## Containers (prod) — adicionais

| Container | O que faz |
|---|---|
| certbot | Renova SSL automaticamente a cada 12h |
| portainer | Monitoramento em `https://dominio:9443` |

## Comandos (Makefile)

```bash
make up          # Sobe containers + corrige permissões
make down        # Para containers
make build       # Rebuild + sobe + permissões
make shell       # Bash dentro do container app
make migrate     # php artisan migrate
make fresh       # migrate:fresh --seed (reseta banco + cria admin)
make tinker      # php artisan tinker
make test        # php artisan test
make fix         # Pint (lint)
make logs        # Logs do app
make logs-nginx  # Logs do nginx
make logs-front  # Logs do frontend
make front-shell # Shell no container frontend
make front-build # Build do Quasar
make psql        # psql no banco
make permissions # Corrige permissões storage/
make prune       # Limpa Docker não usado
```

## Rede interna Docker

Dentro da rede Docker os containers se comunicam pelo **nome do serviço**:
- `postgres` (não 127.0.0.1) para o banco
- `redis` para cache/queue
- `nginx` para proxy do frontend → backend
- `app` para Nginx → PHP-FPM (porta 9000)

## Convenções de Banco de Dados

- Tabelas em **português no singular**: `usuario`, `produto`, `pedido`
- Colunas em **português**: `nome`, `senha`, `is_ativo`
- **Sem timestamps()** — usar `data_criacao`/`data_cadastro`, `data_alteracao`, `data_exclusao`
- Foreign keys: `usuario_id`, `produto_id` (singular + _id)
- Stubs customizados em `backend/stubs/`
- Models em inglês com `$table` explícito e constantes de timestamp customizadas

## Autenticação (Sanctum — Dual Mode)

O Sanctum diferencia automaticamente: cookie de session → SPA, header Bearer → API.

### SPA (frontend Quasar)
- Session driver + cookies HTTP-only
- Fluxo: `GET /sanctum/csrf-cookie` → `POST /login`
- Rotas auth em `routes/web.php`: `/login`, `/logout`, `/register`, `/forgot-password`
- `statefulApi()` habilitado em `bootstrap/app.php`
- Campo de senha: `senha` (não `password`) — `getAuthPassword()` customizado
- Navigation guard no router redireciona para login se não autenticado

### API (sistemas externos)
- Token via `POST /api/tokens` (retorna Bearer token)
- Suporte a **abilities** (permissões por token)
- Gerenciamento: listar, revogar

### Credenciais de teste
- **Email:** `suporte@sistemaoslo.com.br`
- **Senha:** `password`
- **Grupo:** Administrador (acesso total)

### Seeder (`make fresh`)
O `DatabaseSeeder` cria:
- Grupo `Administrador`
- 16 permissões base (posts, usuarios, grupos, permissoes × listar/criar/editar/excluir)
- Usuário admin com grupo Administrador

## Sistema de Permissões

### Tabelas

```
grupo               → Grupos de usuários (ex: Administrador, Operador)
permissao           → Permissões individuais (ex: posts.criar)
grupo_permissao     → Pivot: quais permissões cada grupo tem
usuario_grupo       → Pivot: quais grupos cada usuário pertence
usuario_permissao   → Pivot: permissão individual por usuário (permitir/negar)
```

### Hierarquia de resolução
1. **Admin bypass** — grupo "Administrador" tem acesso total
2. **Negar individual** — bloqueia mesmo se o grupo permite
3. **Permitir individual** — libera mesmo sem grupo
4. **Grupo** — se o grupo tem a permissão, libera

### Convenção de nomes
Formato: `modulo.acao` (ex: `posts.criar`, `usuarios.excluir`)

### Uso nas rotas
```php
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('permissao:posts.criar');
```

### Uso no controller (trait)
```php
use App\Traits\VerificaPermissao;

class PostController extends Controller
{
    use VerificaPermissao;

    public function destroy(Post $post)
    {
        if ($erro = $this->verificarPermissao('posts.excluir')) {
            return $erro;
        }
        // ...
    }
}
```

### Uso no model User
```php
$user->temPermissao('posts.criar');
$user->temAlgumaPermissao(['posts.criar', 'posts.editar']);
$user->isAdmin();
$user->obterPermissoes();           // ['posts.criar', ...]
$user->obterPermissoesPorModulo();  // ['posts' => ['criar', ...]]
```

## .env do Backend

Valores obrigatórios para Docker:
```env
APP_TIMEZONE=America/Cuiaba
APP_LOCALE=pt_BR
DB_HOST=postgres
DB_USERNAME=laravel_docker
DB_PASSWORD=secret
REDIS_HOST=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
BROADCAST_CONNECTION=reverb
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:9100,localhost:8080
FRONTEND_URL=http://localhost:9100
```

## .env do Frontend

```env
API_URL=http://localhost:8080
BACKEND_URL=http://nginx          # Proxy interno Docker
REVERB_APP_KEY=<chave do .env backend>
REVERB_HOST=localhost
REVERB_PORT=8085
REVERB_SCHEME=http
```

## Supervisor (processos no container app)

O container `app` roda 4 processos via Supervisor:
1. **php-fpm** — Serve requests PHP (porta 9000)
2. **laravel-worker** — Queue worker Redis (2 processos)
3. **laravel-scheduler** — `schedule:work` (substitui cron)
4. **laravel-reverb** — WebSocket server (porta 8085)

## WebSocket (Laravel Reverb)

### Como funciona
- Reverb roda dentro do container `app` na porta 8085
- Frontend conecta via `laravel-echo` + `pusher-js` (boot `src/boot/echo.js`)
- `pusher-js` é só transport layer — conexão é 100% local com o Reverb, não usa pusher.com
- Eventos implementam `ShouldBroadcast` e são despachados via queue

### Configuração principal (`config/reverb.php`)

**Servidor:**
- `REVERB_SERVER_HOST=0.0.0.0` — escuta em todas interfaces (obrigatório no Docker)
- `REVERB_SERVER_PORT=8085` — porta do WS server

**App (credenciais):**
- `REVERB_APP_KEY` — chave pública (frontend usa para conectar)
- `REVERB_APP_SECRET` — chave privada (backend assina mensagens)
- `REVERB_APP_ID` — identificador da app

**Client (como o frontend conecta):**
- `REVERB_HOST=localhost` — hostname público
- `REVERB_PORT=8085` — porta pública
- `REVERB_SCHEME=http` — http em dev, https em prod

**Scaling (múltiplas instâncias):**
- `REVERB_SCALING_ENABLED=false` — ligar se usar load balancer com múltiplos containers app
- Usa Redis para sincronizar mensagens entre instâncias

**Limites:**
- `REVERB_APP_MAX_CONNECTIONS` — null = sem limite
- `REVERB_APP_MAX_MESSAGE_SIZE=10000` — 10KB por mensagem
- `REVERB_APP_PING_INTERVAL=60` — ping a cada 60s
- `REVERB_APP_ACTIVITY_TIMEOUT=30` — desconecta após 30s sem atividade

### Criando um evento broadcast

```php
class MeuEvento implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Model $model) {}

    public function broadcastOn(): array
    {
        return [new Channel('meu-canal')];
        // Privado: return [new PrivateChannel('conta.' . $this->model->conta_id)];
    }
}

// No controller:
broadcast(new MeuEvento($model))->toOthers();
```

### Escutando no frontend (Quasar)

```js
proxy.$echo.channel('meu-canal')
  .listen('MeuEvento', (e) => { console.log(e.model) })

// Privado:
proxy.$echo.private('conta.1')
  .listen('PagamentoConfirmado', (e) => { ... })

// Limpar ao sair:
onUnmounted(() => proxy.$echo.leave('meu-canal'))
```

### Padrão webhook + broadcast (ex: PIX)

```
Gateway PIX → POST /api/webhooks/pix → Backend processa
  → broadcast(new PagamentoConfirmado($pagamento))
  → PrivateChannel('conta.' . $conta_id) → Frontend do usuário
```

### Produção — Reverb atrás do Nginx (WSS)

Adicionar no `prod.conf`:
```nginx
location /app {
    proxy_pass http://app:8085;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}
```

E no `.env.prod`:
```env
REVERB_HOST=seu-dominio.com.br
REVERB_PORT=443
REVERB_SCHEME=https
```

## CI/CD (GitHub Actions)

Pipeline em `.github/workflows/deploy.yml`:

1. **test** — Pint + Pest com Postgres + Redis (roda em paralelo com build-frontend)
2. **build-frontend** — `npm ci` + `quasar build`, salva artefato
3. **deploy** — SCP do build, git pull na VPS, rebuild containers, migrations, cache

### Secrets necessários no GitHub
- `VPS_HOST` — IP ou domínio da VPS
- `VPS_USER` — usuário SSH
- `VPS_SSH_KEY` — chave privada SSH

## SSL (Certbot)

### Primeiro deploy
```bash
./docker/init-ssl.sh seu-dominio.com.br seu@email.com
```

Depois a renovação é automática (container `certbot` renova a cada 12h).

## Postgres

- Versão 18 com locale `pt_BR.UTF-8`
- Dockerfile customizado em `docker/postgres/Dockerfile`
- Volume em `/var/lib/postgresql` (não `/var/lib/postgresql/data` — exigência do Postgres 18)
- Timezone: `America/Cuiaba` (configurável por cliente via `APP_TIMEZONE`)

## Portas resumo (dev)

| Serviço | URL |
|---|---|
| Frontend (Quasar) | `http://localhost:9100` |
| Backend API (via Nginx) | `http://localhost:8080/api/*` |
| Auth (via Nginx) | `http://localhost:8080/login` |
| WebSocket (Reverb) | `ws://localhost:8085` |
| Postgres (acesso externo) | `localhost:5433` |
| Redis (acesso externo) | `localhost:6380` |

## Primeiro uso

```bash
make build      # Builda e sobe tudo
make fresh      # Cria tabelas + usuário admin
# Acesse localhost:9100
# Login: suporte@sistemaoslo.com.br / password
```
