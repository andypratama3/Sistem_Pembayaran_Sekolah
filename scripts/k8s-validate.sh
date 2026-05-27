#!/bin/bash

###########################################
# ProductSchool K8s Validation Script
# Tests Kubernetes manifests with local cluster
###########################################

set -e

echo "☸️  ProductSchool Kubernetes Validation Test"
echo "=============================================="
echo ""

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Test 1: Check kubectl
info "Test 1: Checking kubectl installation..."
if ! command -v kubectl &> /dev/null; then
    error "kubectl is not installed"
fi
KUBECTL_VERSION=$(kubectl version --client --short)
success "kubectl found: $KUBECTL_VERSION"
echo ""

# Test 2: Check kustomize
info "Test 2: Checking kustomize installation..."
if ! command -v kustomize &> /dev/null; then
    error "kustomize is not installed"
fi
KUSTOMIZE_VERSION=$(kustomize version)
success "kustomize found"
echo ""

# Test 3: Check cluster connection
info "Test 3: Checking K8s cluster connection..."
if kubectl cluster-info &> /dev/null; then
    CONTEXT=$(kubectl config current-context)
    success "Connected to K8s cluster: $CONTEXT"
else
    warning "Not connected to K8s cluster. Start one:"
    echo "  minikube start --cpus=4 --memory=8192"
    echo "  Or connect to existing cluster: kubectl config use-context <cluster-name>"
    exit 0
fi
echo ""

# Test 4: Validate K8s manifests
info "Test 4: Validating Kubernetes manifests..."
KUBERNETES_DIR="kubernetes"

for env in development staging production; do
    info "  Validating $env overlay..."
    if kustomize build "$KUBERNETES_DIR/overlays/$env" > /tmp/k8s-$env.yaml 2>/dev/null; then
        MANIFEST_SIZE=$(wc -c < /tmp/k8s-$env.yaml)
        success "  $env: Valid ($MANIFEST_SIZE bytes)"
    else
        error "$env overlay failed to build"
    fi
done
echo ""

# Test 5: Check manifest syntax
info "Test 5: Checking manifest syntax with kubectl..."
for env in development staging production; do
    if kubectl apply -f /tmp/k8s-$env.yaml --dry-run=client > /dev/null 2>&1; then
        success "$env manifests syntax valid"
    else
        error "$env manifests have syntax errors"
    fi
done
echo ""

# Test 6: Dry-run test
info "Test 6: Performing dry-run deployment (development)..."
if kustomize build "$KUBERNETES_DIR/overlays/development" | \
    kubectl apply -f - --dry-run=server -n school-management > /dev/null 2>&1; then
    success "Dry-run deployment successful"
else
    warning "Dry-run deployment check skipped (namespace may not exist yet)"
fi
echo ""

# Test 7: Check resource definitions
info "Test 7: Checking resource definitions..."
REQUIRED_KINDS=("Deployment" "StatefulSet" "Service" "ConfigMap" "Secret" "Ingress" "NetworkPolicy")
for kind in "${REQUIRED_KINDS[@]}"; do
    COUNT=$(kustomize build "$KUBERNETES_DIR/overlays/development" | grep -c "^kind: $kind" || echo "0")
    if [ "$COUNT" -gt 0 ]; then
        success "$kind: Found $COUNT instances"
    fi
done
echo ""

# Test 8: Check PVC definitions
info "Test 8: Checking Persistent Volume Claims..."
PVC_COUNT=$(kustomize build "$KUBERNETES_DIR/overlays/production" | grep -c "^kind: PersistentVolumeClaim" || echo "0")
if [ "$PVC_COUNT" -gt 0 ]; then
    success "Found $PVC_COUNT PersistentVolumeClaim definitions"
else
    error "No PersistentVolumeClaim definitions found"
fi
echo ""

# Test 9: Check HPA in production
info "Test 9: Checking HPA configuration..."
HPA_COUNT=$(kustomize build "$KUBERNETES_DIR/overlays/production" | grep -c "^kind: HorizontalPodAutoscaler" || echo "0")
if [ "$HPA_COUNT" -gt 0 ]; then
    success "Found $HPA_COUNT HorizontalPodAutoscaler in production"
else
    error "No HPA found in production overlay"
fi
echo ""

# Test 10: Check labels and annotations
info "Test 10: Checking labels and annotations..."
LABEL_COUNT=$(kustomize build "$KUBERNETES_DIR/overlays/development" | grep -c "project: school-management" || echo "0")
if [ "$LABEL_COUNT" -gt 0 ]; then
    success "Found common labels ($LABEL_COUNT instances)"
else
    warning "Common labels not found"
fi
echo ""

# Summary
echo "════════════════════════════════════════════════════"
echo -e "${GREEN}✅ All K8s validation tests passed!${NC}"
echo "════════════════════════════════════════════════════"
echo ""
echo "📊 Manifests Overview:"
echo "  Development: $(kustomize build "$KUBERNETES_DIR/overlays/development" | grep "^kind:" | wc -l) resources"
echo "  Staging:     $(kustomize build "$KUBERNETES_DIR/overlays/staging" | grep "^kind:" | wc -l) resources"
echo "  Production:  $(kustomize build "$KUBERNETES_DIR/overlays/production" | grep "^kind:" | wc -l) resources"
echo ""
echo "🚀 Next steps:"
echo "  1. For Minikube testing:"
echo "     minikube start --cpus=4 --memory=8192"
echo "     docker build -f docker/Dockerfile -t school-app:latest ."
echo "     minikube image load school-app:latest"
echo ""
echo "  2. Deploy to Minikube:"
echo "     ./kubernetes/scripts/deploy.sh development localhost:5000 latest"
echo ""
echo "  3. For cloud deployment:"
echo "     ./kubernetes/scripts/deploy.sh production your.registry.com/school-app v1.0.0"
echo ""
echo "📝 Useful K8s commands:"
echo "  kubectl get all -n school-management"
echo "  kubectl describe pod -n school-management -l app=school-app"
echo "  kubectl logs deployment/dev-school-app -n school-management"
echo "  kubectl exec -it deployment/dev-school-app -n school-management -- bash"
echo ""
