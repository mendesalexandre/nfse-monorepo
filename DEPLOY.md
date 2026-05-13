# Deploy em Produção

Guia passo-a-passo pra subir o `nfse-monorepo` numa VM Linux (recomendado: **Debian 13 Trixie**).

---

## Pré-requisitos

- VM com **2 GB RAM mínimo** (recomendado 4 GB), 20 GB disco, kernel 5.10+
- **IP público** + **domínio** (ex.: `nfse.minhaempresa.com.br`) com DNS apontando pra VM
- Portas **80** e **443** abertas no firewall
- Acesso SSH como root ou usuário com sudo

> Por que Debian 13: Docker roda nativo sem fricção SELinux (AlmaLinux exige `:Z` em volumes), apt instala PHP 8.4 / certbot direto, kernel 6.12 melhora networking de containers.

---

## 1. Setup da VM (Debian 13)

```bash
# Atualizar
apt update && apt upgrade -y

# Pacotes base
apt install -y curl git ca-certificates gnupg ufw fail2ban

# Firewall: SSH + HTTP + HTTPS apenas
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
# Opcional: Portainer (admin) — só liberar se vai usar
# ufw allow 9443/tcp
ufw --force enable

# Fail2ban (proteção contra brute-force SSH)
systemctl enable --now fail2ban
```

---

## 2. Docker

```bash
# Repo oficial Docker
install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | tee /etc/apt/keyrings/docker.asc > /dev/null
chmod a+r /etc/apt/keyrings/docker.asc

echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
  | tee /etc/apt/sources.list.d/docker.list > /dev/null

apt update
apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Habilita
systemctl enable --now docker

# (opcional) usuário não-root no docker
usermod -aG docker SEU_USUARIO
```

---

## 3. Clone e configuração

```bash
mkdir -p /opt && cd /opt
git clone https://github.com/mendesalexandre/nfse-monorepo.git
cd nfse-monorepo

# .env de produção
cp backend/.env.prod.example backend/.env.prod
nano backend/.env.prod
```

**Editar `backend/.env.prod`:**

```env
APP_NAME="NFSe Emissor"
APP_ENV=production
APP_KEY=                      # gerar logo abaixo
APP_DEBUG=false
APP_URL=https://nfse.minhaempresa.com.br
APP_TIMEZONE=America/Cuiaba
APP_LOCALE=pt_BR

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=nfse
DB_USERNAME=nfse
DB_PASSWORD=<senha-forte-32-chars>

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=<senha-forte-32-chars>

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DOMAIN=nfse.minhaempresa.com.br
SANCTUM_STATEFUL_DOMAINS=nfse.minhaempresa.com.br

OPENSSL_CONF=/etc/ssl/openssl-sha1.cnf

# Path do PFX dentro do container
PFX_PATH=/var/www/html/storage/certs/cartorio_sinop.pfx
```

**Gerar senhas:**
```bash
openssl rand -base64 32   # roda 2x — uma pra DB_PASSWORD, outra REDIS_PASSWORD
```

**Editar `docker/nginx/prod.conf`:**
```bash
sed -i 's/SEU_DOMINIO/nfse.minhaempresa.com.br/g' docker/nginx/prod.conf
```

**Editar `docker-compose.prod.yml`** — adicionar bind mount do cert:
```yaml
  app:
    # ...
    volumes:
      - ./backend/storage/certs:/var/www/html/storage/certs:ro
```

---

## 4. Cert PFX do prestador

```bash
mkdir -p backend/storage/certs
chmod 700 backend/storage/certs

# Subir o PFX (do seu workstation, via scp)
# scp /caminho/local/cartorio_sinop.pfx root@VM:/opt/nfse-monorepo/backend/storage/certs/

chmod 600 backend/storage/certs/cartorio_sinop.pfx
```

---

## 5. Build da imagem

```bash
cd /opt/nfse-monorepo
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod build
```

Demora 3-5 min na primeira vez (composer install + extensões PHP).

---

## 6. Build do frontend SPA

```bash
# Como o nginx prod serve os estáticos do Quasar buildado, gera o dist
docker run --rm -v $(pwd)/frontend:/app -w /app node:20-alpine \
  sh -c "npm ci && npx quasar build"

# Resultado: frontend/dist/spa/ (12 MB)
```

---

## 7. Primeiro certificado SSL (Let's Encrypt)

```bash
chmod +x docker/init-ssl.sh
./docker/init-ssl.sh nfse.minhaempresa.com.br seu-email@empresa.com
```

Esse script:
1. Sobe Nginx temporário em :80 com challenge dir
2. Roda `certbot certonly --webroot` pra solicitar cert do Let's Encrypt
3. Cert fica em volume `certbot_certs:/etc/letsencrypt/`
4. Para o nginx temporário
5. Sobe stack completa com HTTPS

**Resultado esperado:**
```
=== Certificado gerado com sucesso! ===
=== Acesse: https://nfse.minhaempresa.com.br ===
```

> **Pré-requisito do Let's Encrypt:** DNS já tem que estar propagado (use `dig nfse.minhaempresa.com.br` ou `host` pra confirmar).

---

## 8. Migrações + seed inicial

```bash
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod exec app \
  php artisan key:generate --force
# Copie o APP_KEY gerado pro .env.prod

docker compose -f docker-compose.prod.yml --env-file backend/.env.prod exec app \
  php artisan migrate --force

# Cria primeiro cliente (cartório Sinop ou outro)
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod exec app \
  php artisan db:seed --class=ClienteCartorioSinopSeeder --force
```

---

## 9. Validação pós-deploy

```bash
# HTTPS responde
curl -I https://nfse.minhaempresa.com.br
# → HTTP/2 200, headers HSTS

# API responde sem auth (deve dar 401)
curl -I https://nfse.minhaempresa.com.br/api/v1/nfse \
  -X POST -H "Content-Type: application/json" -d '{}'
# → HTTP/2 401

# Emitir NFS-e teste
curl -X POST https://nfse.minhaempresa.com.br/api/v1/nfse \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: <api-key-do-cliente>" \
  -d @payload.json

# Frontend
# Abrir https://nfse.minhaempresa.com.br no navegador → deve mostrar SPA Quasar
```

---

## 10. Operação contínua

### Logs
```bash
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod logs -f app
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod logs -f nginx
```

### Backups (banco)
```bash
# Adicionar ao crontab da VM (ex: diário às 03h)
0 3 * * * docker compose -f /opt/nfse-monorepo/docker-compose.prod.yml --env-file /opt/nfse-monorepo/backend/.env.prod exec -T postgres pg_dump -U nfse nfse | gzip > /backup/nfse-$(date +\%Y\%m\%d).sql.gz
```

### Renovação SSL
- Container `certbot` roda `certbot renew` a cada 12h automaticamente
- Cert só renova se faltarem <30 dias pra expirar
- Sem ação manual necessária

### Update do código
```bash
cd /opt/nfse-monorepo
git pull origin master
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod build app
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod up -d
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod exec app php artisan optimize
# Frontend rebuild (se houve mudança)
docker run --rm -v $(pwd)/frontend:/app -w /app node:20-alpine sh -c "npm ci && npx quasar build"
docker compose -f docker-compose.prod.yml --env-file backend/.env.prod restart nginx
```

---

## Portainer (opcional — admin Docker via web)

Acessa `https://nfse.minhaempresa.com.br:9443` (porta exposta no compose).

> **Atenção:** abre porta 9443 no firewall + protege com senha forte. Consider usar VPN ou bind apenas a localhost (`127.0.0.1:9443:9443`) e SSH tunnel pra acessar.

---

## Troubleshooting comum

| Sintoma | Causa | Fix |
|---|---|---|
| `Permission denied` em `storage/logs/` | Bind mount sobrepôs perms do build | `docker compose exec -u root app chown -R www-data:www-data storage bootstrap/cache` |
| `error:03000098:digital envelope routines::invalid digest` | OpenSSL sem legacy provider (rsa-sha1) | Verificar `OPENSSL_CONF` no `.env.prod` (deve apontar pra `/etc/ssl/openssl-sha1.cnf`) |
| `cStat=15` ao emitir NFS-e | Servidor com timezone errado | Confirmar `APP_TIMEZONE=America/Cuiaba` (ou MT do prestador) |
| Certbot falha "Could not validate" | DNS não propagado / firewall :80 fechado | `dig dominio.com.br` deve retornar IP da VM. `ufw allow 80/tcp` |
| Containers reiniciam loop | Senha do Postgres mudou após primeiro up | `docker volume rm nfse-monorepo-prod_postgres_data` (apaga banco) — **só fazer antes do migrate inicial** |

---

## Checklist pós-deploy

- [ ] HTTPS responde com cert válido (verificar em https://www.ssllabs.com/ssltest/)
- [ ] API rejeita sem `X-Api-Key` (401)
- [ ] Frontend abre no navegador
- [ ] Emitir NFS-e teste em homologação SEFIN funciona
- [ ] DANFSe baixa via `/api/v1/danfse/{chave}`
- [ ] Backup do banco rodando no cron
- [ ] Monitoramento (Portainer ou outra ferramenta)
- [ ] Senhas DB/Redis fortes (32+ chars)
- [ ] Cert PFX com perms 600 e dono correto
- [ ] Firewall só permite 80/443 (e 22 SSH)

---

## Referências

- Docker compose dev: [`docker-compose.yml`](docker-compose.yml)
- Docker compose prod: [`docker-compose.prod.yml`](docker-compose.prod.yml)
- Init SSL: [`docker/init-ssl.sh`](docker/init-ssl.sh)
- Backend convenções: [`backend/CLAUDE.md`](backend/CLAUDE.md)
