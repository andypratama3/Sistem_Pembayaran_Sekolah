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
