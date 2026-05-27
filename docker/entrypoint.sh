#!/bin/sh
set -e

echo ""
echo "============================================"
echo "  School Management System — Laravel Boot"
echo "============================================"

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fungsi untuk log
log_info() {
    echo -e "${GREEN}✓${NC} $1"
}

log_error() {
    echo -e "${RED}✗${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# ============================================
# 1. WAIT FOR REDIS (dengan password)
# ============================================
echo ""
echo "📡 Menunggu Redis..."
RETRIES=30
REDIS_READY=0

while [ $RETRIES -gt 0 ]; do
    if [ -n "$REDIS_PASSWORD" ] && [ "$REDIS_PASSWORD" != "null" ]; then
        # Redis dengan password
        if php -r "try { \$redis = new Redis(); \$redis->connect('redis', 6379); \$redis->auth('$REDIS_PASSWORD'); \$redis->ping(); exit(0); } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
            REDIS_READY=1
            break
        fi
    else
        # Redis tanpa password
        if php -r "try { \$redis = new Redis(); \$redis->connect('redis', 6379); \$redis->ping(); exit(0); } catch(Exception \$e) { exit(1); }" 2>/dev/null; then
            REDIS_READY=1
            break
        fi
    fi
    echo "   Redis belum siap, coba lagi... ($RETRIES)"
    RETRIES=$((RETRIES - 1))
    sleep 3
done

if [ $REDIS_READY -eq 0 ]; then
    log_error "Gagal terhubung ke Redis"
    exit 1
fi
log_info "Redis siap"

# ============================================
# 2. WAIT FOR DATABASE
# ============================================
echo ""
echo "🗄️  Menunggu database..."
RETRIES=30
DB_READY=0

while [ $RETRIES -gt 0 ]; do
    if php artisan db:show > /dev/null 2>&1; then
        DB_READY=1
        break
    fi
    echo "   Database belum siap, coba lagi... ($RETRIES)"
    RETRIES=$((RETRIES - 1))
    sleep 3
done

if [ $DB_READY -eq 0 ]; then
    log_error "Gagal terhubung ke database"
    exit 1
fi
log_info "Database terhubung"

# ============================================
# 3. GENERATE APP KEY
# ============================================
echo ""
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    log_warning "APP_KEY belum ada, generating..."
    php artisan key:generate --force
    log_info "APP_KEY berhasil digenerate"
else
    log_info "APP_KEY sudah ada"
fi

# ============================================
# 4. RUN MIGRATIONS (hanya jika bukan production atau force)
# ============================================
echo ""
if [ "$APP_ENV" != "production" ] || [ "$FORCE_MIGRATE" = "true" ]; then
    log_info "Menjalankan migrasi..."
    php artisan migrate --force
    log_info "Migrasi selesai"
else
    log_warning "Skip migrasi di production (set FORCE_MIGRATE=true untuk override)"
fi

# ============================================
# 5. OPTIMIZE FOR PRODUCTION
# ============================================
echo ""
if [ "$APP_ENV" = "production" ]; then
    log_info "Mode production - mengoptimalkan..."
    php artisan optimize
    log_info "Optimasi selesai"
else
    log_info "Mode development - skip optimasi"
    # Clear cache di development
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    php artisan event:clear
fi

# ============================================
# 6. STORAGE LINK
# ============================================
echo ""
php artisan storage:link 2>/dev/null || true
log_info "Storage link siap"

# ============================================
# 7. SET PERMISSIONS
# ============================================
echo ""
# Pastikan direktori storage writable
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true
log_info "Permissions sudah diset"

# ============================================
# 8. HEALTH CHECK
# ============================================
echo ""
if php artisan about > /dev/null 2>&1; then
    log_info "Aplikasi dalam keadaan sehat"
else
    log_warning "Aplikasi mungkin bermasalah"
fi

echo ""
echo "============================================"
echo -e "${GREEN}🚀 Aplikasi siap!${NC}"
echo "============================================"
echo "Environment: ${APP_ENV:-local}"
echo "Debug mode: ${APP_DEBUG:-false}"
echo "URL: ${APP_URL:-http://localhost}"
echo "============================================"
echo ""

# ============================================
# 9. RUN COMMAND
# ============================================
if [ $# -eq 0 ]; then
    # Tidak ada command, jalankan php-fpm
    log_info "Menjalankan php-fpm..."
    exec php-fpm
else
    # Ada command, jalankan command tersebut
    log_info "Menjalankan command: $@"
    exec "$@"
fi
