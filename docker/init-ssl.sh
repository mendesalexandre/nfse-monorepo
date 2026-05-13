#!/bin/bash
# =============================================================================
# Primeiro certificado SSL com Certbot
# Uso: ./docker/init-ssl.sh seu-dominio.com.br seu@email.com
# =============================================================================
set -e

DOMAIN=${1:?"Uso: $0 <dominio> <email>"}
EMAIL=${2:?"Uso: $0 <dominio> <email>"}

echo "=== Gerando certificado SSL para ${DOMAIN} ==="

# 1. Sobe apenas o Nginx com config temporaria (HTTP only)
#    Para o Certbot validar via HTTP-01 challenge
echo ">>> Subindo Nginx temporario (HTTP)..."

# Cria config temporaria so com HTTP e challenge
cat > /tmp/init-ssl-nginx.conf <<EOF
server {
    listen 80;
    server_name ${DOMAIN};

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    location / {
        return 200 'aguardando SSL...';
        add_header Content-Type text/plain;
    }
}
EOF

# Sobe Nginx com config temporaria
docker compose -f docker-compose.prod.yml run -d --rm \
  --name certbot_nginx_temp \
  -p 80:80 \
  -v /tmp/init-ssl-nginx.conf:/etc/nginx/conf.d/default.conf:ro \
  -v certbot_www:/var/www/certbot \
  nginx

# 2. Pede o certificado ao Let's Encrypt
echo ">>> Solicitando certificado ao Let's Encrypt..."
docker compose -f docker-compose.prod.yml run --rm certbot \
  certbot certonly \
  --webroot \
  -w /var/www/certbot \
  -d "${DOMAIN}" \
  --email "${EMAIL}" \
  --agree-tos \
  --no-eff-email \
  --force-renewal

# 3. Para o Nginx temporario
echo ">>> Parando Nginx temporario..."
docker stop certbot_nginx_temp 2>/dev/null || true

# 4. Sobe tudo com a config real (HTTPS)
echo ">>> Subindo stack completa com HTTPS..."
docker compose -f docker-compose.prod.yml up -d

echo ""
echo "=== Certificado gerado com sucesso! ==="
echo "=== Acesse: https://${DOMAIN} ==="
echo "=== Portainer: https://${DOMAIN}:9443 ==="
