#!/bin/bash

################################################################################
# ProductSchool Docker Complete Fix
# Syncs all credentials, removes old volumes, and rebuilds everything
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}✓${NC} $1"
}

log_error() {
    echo -e "${RED}✗${NC} $1"
}

log_section() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

PROJECT_ROOT="$(pwd)"

################################################################################
# 1. VALIDATE ENVIRONMENT
################################################################################

log_section "Validating Environment"

if [ ! -f "$PROJECT_ROOT/src/.env" ]; then
    log_error "src/.env not found in $PROJECT_ROOT"
    exit 1
fi

if [ ! -f "$PROJECT_ROOT/docker-compose.yml" ]; then
    log_error "docker-compose.yml not found"
    exit 1
fi

log_info "Project structure validated"

################################################################################
# 2. EXTRACT PASSWORDS FROM SRC/.ENV
################################################################################

log_section "Extracting Credentials from src/.env"

DB_PASSWORD=$(grep "^DB_PASSWORD=" "$PROJECT_ROOT/src/.env" | cut -d'=' -f2)
DB_ROOT_PASSWORD=$(grep "^DB_ROOT_PASSWORD=" "$PROJECT_ROOT/src/.env" | cut -d'=' -f2)
REDIS_PASSWORD=$(grep "^REDIS_PASSWORD=" "$PROJECT_ROOT/src/.env" | cut -d'=' -f2)
DB_DATABASE=$(grep "^DB_DATABASE=" "$PROJECT_ROOT/src/.env" | cut -d'=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" "$PROJECT_ROOT/src/.env" | cut -d'=' -f2)
APP_ENV=$(grep "^APP_ENV=" "$PROJECT_ROOT/src/.env" | cut -d'=' -f2)

if [ -z "$DB_PASSWORD" ] || [ -z "$DB_ROOT_PASSWORD" ] || [ -z "$REDIS_PASSWORD" ]; then
    log_error "Missing required credentials in src/.env"
    exit 1
fi

log_info "DB_PASSWORD: ${DB_PASSWORD:0:10}..."
log_info "DB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:0:10}..."
log_info "REDIS_PASSWORD: ${REDIS_PASSWORD:0:10}..."

################################################################################
# 3. STOP ALL CONTAINERS
################################################################################

log_section "Stopping All Containers"

echo "Stopping containers..."
docker compose down --remove-orphans 2>/dev/null || true
sleep 2
log_info "Containers stopped"

################################################################################
# 4. REMOVE ALL VOLUMES (FRESH START)
################################################################################

log_section "Removing Old Data Volumes"

echo "Removing database volume..."
docker volume rm productsschool_db_data 2>/dev/null || true

echo "Removing redis volume..."
docker volume rm productsschool_redis_data 2>/dev/null || true

echo "Removing app storage volume..."
docker volume rm productsschool_app_storage 2>/dev/null || true

log_info "Old volumes removed - database will reinitialize with correct credentials"

################################################################################
# 5. CREATE ROOT .ENV
################################################################################

log_section "Creating Root .env File"

# Remove old .env if exists
if [ -f "$PROJECT_ROOT/.env" ]; then
    rm -f "$PROJECT_ROOT/.env" 2>/dev/null || sudo rm -f "$PROJECT_ROOT/.env" || true
fi

# Create temp file
cat > "/tmp/productschool.env.tmp" << EOF
APP_ENV=$APP_ENV
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD
DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD
REDIS_PASSWORD=$REDIS_PASSWORD
EOF

# Move to destination
if ! mv "/tmp/productschool.env.tmp" "$PROJECT_ROOT/.env" 2>/dev/null; then
    sudo mv "/tmp/productschool.env.tmp" "$PROJECT_ROOT/.env" || {
        log_error "Failed to create .env file"
        exit 1
    }
fi

chmod 644 "$PROJECT_ROOT/.env" 2>/dev/null || sudo chmod 644 "$PROJECT_ROOT/.env" || true

log_info ".env created with credentials matching src/.env"
log_info "File: $PROJECT_ROOT/.env"

################################################################################
# 6. PRUNE DOCKER (CLEAN IMAGE CACHE)
################################################################################

log_section "Cleaning Docker System"

echo "Removing dangling images..."
docker image prune -f 2>/dev/null || true
log_info "Docker cleaned"

################################################################################
# 7. REBUILD IMAGE
################################################################################

log_section "Building Docker Image"

cd "$PROJECT_ROOT"

echo "Building image with fresh code and volumes..."
echo "(This may take 5-10 minutes on first run)"
echo ""

if ! docker compose build --no-cache; then
    log_error "Failed to build Docker image"
    exit 1
fi

log_info "Image built successfully"

################################################################################
# 8. START CONTAINERS
################################################################################

log_section "Starting Services"

echo "Starting containers..."
docker compose up -d

sleep 5

log_info "Containers started - waiting for health checks..."

################################################################################
# 9. WAIT FOR HEALTH
################################################################################

log_section "Waiting for Services"

RETRIES=180
DB_READY=0
APP_READY=0

while [ $RETRIES -gt 0 ]; do
    # Count healthy services
    HEALTHY=$(docker compose ps 2>/dev/null | grep -c "healthy" || echo 0)

    if [ $HEALTHY -ge 4 ]; then
        log_info "All services healthy!"
        DB_READY=1
        APP_READY=1
        break
    fi

    if [ $(((180 - RETRIES) % 20)) -eq 0 ]; then
        echo "   [$HEALTHY/4] services healthy... (${RETRIES}s remaining)"
    fi

    RETRIES=$((RETRIES - 1))
    sleep 1
done

if [ $DB_READY -eq 0 ]; then
    log_error "Services did not become healthy in time"
    echo ""
    echo "Current status:"
    docker compose ps
    echo ""
    echo "Database logs:"
    docker compose logs db | tail -20
    exit 1
fi

################################################################################
# 10. VERIFY CONNECTION
################################################################################

log_section "Verifying Connection"

echo "Testing database access from app..."
if docker compose exec -T app php artisan db:show > /dev/null 2>&1; then
    log_info "✓ Database connection successful!"
else
    log_error "Database connection failed"
    echo ""
    echo "Debug info:"
    docker compose logs app | tail -10
    exit 1
fi

################################################################################
# 11. SHOW STATUS
################################################################################

log_section "Service Status"

docker compose ps

################################################################################
# 12. COMPLETION
################################################################################

log_section "✨ Fix Complete! Ready to Deploy"

echo "✓ All credentials synced between .env and src/.env"
echo "✓ Database reinitialized with correct password"
echo "✓ All containers running and healthy"
echo ""

echo "📋 Next Steps:"
echo ""
echo "1. Run migrations:"
echo "   docker compose exec app php artisan migrate"
echo ""
echo "2. Generate APP_KEY:"
echo "   docker compose exec app php artisan key:generate"
echo ""
echo "3. Access your app:"
echo "   http://localhost"
echo ""

echo "📝 Credentials:"
echo "   MySQL User:  $DB_USERNAME"
echo "   MySQL Pass:  ${DB_PASSWORD:0:10}..."
echo "   MySQL Root:  root / ${DB_ROOT_PASSWORD:0:10}..."
echo "   Redis Pass:  ${REDIS_PASSWORD:0:10}..."
echo ""

echo "📁 Files:"
echo "   Root:        $PROJECT_ROOT/.env"
echo "   App:         $PROJECT_ROOT/src/.env"
echo ""

log_info "Done! Your application is ready."
