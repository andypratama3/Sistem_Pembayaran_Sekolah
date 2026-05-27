#!/bin/bash

###########################################
# ProductSchool - Project Restructure Script
# Moves Laravel files to src/ and Docker to docker/
###########################################

set -e  # Exit on error

echo "🔧 Starting ProductSchool project restructure..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

error() {
    echo -e "${RED}❌ Error: $1${NC}" >&2
    exit 1
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

cd "$PROJECT_ROOT"

# Step 1: Check if src/ already exists
if [ -d "src" ]; then
    error "src/ directory already exists. Please backup or remove it first."
fi

# Step 2: Create src/ directory
info "Creating src/ directory..."
mkdir -p src
success "src/ directory created"
echo ""

# Step 3: Laravel core files and directories to move
LARAVEL_DIRS=(
    "app"
    "bootstrap"
    "config"
    "database"
    "public"
    "resources"
    "routes"
    "storage"
    "tests"
)

LARAVEL_FILES=(
    ".env"
    ".env.example"
    ".env.testing"
    "artisan"
    "composer.json"
    "composer.lock"
    "package.json"
    "package-lock.json"
    "phpunit.xml"
    "vite.config.js"
    "tailwind.config.js"
    "phpstan-baseline.neon"
    ".gitignore-laravel"
)

# Step 4: Move Laravel directories
info "Moving Laravel directories to src/..."
for dir in "${LARAVEL_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "  Moving $dir..."
        mv "$dir" "src/$dir"
    fi
done
success "Laravel directories moved"
echo ""

# Step 5: Move Laravel files
info "Moving Laravel files to src/..."
for file in "${LARAVEL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  Moving $file..."
        mv "$file" "src/$file" 2>/dev/null || true
    fi
done
success "Laravel files moved"
echo ""

# Step 6: Move vendor directory if exists
if [ -d "vendor" ]; then
    info "Moving vendor/ directory..."
    mv vendor "src/vendor"
    success "vendor/ moved"
    echo ""
fi

# Step 7: Move node_modules if exists
if [ -d "node_modules" ]; then
    info "Moving node_modules/ directory..."
    mv node_modules "src/node_modules"
    success "node_modules/ moved"
    echo ""
fi

# Step 8: Ensure docker/ folder exists and structure
info "Setting up Docker directory structure..."
if [ ! -d "docker" ]; then
    mkdir -p docker/php
    mkdir -p docker/nginx/conf.d
    mkdir -p docker/mysql
    mkdir -p docker/ssl

    # Move existing Docker files if present
    [ -f "Dockerfile" ] && mv Dockerfile docker/Dockerfile
    [ -f "docker-entrypoint.sh" ] && mv docker-entrypoint.sh docker/entrypoint.sh
    [ -d "docker" ] && [ ! -d "docker/nginx/conf.d" ] && mkdir -p docker/nginx/conf.d
    [ -d "docker" ] && [ ! -d "docker/php" ] && mkdir -p docker/php
    [ -d "docker" ] && [ ! -d "docker/mysql" ] && mkdir -p docker/mysql
else
    info "docker/ directory already exists"
    # Still move loose Docker files if they exist in root
    [ -f "Dockerfile" ] && mv Dockerfile docker/Dockerfile 2>/dev/null || true
    [ -f "docker-entrypoint.sh" ] && mv docker-entrypoint.sh docker/entrypoint.sh 2>/dev/null || true
fi
success "Docker directory structure created/verified"
echo ""

# Step 9: Update docker-compose.yml location
info "Setting up Docker Compose..."
if [ -f "docker-compose.yml" ]; then
    echo "  Moving docker-compose.yml to docker/..."
    mv docker-compose.yml docker/docker-compose.yml

    # Create backward-compatible symlink
    echo "  Creating symlink in root for backward compatibility..."
    ln -s docker/docker-compose.yml docker-compose.yml

    success "Docker Compose updated at docker/docker-compose.yml"
else
    info "docker-compose.yml not found in root"
fi
echo ""

# Step 10: Create backward-compatible symlinks for Dockerfile
info "Creating backward-compatibility symlinks..."
if [ -f "docker/Dockerfile" ] && [ ! -f "Dockerfile" ]; then
    ln -s docker/Dockerfile Dockerfile
    success "Dockerfile symlink created"
fi

if [ -f "docker/entrypoint.sh" ] && [ ! -f "docker-entrypoint.sh" ]; then
    ln -s docker/entrypoint.sh docker-entrypoint.sh
    success "docker-entrypoint.sh symlink created"
fi
echo ""

# Step 11: Create kubernetes directory structure
info "Creating Kubernetes directory structure..."
mkdir -p kubernetes/base/{app,mysql,redis,ingress,rbac,network-policies,monitoring}
mkdir -p kubernetes/overlays/{development,staging,production}
mkdir -p kubernetes/scripts
success "Kubernetes directories created"
echo ""

# Step 12: Create scripts directory structure
info "Creating scripts directory structure..."
mkdir -p scripts
success "scripts directory verified"
echo ""

# Step 13: Update .gitignore
info "Updating .gitignore..."
cat >> .gitignore << 'EOF'

# Docker
docker/.env
docker/.env.local
docker/ssl/*.crt
docker/ssl/*.key
docker/ssl/*.pem

# Kubernetes
kubernetes/secrets/
kubernetes/overlays/*/secrets/
.kube/

# IDEs
.vscode/
.idea/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Build artifacts
dist/
build/
EOF
success ".gitignore updated"
echo ""

# Step 14: Create post-migration verification checklist
info "Creating post-migration checklist..."
cat > scripts/post-migration-check.sh << 'EOF'
#!/bin/bash

set -e

echo "🔍 Post-Migration Verification Checklist"
echo "========================================"
echo ""

errors=0

# Check 1: src/ structure
echo "✓ Checking src/ directory structure..."
required_dirs=("src/app" "src/bootstrap" "src/config" "src/database" "src/public" "src/resources" "src/routes" "src/storage")
for dir in "${required_dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        echo "  ❌ Missing: $dir"
        ((errors++))
    fi
done

# Check 2: docker/ structure
echo "✓ Checking docker/ directory structure..."
required_docker_dirs=("docker/php" "docker/nginx" "docker/mysql")
for dir in "${required_docker_dirs[@]}"; do
    if [ ! -d "$dir" ]; then
        echo "  ❌ Missing: $dir"
        ((errors++))
    fi
done

# Check 3: Essential files
echo "✓ Checking essential files..."
required_files=("src/composer.json" "src/artisan" "docker/Dockerfile")
for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo "  ❌ Missing: $file"
        ((errors++))
    fi
done

# Check 4: Symlinks
echo "✓ Checking backward-compatibility symlinks..."
if [ -L "docker-compose.yml" ]; then
    echo "  ✅ docker-compose.yml symlink exists"
else
    echo "  ❌ docker-compose.yml symlink missing"
    ((errors++))
fi

if [ -L "Dockerfile" ]; then
    echo "  ✅ Dockerfile symlink exists"
else
    echo "  ❌ Dockerfile symlink missing"
    ((errors++))
fi

echo ""
if [ $errors -eq 0 ]; then
    echo "✅ All migration checks passed!"
    exit 0
else
    echo "❌ $errors issues found. Please review and fix."
    exit 1
fi
EOF

chmod +x scripts/post-migration-check.sh
success "Post-migration check script created"
echo ""

# Step 15: Summary
echo "════════════════════════════════════════════════════"
echo -e "${GREEN}✅ Project restructure completed successfully!${NC}"
echo "════════════════════════════════════════════════════"
echo ""
echo "📋 Summary of changes:"
echo "  • Laravel files moved to src/"
echo "  • Docker files organized in docker/"
echo "  • Kubernetes directories created at kubernetes/"
echo "  • Backward-compatibility symlinks created"
echo "  • Helper scripts created in scripts/"
echo ""
echo "🚀 Next steps:"
echo "  1. Run: ./scripts/post-migration-check.sh"
echo "  2. Update docker/Dockerfile paths (if needed)"
echo "  3. Update docker/docker-compose.yml (if needed)"
echo "  4. Test with: docker-compose up -d"
echo ""
echo "📝 Note: Verify the following manually:"
echo "  • Check src/.env and src/.env.example"
echo "  • Verify docker/docker-compose.yml path configurations"
echo "  • Review docker/Dockerfile WORKDIR and COPY statements"
echo ""
