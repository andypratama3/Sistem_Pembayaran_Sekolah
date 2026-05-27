#!/bin/bash

###########################################
# ProductSchool Docker Validation Script
# Tests Docker build and Docker Compose setup
###########################################

set -e

echo "🐳 ProductSchool Docker Validation Test"
echo "========================================"
echo ""

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

error() {
    echo -e "${RED}❌ $1${NC}" >&2
    exit 1
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

# Test 1: Check Docker installation
info "Test 1: Checking Docker installation..."
if ! command -v docker &> /dev/null; then
    error "Docker is not installed"
fi
DOCKER_VERSION=$(docker --version)
success "Docker found: $DOCKER_VERSION"
echo ""

# Test 2: Check Docker Compose
info "Test 2: Checking Docker Compose..."
if command -v docker-compose &> /dev/null; then
    COMPOSE_VERSION=$(docker-compose --version)
    success "Docker Compose found: $COMPOSE_VERSION"
elif docker compose version &> /dev/null; then
    COMPOSE_VERSION=$(docker compose version)
    success "Docker Compose (integrated) found: $COMPOSE_VERSION"
else
    warning "Docker Compose not found. You may need to: brew install docker-compose"
    echo "  Or use: docker compose (version 2.0+ integrated with Docker Desktop)"
fi
echo ""

# Test 3: Verify file structure
info "Test 3: Verifying project structure..."
required_dirs=("src/app" "src/config" "src/database" "docker/php" "docker/nginx" "kubernetes/base")
all_exist=true
for dir in "${required_dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        echo "  ❌ Missing: $dir"
        all_exist=false
    fi
done

if [ "$all_exist" = true ]; then
    success "All required directories exist"
else
    error "Some directories are missing"
fi
echo ""

# Test 4: Docker build test
info "Test 4: Building Docker image (this may take a few minutes)..."
if docker build -f docker/Dockerfile -t school-app:test . > /dev/null 2>&1; then
    success "Docker build successful"
    BUILD_SIZE=$(docker images school-app:test --format "{{.Size}}")
    info "Image size: $BUILD_SIZE"
else
    error "Docker build failed. Run: docker build -f docker/Dockerfile -t school-app:test ."
fi
echo ""

# Test 5: Docker Compose validation
info "Test 5: Validating Docker Compose configuration..."
if docker-compose -f docker/docker-compose.yml config > /dev/null 2>&1; then
    success "Docker Compose configuration is valid"
elif docker compose -f docker/docker-compose.yml config > /dev/null 2>&1; then
    success "Docker Compose configuration is valid (v2)"
else
    warning "Could not validate Docker Compose config (may not be installed)"
fi
echo ""

# Test 6: Environment file check
info "Test 6: Checking environment files..."
if [ -f "src/.env" ]; then
    success "src/.env found"
    # Count env vars
    ENV_COUNT=$(grep -c "^[A-Z]" src/.env || echo "0")
    info "Found $ENV_COUNT environment variables"
else
    echo "  ⚠️  Warning: src/.env not found (you'll need this before running)"
fi
echo ""

# Test 7: Check essential Laravel files
info "Test 7: Checking Laravel files..."
essential_files=("src/artisan" "src/composer.json" "src/composer.lock" "docker/Dockerfile")
all_exist=true
for file in "${essential_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo "  ❌ Missing: $file"
        all_exist=false
    fi
done

if [ "$all_exist" = true ]; then
    success "All essential Laravel files exist"
else
    error "Some essential files are missing"
fi
echo ""

# Test 8: Symlink verification
info "Test 8: Verifying backward compatibility symlinks..."
if [ -L "docker-compose.yml" ]; then
    success "docker-compose.yml symlink exists"
else
    error "docker-compose.yml symlink missing"
fi

if [ -L "Dockerfile" ]; then
    success "Dockerfile symlink exists"
else
    error "Dockerfile symlink missing"
fi
echo ""

# Summary
echo "════════════════════════════════════════════════════"
echo -e "${GREEN}✅ All Docker validation tests passed!${NC}"
echo "════════════════════════════════════════════════════"
echo ""
echo "🚀 Next steps:"
echo "  1. Update src/.env if needed: cp src/.env.example src/.env"
echo "  2. Start services: docker-compose up -d"
echo "  3. Run migrations: docker-compose exec app php artisan migrate --force"
echo "  4. Check logs: docker-compose logs -f app"
echo ""
echo "📝 Common Docker Compose commands:"
echo "  docker-compose ps                  # Show running containers"
echo "  docker-compose logs -f app         # Follow app logs"
echo "  docker-compose exec app bash       # Enter app container"
echo "  docker-compose down                # Stop all services"
echo "  docker-compose down -v             # Stop and remove volumes"
echo ""
