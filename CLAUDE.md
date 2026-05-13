# `nfse-monorepo` — API multi-tenant pra emissão de NFS-e Nacional

> **Pra próximas sessões:** este arquivo é o ponto de entrada de contexto. Leia ele primeiro.

---

## O que é

Monorepo com **API REST multi-tenant** pra emissão/cancelamento/consulta de **NFS-e Nacional** (SefinNacional 1.6) + **painel admin Quasar** pra gerenciar clientes e visualizar emissões.

- **Repo:** github.com/mendesalexandre/nfse-monorepo
- **Diretório local:** `/home/alexandre/code/nfse-monorepo`
- **Branch principal:** `master`
- **Licença:** MIT

## Stack

- **Backend:** Laravel 13 + PHP 8.4 + PostgreSQL 18 (locale pt_BR) + Redis 7 + Sanctum dual mode (SPA + API token) + Reverb WebSocket + Pest 4
- **Frontend:** Quasar 2 + Vue 3 (Composition API + script setup) + Pinia + axios
- **Docker:** Compose dev/prod, supervisor (php-fpm + queue + scheduler + reverb), nginx, certbot Let's Encrypt
- **NFS-e:** SDK [`mendesalexandre/php-nfse-nacional`](https://github.com/mendesalexandre/php-nfse-nacional) `^0.5.x`

## Arquitetura

```
                  ┌─────────────────┐
   POST /api/v1/  │   nfse_nginx    │
   X-Api-Key  ──▶ │  (8089→80 dev   │
                  │  443/HTTPS prod)│
                  └────────┬────────┘
                           │
                           ▼
                  ┌─────────────────┐
                  │    nfse_app     │      ┌──────────────────────┐
                  │  PHP-FPM 8.4    │ ───▶ │ mendesalexandre/     │
                  │  Supervisor     │      │ php-nfse-nacional    │
                  │  (queue, sched, │      │ (composer dep)       │
                  │   reverb)       │      │  ├─ NFSe::create()   │
                  └────────┬────────┘      │  └─ ->emitir/cancelar│
                           │               └──────────┬───────────┘
                           │                          │
                           ▼                          ▼ HTTPS+mTLS
                  ┌─────────────────┐         ┌──────────────┐
                  │  nfse_postgres  │         │ SEFIN/ADN    │
                  │   (porta:5433)  │         │  homol/prod  │
                  └─────────────────┘         └──────────────┘
                  ┌─────────────────┐
                  │   nfse_redis    │
                  │  (porta:6380)   │
                  └─────────────────┘
                  ┌─────────────────┐
                  │  nfse_frontend  │
                  │ Quasar 2 + Vue 3│  ← painel admin
                  │  (porta:9100)   │
                  └─────────────────┘
```

## Estrutura

```
nfse-monorepo/
├── backend/                    # Laravel 13 (PHP 8.4) — API + admin
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── NFSe/       # API multi-tenant: emitir/consultar/cancelar/danfse
│   │   │   │   ├── Admin/      # CRUD clientes, emissões, audit (Sanctum SPA)
│   │   │   │   └── Auth/
│   │   │   ├── Middleware/
│   │   │   │   ├── AutenticarApiKey.php  # X-Api-Key + cache + rate limit
│   │   │   │   └── ChecarPermissao.php
│   │   │   ├── Requests/        # FormRequests (validação)
│   │   │   └── Resources/        # API Resources (não vazam encrypted)
│   │   ├── Models/Cliente, NfseEmissao, AuditLog, User, Grupo, Permissao
│   │   ├── Services/NFSe/NfseEmissorService, CertificadoLoader
│   │   ├── Console/Commands/NfseSmokeTestCommand.php  # 12 cenários
│   │   └── Traits/VerificaPermissao
│   ├── database/migrations,seeders,factories
│   ├── routes/api.php, web.php
│   ├── tests/Feature/  # Pest — 18 testes verde
│   ├── stubs/  # Stubs customizados pt_BR (data_criacao/data_alteracao)
│   ├── storage/certs/  # PFX (gitignored, montado read-only no container)
│   └── CLAUDE.md  # Convenções backend
├── frontend/                   # Quasar 2 + Vue 3
│   ├── src/
│   │   ├── pages/dashboard, clientes (Index/Form/Detalhe), emissoes
│   │   ├── stores/cliente, emissao, dashboard, auth (Pinia)
│   │   ├── components/CertSemaforo, StatusNfseBadge, CredencialModal
│   │   ├── router/
│   │   └── boot/axios.js  # withCredentials: true (Sanctum SPA)
│   ├── quasar.config.js
│   └── CLAUDE.md  # Convenções frontend
├── docker/
│   ├── Dockerfile              # PHP 8.4 + supervisor + gd + OpenSSL legacy
│   ├── openssl-sha1.cnf        # Habilita SHA1 (DPS rsa-sha1)
│   ├── init-ssl.sh             # Primeiro cert Let's Encrypt
│   ├── nginx/dev.conf, prod.conf
│   ├── postgres/Dockerfile     # postgres:18 + pt_BR.UTF-8
│   └── supervisor/supervisord.conf
├── docker-compose.yml          # Dev
├── docker-compose.prod.yml     # Prod (HTTPS, certbot, portainer)
├── Makefile                    # make up/down/migrate/fresh/test/shell
├── README.md                   # Setup quickstart
├── DEPLOY.md                   # Deploy passo-a-passo VM Debian 13
└── CLAUDE.md                   # ESTE arquivo
```

## Convenções de banco

- **Tabelas em pt_BR singular:** `usuario`, `cliente`, `nfse_emissao`, `audit_log`, `grupo`, `permissao`
- **Sem `created_at`/`updated_at`** — usar `data_criacao`, `data_alteracao`, `data_exclusao` (soft delete)
- Models em **inglês** (`User`, `Cliente`, `NfseEmissao`) com `$table` explícito + constantes pt_BR de timestamp
- Stubs customizados em `backend/stubs/` já usam isso

## Endpoints

### API multi-tenant (`X-Api-Key`)

| Método | Rota | O que faz |
|---|---|---|
| POST | `/api/v1/nfse` | Emite NFS-e |
| GET | `/api/v1/nfse/{chave}` | Consulta status no SEFIN |
| POST | `/api/v1/nfse/{chave}/cancelar` | Cancela (motivo 1/2/9 + justificativa) |
| GET | `/api/v1/danfse/{chave}` | Baixa PDF (gerado local via SDK) |

### Admin (Sanctum SPA)

| Método | Rota | Permissão |
|---|---|---|
| GET/POST/PUT/DELETE | `/api/admin/clientes` | `cliente.criar/editar/...` |
| POST | `/api/admin/clientes/{id}/cert` | `cliente.editar` (upload PFX) |
| POST | `/api/admin/clientes/{id}/regenerar-api-key` | `cliente.gerar_credenciais` |
| POST | `/api/admin/clientes/{id}/regenerar-client-secret` | idem |
| POST | `/api/admin/clientes/{id}/revogar` | `cliente.revogar` |
| GET | `/api/admin/nfses` | `nfse.consultar` (listagem + filtros) |
| GET | `/api/admin/nfses/{chave}` | idem |
| GET | `/api/admin/audit-logs` | admin |

### Auth (Sanctum dual)

- SPA: `GET /sanctum/csrf-cookie` → `POST /login` (cookie HTTP-only)
- API: `POST /api/tokens` → Bearer token

## Multi-tenancy

Tabela `cliente` (uma linha por integração):
- Dados da empresa: nome, cnpj, email, endereço completo
- Cert PFX **encrypted at-rest** (Laravel `Crypt::encryptString`) + senha encrypted
- API key **bcrypt hash** + client_id/client_secret (hash) — show-once na criação
- Config NFS-e: ambiente (homologação/produção), regime tributário, simples nacional, IM, razão social, `incluir_ibscbs`
- `is_ativo`, soft delete via `data_exclusao`

Cada emissão em `nfse_emissao` tem `cliente_id` (FK) + tomador/discriminação/payload/XML retorno **encrypted**.

`audit_log` append-only registra IP/user_agent/payload de toda operação.

## LGPD

- **Cert PFX** + **dados pessoais** (CPF/CNPJ tomador, nome, endereço, discriminação) **encrypted at-rest** via `Crypt::encryptString` (chave `APP_KEY`)
- **Audit log** com IP/UA de cada emissão/consulta/cancelamento
- **Soft delete** via `data_exclusao`
- **Retenção fiscal:** 5 anos (SPED) — cron de purge ainda não implementado
- API Resources nunca vazam campos `*_encrypted`, `*_hash`

## OpenSSL legacy provider

DPS exige `rsa-sha1` (Anexo I do leiaute). OpenSSL 3.5+ (Fedora 43, Debian 13, RHEL 9 atualizado) desabilita SHA1 por default.

- **Container:** `OPENSSL_CONF=/etc/ssl/openssl-sha1.cnf` no Dockerfile (já configurado)
- **Local sem Docker:** `OPENSSL_CONF=/path/openssl-sha1.cnf php artisan ...`
- Sem isso: `error:03000098:digital envelope routines::invalid digest` na assinatura DPS

## Comandos úteis

```bash
# Docker dev
make up          # sobe tudo
make down        # para
make build       # rebuild + sobe
make shell       # bash dentro do container app
make migrate     # php artisan migrate
make fresh       # migrate:fresh + seed
make tinker
make test        # Pest
make logs        # logs do app
make psql        # psql no banco
make permissions # corrige perms storage

# Smoke test NFS-e (12 cenários em homologação SEFIN)
docker compose exec app php artisan nfse:smoke-test

# Sem Docker (SQLite)
cd backend && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
OPENSSL_CONF=/path/openssl-sha1.cnf php artisan serve --port=8000
```

## Validado em homologação SEFIN

**Smoke test 11/12 cenários OK** rodando dentro do Docker:
1. PF básico ✅ | 2. PF com email/telefone ✅ | 3. PJ básico ✅
4. PJ com IM ✅ | 5. Alíquota 3.5125→3.51 ✅ | 6. Alíquota 3.5995→3.60 ✅
7. IBSCBS habilitado ✅ | 8. Retroativo -7d ✅ | 9. Cancelamento ✅
10. Substituição ⚠️ cStat=1861 (parametrização Sinop)
11. Manifestação Confirmação Prestador ✅ | 12. Rejeição Prestador ✅

## Cliente teste seedado (cartório Sinop)

Pra rodar smoke + testar UI, o `ClienteCartorioSinopSeeder` cria:

```
nome:           Cartório de Registro de Imóveis de Sinop
cnpj:           00179028000138
ambiente:       homologacao
X-Api-Key:      nfse_test_sinop_2026
client_id:      cli_sinop_001
client_secret:  sk_sinop_2026_secret
cert:           backend/storage/certs/cartorio_sinop.pfx (PFX_PATH env override)
```

**Cert NÃO está commitado** (gitignored). Pra subir local:
```bash
mkdir -p backend/storage/certs
cp /home/alexandre/code/SINOP/certificado_digital_a1_ecnpj_00179028000138.pfx \
   backend/storage/certs/cartorio_sinop.pfx
```

## Credenciais admin (dev)

```
Email: suporte@sistemaoslo.com.br
Senha: password
Grupo: Administrador (acesso total)
```

## URLs locais (Docker dev)

| Serviço | URL |
|---|---|
| Frontend Quasar | http://localhost:9100 |
| API REST (via nginx) | http://localhost:8089 |
| Postgres | localhost:5433 (user/db `nfse`, senha `secret`) |
| Redis | localhost:6380 |
| Reverb WS | localhost:8095 |

## Deploy em produção

Ver [`DEPLOY.md`](DEPLOY.md) — passo-a-passo completo VM Debian 13 + Docker + Let's Encrypt + Portainer.

## Workflow de release

Não há tags por enquanto (projeto monolítico, deploy via `git pull` + rebuild). Quando virar SaaS multi-tenant pago, considerar versionamento semântico.

## Onde NÃO mexer

- `vendor/` (composer) e `node_modules/` (npm) — gerenciados por package managers
- `backend/storage/certs/` — dados sensíveis, NUNCA commit
- `backend/.env*` — secrets locais
- Migrations já aplicadas em produção — sempre criar migration nova pra mudar schema

## Documentação

- [`README.md`](README.md) — quickstart
- [`DEPLOY.md`](DEPLOY.md) — deploy prod (VM + SSL)
- [`backend/CLAUDE.md`](backend/CLAUDE.md) — convenções backend (auth, perms, models)
- [`frontend/CLAUDE.md`](frontend/CLAUDE.md) — convenções frontend (Quasar, Pinia, axios)

## Repositórios relacionados

- **SDK** (dependência core): `/home/alexandre/code/sinop-nfse-nacional` → `mendesalexandre/php-nfse-nacional` (Packagist)
- **SINOP** (sistema cartório legado): `/home/alexandre/code/SINOP` — usa Hadder + shadow do SDK pra migração

## Memórias relacionadas (auto-memory)

Em `~/.claude/projects/-home-alexandre-code-SINOP/memory/`:
- `sdk-php-nfse-nacional.md` — histórico de versões do SDK consumido
- `sdk-roadmap-lote.md` — análise sobre lote (não existe no SefinNacional)
