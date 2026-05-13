.PHONY: up down build shell migrate fresh logs tinker test fix front-shell front-build permissions

# ── DEV ───────────────────────────────────────────────────────────────────────
up:
	docker compose up -d
	@$(MAKE) permissions

down:
	docker compose down

build:
	docker compose up -d --build
	@$(MAKE) permissions

permissions:
	docker compose exec -T app chown -R www-data:www-data storage bootstrap/cache

# ── BACKEND ───────────────────────────────────────────────────────────────────
shell:
	docker compose exec app bash

migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

tinker:
	docker compose exec app php artisan tinker

test:
	docker compose exec app php artisan test

fix:
	docker compose exec app ./vendor/bin/pint

# ── LOGS ──────────────────────────────────────────────────────────────────────
logs:
	docker compose logs -f app

logs-nginx:
	docker compose logs -f nginx

logs-front:
	docker compose logs -f frontend

# ── FRONTEND ──────────────────────────────────────────────────────────────────
front-shell:
	docker compose exec frontend sh

front-build:
	docker compose exec frontend npx quasar build

# ── BANCO ─────────────────────────────────────────────────────────────────────
psql:
	docker compose exec postgres psql -U laravel_docker laravel_docker

# ── LIMPEZA ───────────────────────────────────────────────────────────────────
prune:
	docker system prune -a
