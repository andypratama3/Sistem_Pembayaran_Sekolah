#!/bin/bash
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'
BOLD='\033[1m'

log()    { echo -e "${GREEN}✅ $1${NC}"; }
warn()   { echo -e "${YELLOW}⚠️  $1${NC}"; }
error()  { echo -e "${RED}❌ $1${NC}"; exit 1; }
info()   { echo -e "${BLUE}ℹ️  $1${NC}"; }
header() { echo -e "\n${BOLD}${BLUE}══ $1 ══${NC}\n"; }

if [ "$EUID" -ne 0 ]; then
    error "Jalankan dengan sudo: sudo ./set-up.sh"
fi

echo ""
echo -e "${BOLD}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}║   School Management System — Setup Script   ║${NC}"
echo -e "${BOLD}╚══════════════════════════════════════════════╝${NC}"
echo ""

header "Step 1: Konfigurasi .env"

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        warn ".env belum ada, sudah disalin dari .env.example"
        echo ""
        echo -e "${RED}${BOLD}WAJIB: Edit .env sebelum melanjutkan!${NC}"
        echo ""
        echo "  nano .env"
        echo ""
        echo "Minimal isi:"
        echo "  - APP_URL"
        echo "  - DB_PASSWORD"
        echo "  - DB_ROOT_PASSWORD"
        echo ""
        read -p "Sudah edit .env? Tekan ENTER untuk lanjut, atau Ctrl+C untuk batal... "
    else
        error ".env.example tidak ditemukan."
    fi
else
    log ".env sudah ada"
fi

APP_URL=$(grep -E "^APP_URL=" .env | cut -d'=' -f2 | tr -d '"' | tr -d "'")

if [ -z "$APP_URL" ]; then
    read -p "Masukkan domain atau IP server: " APP_URL
fi

info "Domain/IP target: ${APP_URL}"

header "Step 2: Mengecek dependencies"

command -v docker > /dev/null 2>&1 || error "Docker belum terinstall."
command -v docker compose > /dev/null 2>&1 || docker compose version > /dev/null 2>&1 || error "Docker Compose belum terinstall."

log "Docker: $(docker --version)"

header "Step 3: Sertifikat SSL"

mkdir -p ssl

if [ -f "ssl/fullchain.pem" ] && [ -f "ssl/privkey.pem" ]; then
    log "Sertifikat SSL sudah ada di ./ssl/"
else
    IS_IP=false
    if echo "$APP_URL" | grep -qE '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$'; then
        IS_IP=true
    fi

    if [ "$IS_IP" = true ]; then
        warn "APP_URL adalah IP address ($APP_URL), menggunakan self-signed certificate"
        SSL_TYPE="self-signed"
    else
        echo ""
        echo "Pilih tipe SSL:"
        echo "  1) Let's Encrypt (GRATIS, perlu domain valid)"
        echo "  2) Self-signed (untuk testing)"
        echo ""
        read -p "Pilih [1/2]: " SSL_CHOICE

        case "$SSL_CHOICE" in
            1) SSL_TYPE="letsencrypt" ;;
            *) SSL_TYPE="self-signed" ;;
        esac
    fi

    if [ "$SSL_TYPE" = "letsencrypt" ]; then
        apt-get update -qq
        apt-get install -y -qq certbot

        certbot certonly \
            --standalone \
            --non-interactive \
            --agree-tos \
            --register-unsafely-without-email \
            -d "$APP_URL" \
            || error "Certbot gagal. Pastikan domain sudah pointing ke server ini dan port 80 terbuka."

        cp "/etc/letsencrypt/live/${APP_URL}/fullchain.pem" ssl/
        cp "/etc/letsencrypt/live/${APP_URL}/privkey.pem"   ssl/
        chown -R "$SUDO_USER:$SUDO_USER" ssl/ 2>/dev/null || true
        log "Sertifikat Let's Encrypt berhasil"

        CRON_CMD="0 2 1 * * cd $(pwd) && docker compose run --rm certbot renew --quiet 2>&1 | logger -t certbot"
        (crontab -l 2>/dev/null | grep -v "certbot"; echo "$CRON_CMD") | crontab -
        log "Auto-renewal SSL ditambahkan ke crontab"
    else
        openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
            -keyout ssl/privkey.pem \
            -out    ssl/fullchain.pem \
            -subj   "/C=ID/ST=Jakarta/L=Jakarta/O=School/CN=${APP_URL}" \
            -quiet
        chown -R "$SUDO_USER:$SUDO_USER" ssl/ 2>/dev/null || true
        log "Self-signed certificate dibuat (berlaku 10 tahun)"
    fi
fi

header "Step 4: Membuat struktur folder"

mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/uploads
mkdir -p nginx/conf.d

chown -R "$SUDO_USER:$SUDO_USER" storage bootstrap/cache public/uploads 2>/dev/null || true
chmod -R 775 storage bootstrap/cache public/uploads

log "Folder project siap"

header "Step 5: Nginx configuration"

if [ ! -f "nginx/conf.d/default.conf" ]; then
    error "nginx/conf.d/default.conf tidak ada!"
else
    log "nginx/conf.d/default.conf ditemukan"
fi

header "Step 6: Build dan jalankan Docker containers"

info "Proses ini bisa memakan waktu 5-15 menit saat pertama kali..."
echo ""

docker compose up -d --build

log "Containers berjalan"

header "Step 7: Menunggu aplikasi siap"

TIMEOUT=120
ELAPSED=0

while [ $ELAPSED -lt $TIMEOUT ]; do
    STATUS=$(docker inspect --format='{{.State.Health.Status}}' schoolapp_app 2>/dev/null || echo "unknown")
    if [ "$STATUS" = "healthy" ]; then
        break
    fi
    echo -n "."
    sleep 5
    ELAPSED=$((ELAPSED + 5))
done
echo ""

if [ "$STATUS" = "healthy" ]; then
    log "Container app sehat"
else
    warn "Container belum healthy setelah ${TIMEOUT}s. Cek: docker compose logs app"
fi

header "Status Containers"

docker compose ps

echo ""
echo -e "${BOLD}${GREEN}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${GREEN}║          🎉 Setup Selesai!                  ║${NC}"
echo -e "${BOLD}${GREEN}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  🌐 Aplikasi: ${BOLD}https://${APP_URL}${NC}"
echo ""
echo "  docker compose logs -f app         → log Laravel"
echo "  docker compose logs -f             → semua log"
echo "  docker compose ps                  → status"
echo "  docker compose down                → matikan"
echo "  docker compose up -d --build       → update"
echo ""