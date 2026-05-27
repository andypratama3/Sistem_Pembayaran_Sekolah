#!/bin/bash

echo "🔍 Monitoring Stack Validation"
echo "=============================="
echo ""

ERRORS=0

# Check directory structure
echo "Checking directory structure..."
REQUIRED_DIRS=(
    "kubernetes/monitoring/base/prometheus"
    "kubernetes/monitoring/base/grafana"
    "kubernetes/monitoring/base/loki"
    "kubernetes/monitoring/base/promtail"
    "kubernetes/monitoring/base/ingress"
    "kubernetes/monitoring/overlays/development"
    "kubernetes/monitoring/overlays/staging"
    "kubernetes/monitoring/overlays/production"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "  ✅ $dir"
    else
        echo "  ❌ $dir MISSING"
        ((ERRORS++))
    fi
done
echo ""

# Check YAML files
echo "Checking YAML manifests..."
EXPECTED_FILES=(
    "kubernetes/monitoring/base/namespace.yaml"
    "kubernetes/monitoring/base/prometheus/prometheus-configmap.yaml"
    "kubernetes/monitoring/base/prometheus/prometheus-deployment.yaml"
    "kubernetes/monitoring/base/prometheus/prometheus-service.yaml"
    "kubernetes/monitoring/base/grafana/grafana-configmap.yaml"
    "kubernetes/monitoring/base/grafana/grafana-datasources.yaml"
    "kubernetes/monitoring/base/grafana/grafana-deployment.yaml"
    "kubernetes/monitoring/base/grafana/grafana-service.yaml"
    "kubernetes/monitoring/base/loki/loki-configmap.yaml"
    "kubernetes/monitoring/base/loki/loki-deployment.yaml"
    "kubernetes/monitoring/base/loki/loki-service.yaml"
    "kubernetes/monitoring/base/promtail/promtail-configmap.yaml"
    "kubernetes/monitoring/base/promtail/promtail-daemonset.yaml"
    "kubernetes/monitoring/base/promtail/promtail-rbac.yaml"
    "kubernetes/monitoring/base/ingress/grafana-ingress.yaml"
)

for file in "${EXPECTED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $(basename $file)"
    else
        echo "  ❌ $(basename $file) MISSING"
        ((ERRORS++))
    fi
done
echo ""

# Check kustomization files
echo "Checking Kustomization files..."
KUSTOMIZE_FILES=(
    "kubernetes/monitoring/base/kustomization.yaml"
    "kubernetes/monitoring/base/prometheus/kustomization.yaml"
    "kubernetes/monitoring/base/grafana/kustomization.yaml"
    "kubernetes/monitoring/base/loki/kustomization.yaml"
    "kubernetes/monitoring/base/promtail/kustomization.yaml"
    "kubernetes/monitoring/base/ingress/kustomization.yaml"
    "kubernetes/monitoring/overlays/development/kustomization.yaml"
    "kubernetes/monitoring/overlays/staging/kustomization.yaml"
    "kubernetes/monitoring/overlays/production/kustomization.yaml"
)

for file in "${KUSTOMIZE_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $(basename $(dirname $file))/kustomization.yaml"
    else
        echo "  ❌ $(basename $(dirname $file))/kustomization.yaml MISSING"
        ((ERRORS++))
    fi
done
echo ""

# Check documentation
echo "Checking documentation..."
DOC_FILES=(
    "kubernetes/monitoring/README.md"
    "kubernetes/monitoring/QUICK_REFERENCE.md"
    "kubernetes/monitoring/deploy-monitoring.sh"
)

for file in "${DOC_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $(basename $file)"
    else
        echo "  ❌ $(basename $file) MISSING"
        ((ERRORS++))
    fi
done
echo ""

# Validate Kustomize builds
if command -v kustomize &> /dev/null; then
    echo "Validating Kustomize builds..."
    
    for env in development staging production; do
        if kustomize build kubernetes/monitoring/overlays/$env > /dev/null 2>&1; then
            echo "  ✅ $env overlay builds successfully"
        else
            echo "  ❌ $env overlay build FAILED"
            ((ERRORS++))
        fi
    done
    echo ""
else
    echo "⚠️  kustomize not installed - skipping build validation"
    echo ""
fi

# Summary
echo "════════════════════════════════════════"
if [ $ERRORS -eq 0 ]; then
    echo "✅ All validations passed! Monitoring stack is ready."
    echo ""
    echo "Next steps:"
    echo "  1. Deploy: bash kubernetes/monitoring/deploy-monitoring.sh development"
    echo "  2. Verify: kubectl get pods -n monitoring"
    echo "  3. Access: kubectl port-forward svc/grafana 3000:3000 -n monitoring"
    exit 0
else
    echo "❌ $ERRORS validation(s) failed. Please fix issues above."
    exit 1
fi
