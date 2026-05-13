# Backend — Laravel 13

API backend rodando em PHP 8.4 com PostgreSQL 18 e Redis 7.

## Stack

- Laravel 13 (API)
- PHP 8.4-FPM
- PostgreSQL 18 (pt_BR.UTF-8)
- Redis 7 (cache, queue, session)
- Laravel Reverb (WebSocket)
- Sanctum SPA (autenticação por session + cookies HTTP-only)
- Pest (testes)
- Pint (lint)

## Convenções de Banco de Dados

- Tabelas em **português no singular**: `usuario`, `produto`, `pedido`
- Colunas em **português**: `nome`, `senha`, `is_ativo`
- **Sem timestamps()** — usar campos explícitos:
  - `data_criacao` (CREATED_AT) — ou `data_cadastro` para tabelas de cadastro
  - `data_alteracao` (UPDATED_AT)
  - `data_exclusao` (DELETED_AT) para soft delete
- Chave primária: `id` (bigIncrements)
- Foreign keys: `usuario_id`, `produto_id` (singular + _id)
- Stubs customizados em `stubs/` já usam `data_criacao`/`data_alteracao`

## Models

- Nome do Model em **inglês** (padrão Laravel): `User`, `Product`, `Post`
- Definir `$table` explicitamente: `protected $table = 'usuario'`
- Constantes de timestamp customizadas:
  ```php
  const CREATED_AT = 'data_criacao';
  const UPDATED_AT = 'data_alteracao';
  const DELETED_AT = 'data_exclusao';
  ```
- Usar `$fillable` array (não attribute `#[Fillable]` para models com convenções pt_BR)
- Usar `HasFactory` para factories
- Usar `SoftDeletes` com `data_exclusao`

## Autenticação (Sanctum — Dual Mode)

O Sanctum diferencia automaticamente: cookie de session → SPA, header Bearer → API.

### SPA (frontend Quasar)
- Session driver + cookies HTTP-only
- Fluxo: `GET /sanctum/csrf-cookie` → `POST /login`
- Rotas web: `/login`, `/logout`, `/register`, `/forgot-password`
- `statefulApi()` habilitado em `bootstrap/app.php`

### API (sistemas externos)
- Token via `POST /api/tokens` (retorna `Bearer` token)
- Suporte a **abilities** (permissões por token)
- Gerenciamento: listar (`GET /api/tokens`), revogar (`DELETE /api/tokens/{id}`)
- User model usa `HasApiTokens`

### Rotas de token

| Método | Rota | Auth | Descrição |
|---|---|---|---|
| POST | `/api/tokens` | Não | Gera token (envia email + senha + device_name) |
| GET | `/api/tokens` | Sim | Lista tokens do usuário |
| DELETE | `/api/tokens/current` | Sim | Revoga token atual |
| DELETE | `/api/tokens/{id}` | Sim | Revoga token específico |

### Abilities (permissões por token)

```php
// Gerar token com permissões limitadas
POST /api/tokens
{
  "email": "user@email.com",
  "senha": "password",
  "device_name": "sistema-parceiro",
  "abilities": ["posts:read", "posts:create"]
}

// No controller, verificar:
if (! $request->user()->tokenCan('posts:create')) {
    abort(403);
}
```

### Envs Sanctum
```env
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:9100,localhost:8080
FRONTEND_URL=http://localhost:9100
```

### Campo de senha
- Campo é `senha` (não `password`) — `getAuthPassword()` customizado no User model

### Usuário de teste
- **Email**: `suporte@sistemaoslo.com.br`
- **Senha**: `password`

## Sistema de Permissões

### Estrutura de tabelas

```
grupo               → Grupos de usuários (ex: Administrador, Operador)
permissao           → Permissões individuais (ex: posts.criar, posts.excluir)
grupo_permissao     → Pivot: quais permissões cada grupo tem
usuario_grupo       → Pivot: quais grupos cada usuário pertence
usuario_permissao   → Pivot: permissão individual por usuário (permitir/negar)
```

### Hierarquia de resolução

1. **Admin bypass** — grupo "Administrador" tem acesso total
2. **Negar individual** — `usuario_permissao` com `tipo=negar` bloqueia mesmo se o grupo permite
3. **Permitir individual** — `usuario_permissao` com `tipo=permitir` libera mesmo sem grupo
4. **Grupo** — se o grupo do usuário tem a permissão, libera

### Convenção de nomes de permissão

Formato: `modulo.acao`

```
posts.listar
posts.criar
posts.editar
posts.excluir
usuarios.listar
usuarios.criar
```

### Uso no middleware (rotas)

```php
// Exige UMA das permissões (OR)
Route::get('/posts', [PostController::class, 'index'])
    ->middleware('permissao:posts.listar');

Route::post('/posts', [PostController::class, 'store'])
    ->middleware('permissao:posts.criar');

// Exige alguma das permissões
Route::get('/admin', AdminController::class)
    ->middleware('permissao:admin.painel,admin.relatorios');
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

### Uso direto no model User

```php
$user->temPermissao('posts.criar');          // bool
$user->temAlgumaPermissao(['posts.criar', 'posts.editar']); // bool (OR)
$user->temTodasPermissoes(['posts.criar', 'posts.editar']); // bool (AND)
$user->pertenceAoGrupo('Administrador');     // bool
$user->isAdmin();                            // bool (grupo Administrador)
$user->obterPermissoes();                    // ['posts.criar', 'posts.editar', ...]
$user->obterPermissoesPorModulo();           // ['posts' => ['criar', 'editar'], ...]
```

### Endpoint de permissões do usuário logado

O endpoint `GET /api/user` retorna o usuário. Para retornar as permissões junto, use no frontend:
```
GET /api/user/permissoes
```

## Rotas

- Rotas de auth ficam em `routes/web.php` (session/cookies)
- Rotas API protegidas ficam em `routes/api.php` com middleware `auth:sanctum`
- Rotas de broadcast em `routes/channels.php`
- Registradas em `bootstrap/app.php`

## Broadcasting

- `BROADCAST_CONNECTION=reverb`
- `QUEUE_CONNECTION=redis` — eventos `ShouldBroadcast` passam pela queue
- Canais definidos em `routes/channels.php`
- Eventos em `app/Events/`

### Padrão para novo evento broadcast

1. Criar evento implementando `ShouldBroadcast`
2. Definir `broadcastOn()` com `Channel` (público) ou `PrivateChannel` (autenticado)
3. Despachar com `broadcast(new Evento($model))->toOthers()`

### Padrão webhook + broadcast (ex: PIX)

```
Gateway → POST /api/webhooks/x → Controller processa → broadcast(new Evento($model))
  → Reverb → PrivateChannel('conta.' . $conta_id) → Frontend do usuário
```

Canais privados precisam de autorização em `routes/channels.php`.

## Controllers

- Validação inline com `$request->validate()`
- Retornar JSON diretamente (API-only)

## Testes

```bash
make test    # Roda Pest
make fix     # Roda Pint
```

## Comandos úteis dentro do container

```bash
make shell                    # Entra no container
php artisan make:model X -mf  # Model + migration + factory
php artisan make:controller XController
php artisan make:event XCreated
php artisan reverb:start      # Inicia Reverb manualmente (já roda via supervisor)
```
