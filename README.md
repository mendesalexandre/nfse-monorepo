# NFSe Emissor — Monorepo

Monorepo com **API multi-tenant de emissão de NFS-e Nacional** (Laravel 13) + **Quasar 2 / Vue 3** (frontend SPA) rodando em Docker.

Backed pelo SDK público [`mendesalexandre/php-nfse-nacional`](https://github.com/mendesalexandre/php-nfse-nacional) — emissão, consulta, cancelamento, substituição, manifestação e geração local de DANFSe.

## Setup rápido pra desenvolvimento local (sem Docker)

```bash
cd backend
cp .env.example .env
# Editar .env: DB_CONNECTION=sqlite, DB_DATABASE=database/database.sqlite,
# SESSION_DRIVER=array, QUEUE_CONNECTION=sync, CACHE_STORE=array
touch database/database.sqlite

php artisan key:generate
php artisan migrate --seed       # cria admin + cliente teste Cartório Sinop
php artisan test                  # roda Pest (8 testes)

# Smoke test contra SEFIN homologação (precisa do OPENSSL_CONF p/ rsa-sha1)
OPENSSL_CONF=/home/alexandre/code/SINOP/backend/docs/openssl-sha1.cnf \
  php artisan nfse:smoke-test

# Servir API local
OPENSSL_CONF=/home/alexandre/code/SINOP/backend/docs/openssl-sha1.cnf \
  php artisan serve --port=8000
```

Credenciais do cliente de teste seedado:

| Campo | Valor |
|---|---|
| X-Api-Key | `nfse_test_sinop_2026` |
| client_id | `cli_sinop_001` |
| client_secret | `sk_sinop_2026_secret` |
| Ambiente | homologação SEFIN |

### Endpoints da API multi-tenant

| Método | Rota | Descrição |
|---|---|---|
| POST | `/api/v1/nfse` | Emite uma NFS-e |
| GET | `/api/v1/nfse/{chave}` | Consulta status no SEFIN |
| POST | `/api/v1/nfse/{chave}/cancelar` | Cancela (motivo 1, 2 ou 9) |
| GET | `/api/v1/danfse/{chave}` | Gera PDF DANFSe local |

Auth: header `X-Api-Key: <plain key>` (verificada via bcrypt contra `cliente.api_key_hash`).

Exemplo:

```bash
curl -X POST http://localhost:8000/api/v1/nfse \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: nfse_test_sinop_2026" \
  -d '{
    "tomador": {
      "documento": "11144477735",
      "nome": "Joao da Silva",
      "endereco": {
        "logradouro": "Rua dos Testes",
        "numero": "100",
        "bairro": "Centro",
        "cep": "78550200",
        "codigo_municipio_ibge": "5107909",
        "uf": "MT"
      }
    },
    "servico": { "discriminacao": "Certidao de matricula imobiliaria." },
    "valores": {
      "valor_servicos": 100.00,
      "deducoes_reducoes": 0,
      "aliquota_issqn_percentual": 4.0
    }
  }'
```

### Por que `OPENSSL_CONF`?

OpenSSL 3.5+ (Fedora 43, RHEL 9) desabilita SHA1 por padrão. O DPS do Portal Nacional usa `rsa-sha1` (exigência do leiaute SefinNacional). Sem o `OPENSSL_CONF` apontando pra `openssl-sha1.cnf` (que habilita o legacy provider), a assinatura falha com `error:03000098:digital envelope routines::invalid digest`.

---

## Setup completo (Docker)

Monorepo com **Laravel 13** (backend API) + **Quasar 2 / Vue 3** (frontend SPA) rodando em Docker.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 13, PHP 8.4, Sanctum (SPA + API tokens) |
| Frontend | Quasar 2, Vue 3, Pinia, Laravel Echo |
| Banco | PostgreSQL 18 (pt_BR.UTF-8) |
| Cache/Queue | Redis 7 |
| WebSocket | Laravel Reverb |
| Web Server | Nginx |
| Processos | Supervisor (FPM + Queue + Scheduler + Reverb) |
| CI/CD | GitHub Actions |
| SSL | Certbot (Let's Encrypt) |
| Monitoramento | Portainer CE |

## Estrutura

```
├── docker-compose.yml          # Dev
├── docker-compose.prod.yml     # Produção
├── Makefile                    # Atalhos
├── backend/                    # Laravel 13
├── frontend/                   # Quasar 2
└── docker/
    ├── Dockerfile              # PHP-FPM + Supervisor
    ├── init-ssl.sh             # Primeiro certificado SSL
    ├── nginx/                  # Configs Nginx (dev + prod)
    ├── postgres/               # Postgres com locale pt_BR
    └── supervisor/             # Supervisord config
```

## Requisitos

- Docker + Docker Compose
- Portas livres: 8080, 8085, 9100 (ou ajustar no compose)

## Inicio rapido

```bash
# Clonar
git clone git@github.com:mendesalexandre/laravel-inicial.git
cd laravel-inicial

# Copiar envs
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# Subir tudo
make build
make fresh    # Cria tabelas + usuario admin
```

Acesse `http://localhost:9100` e faca login:
- **Email:** `suporte@sistemaoslo.com.br`
- **Senha:** `password`

| Servico | URL |
|---|---|
| Frontend | http://localhost:9100 |
| API | http://localhost:8080/api |
| WebSocket | ws://localhost:8085 |

## Autenticacao

### SPA (frontend)
Session + cookies HTTP-only via Sanctum. O frontend faz proxy das requests `/login`, `/sanctum`, `/api` para o backend via Nginx. Navigation guard redireciona para login se nao autenticado.

### API (sistemas externos)
Token via `POST /api/tokens`:
```bash
curl -X POST http://localhost:8080/api/tokens \
  -H "Content-Type: application/json" \
  -d '{"email":"suporte@sistemaoslo.com.br","senha":"password","device_name":"meu-sistema"}'
# Retorna: {"token": "1|abc123..."}

# Usar:
curl http://localhost:8080/api/user \
  -H "Authorization: Bearer 1|abc123..."
```

Suporte a **abilities** (permissoes por token):
```json
{"email":"...","senha":"...","device_name":"x","abilities":["posts:read"]}
```

## Permissoes

Sistema proprio de permissoes com grupos e permissoes individuais.

| Tabela | Descricao |
|---|---|
| `grupo` | Grupos (Administrador, Operador, etc.) |
| `permissao` | Permissoes (posts.criar, usuarios.excluir, etc.) |
| `grupo_permissao` | Pivot grupo ↔ permissao |
| `usuario_grupo` | Pivot usuario ↔ grupo |
| `usuario_permissao` | Permissao individual (permitir/negar) |

**Hierarquia:** Admin bypass > negar individual > permitir individual > grupo

```php
// Middleware nas rotas
Route::post('/posts', ...)->middleware('permissao:posts.criar');

// No model
$user->temPermissao('posts.criar');
$user->isAdmin();
```

## WebSocket (Reverb)

Reverb roda na porta 8085. Frontend conecta via `laravel-echo` + `pusher-js` (protocolo Pusher local, sem dependencia externa).

```php
// Backend: disparar evento
broadcast(new PostCreated($post))->toOthers();

// Frontend: escutar
proxy.$echo.channel('posts').listen('PostCreated', (e) => { ... })
```

## Comandos principais

```bash
make up          # Sobe containers
make down        # Para containers
make build       # Rebuild + sobe
make fresh       # migrate:fresh --seed (reseta banco + cria admin)
make migrate     # Roda migrations
make shell       # Bash no container app
make tinker      # Laravel tinker
make test        # Pest (testes)
make fix         # Pint (lint)
make logs        # Logs do app
make psql        # psql no banco
```

## Producao

```bash
# Primeiro deploy (gerar SSL)
./docker/init-ssl.sh seu-dominio.com.br seu@email.com

# Deploys seguintes via CI/CD (push na main)
```

### Secrets do GitHub Actions

| Secret | Descricao |
|---|---|
| `VPS_HOST` | IP ou dominio da VPS |
| `VPS_USER` | Usuario SSH |
| `VPS_SSH_KEY` | Chave privada SSH |

## Convencoes

- Tabelas em **portugues no singular**: `usuario`, `produto`
- Timestamps: `data_criacao`/`data_cadastro`, `data_alteracao`, `data_exclusao`
- Models em ingles com `$table` explicito
- Campo de senha: `senha` (nao `password`)
- Stubs customizados em `backend/stubs/`
- Timezone configuravel via `APP_TIMEZONE` (default: `America/Cuiaba`)
