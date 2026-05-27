#!/bin/bash

###########################################
# ProductSchool Kubernetes Deployment Script
# Deploys to K8s cluster using Kustomize
###########################################

set -e

ENVIRONMENT=${1:-development}
REGISTRY=${2:-docker.io/yourregistry}
IMAGE_TAG=${3:-latest}

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

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

KUBERNETES_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$KUBERNETES_DIR")")"

echo "=================================================="
echo "🚀 ProductSchool Kubernetes Deployment"
echo "=================================================="
echo ""

# Validate environment
case $ENVIRONMENT in
    dev|development)
        OVERLAY_DIR="overlays/development"
        NAMESPACE="school-management"
        ;;
    staging)
        OVERLAY_DIR="overlays/staging"
        NAMESPACE="school-management"
        ;;
    prod|production)
        OVERLAY_DIR="overlays/production"
        NAMESPACE="school-management"
        ;;
    *)
        error "Unknown environment: $ENVIRONMENT. Use: development, staging, or production"
        ;;
esac

info "Environment: $ENVIRONMENT"
info "Namespace: $NAMESPACE"
info "Registry: $REGISTRY"
info "Image Tag: $IMAGE_TAG"
echo ""

# Check prerequisites
info "Checking prerequisites..."

if ! command -v kubectl &> /dev/null; then
    error "kubectl not found. Please install kubectl"
fi
success "kubectl found"

if ! command -v kustomize &> /dev/null; then
    error "kustomize not found. Please install kustomize"
fi
success "kustomize found"

# Verify cluster connection
if ! kubectl cluster-info &> /dev/null; then
    error "Not connected to K8s cluster. Run: kubectl config current-context"
fi
success "Connected to K8s cluster"
echo ""

# Build image (optional - you may push manually)
info "Building and pushing Docker image..."
cd "$PROJECT_ROOT"

FULL_IMAGE="${REGISTRY}/school-app:${IMAGE_TAG}"
if docker build -f docker/Dockerfile -t "$FULL_IMAGE" .; then
    success "Docker image built: $FULL_IMAGE"

    if docker push "$FULL_IMAGE"; then
        success "Docker image pushed to registry"
    fi
else
    error "Docker build failed"
fi
echo ""

# Create namespace if not exists
info "Ensuring namespace exists..."
kubectl create namespace $NAMESPACE --dry-run=client -o yaml | kubectl apply -f - || true
success "Namespace ready: $NAMESPACE"
echo ""

# Apply secrets
info "Applying secrets..."
if cd "$KUBERNETES_DIR" && bash scripts/create-secrets.sh $ENVIRONMENT; then
    success "Secrets applied"
else
    error "Failed to apply secrets"
fi
echo ""

# Deploy with Kustomize
info "Deploying application with Kustomize..."
cd "$KUBERNETES_DIR"

if kustomize build $OVERLAY_DIR | kubectl apply -f -; then
    success "Kustomize deployment applied"
else
    error "Kustomize deployment failed"
fi
echo ""

# Wait for rollout
info "Waiting for deployment rollout..."
if kubectl rollout status deployment/$ENVIRONMENT-school-app -n $NAMESPACE --timeout=5m; then
    success "Deployment rolled out successfully"
else
    error "Deployment rollout failed or timed out"
fi
echo ""

# Show deployment status
info "Deployment Status:"
kubectl get all -n $NAMESPACE
echo ""

# Show service endpoints
info "Service Endpoints:"
kubectl get service -n $NAMESPACE
echo ""

# Get pod details
info "Pod Details:"
kubectl get pods -n $NAMESPACE -o wide
echo ""

echo "=================================================="
echo -e "${GREEN}✅ Deployment Complete!${NC}"
echo "=================================================="
echo ""
echo "Useful commands:"
echo "  kubectl logs -n $NAMESPACE deployment/$ENVIRONMENT-school-app"
echo "  kubectl exec -n $NAMESPACE -it deployment/$ENVIRONMENT-school-app -- bash"
echo "  kubectl describe pod -n $NAMESPACE -l app=school-app"
echo ""
