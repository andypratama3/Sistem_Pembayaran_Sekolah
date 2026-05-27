#!/bin/bash

###########################################
# ProductSchool Monitoring Stack Deployment
# Deploys Prometheus, Grafana, Loki, Promtail
###########################################

set -e

ENVIRONMENT=${1:-development}
NAMESPACE="monitoring"

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

MONITORING_DIR="kubernetes/monitoring"

echo "=================================================="
echo "🚀 ProductSchool Monitoring Stack Deployment"
echo "=================================================="
echo ""

# Validate environment
case $ENVIRONMENT in
    dev|development)
        OVERLAY_DIR="overlays/development"
        ENV_DISPLAY="Development"
        ;;
    staging)
        OVERLAY_DIR="overlays/staging"
        ENV_DISPLAY="Staging"
        ;;
    prod|production)
        OVERLAY_DIR="overlays/production"
        ENV_DISPLAY="Production"
        ;;
    *)
        error "Unknown environment: $ENVIRONMENT. Use: development, staging, or production"
        ;;
esac

info "Environment: $ENV_DISPLAY"
info "Overlay: $OVERLAY_DIR"
echo ""

# Check prerequisites
info "Checking prerequisites..."

if ! command -v kubectl &> /dev/null; then
    error "kubectl not found"
fi
success "kubectl found"

if ! command -v kustomize &> /dev/null; then
    error "kustomize not found"
fi
success "kustomize found"

if ! kubectl cluster-info &> /dev/null; then
    error "Not connected to K8s cluster"
fi
success "Connected to K8s cluster"
echo ""

# Create namespace
info "Creating monitoring namespace..."
kubectl create namespace $NAMESPACE --dry-run=client -o yaml | kubectl apply -f -
success "Namespace ready: $NAMESPACE"
echo ""

# Build monitoring manifests
info "Building monitoring manifests..."
if kustomize build "$MONITORING_DIR/$OVERLAY_DIR" > /tmp/monitoring-manifests.yaml; then
    success "Manifests built successfully"
    RESOURCE_COUNT=$(grep "^kind:" /tmp/monitoring-manifests.yaml | wc -l)
    info "Resources to deploy: $RESOURCE_COUNT"
else
    error "Failed to build manifests"
fi
echo ""

# Validate manifests
info "Validating manifests..."
if kubectl apply -f /tmp/monitoring-manifests.yaml --dry-run=client > /dev/null 2>&1; then
    success "Manifests validation passed"
else
    error "Manifests validation failed"
fi
echo ""

# Apply manifests
info "Deploying monitoring stack..."
if kubectl apply -f /tmp/monitoring-manifests.yaml; then
    success "Manifests applied"
else
    error "Failed to apply manifests"
fi
echo ""

# Wait for Prometheus
info "Waiting for Prometheus deployment..."
if kubectl rollout status deployment/${ENVIRONMENT}-prometheus-monitoring -n $NAMESPACE --timeout=3m 2>/dev/null; then
    success "Prometheus rolled out"
else
    warning "Prometheus rollout status check skipped"
fi

# Wait for Grafana
info "Waiting for Grafana deployment..."
if kubectl rollout status deployment/${ENVIRONMENT}-grafana -n $NAMESPACE --timeout=3m 2>/dev/null; then
    success "Grafana rolled out"
else
    warning "Grafana rollout status check skipped"
fi

# Wait for Loki
info "Waiting for Loki deployment..."
if kubectl rollout status deployment/${ENVIRONMENT}-loki -n $NAMESPACE --timeout=3m 2>/dev/null; then
    success "Loki rolled out"
else
    warning "Loki rollout status check skipped"
fi
echo ""

# Show deployment status
info "📊 Monitoring Stack Status:"
kubectl get pods -n $NAMESPACE -o wide | grep -E "prometheus|grafana|loki|promtail"
echo ""

# Show services
info "📡 Services:"
kubectl get svc -n $NAMESPACE --no-headers
echo ""

# Show ingress
info "🌐 Ingress:"
INGRESS_COUNT=$(kubectl get ing -n $NAMESPACE --no-headers 2>/dev/null | wc -l)
if [ $INGRESS_COUNT -gt 0 ]; then
    kubectl get ing -n $NAMESPACE
else
    echo "  No ingress configured"
fi
echo ""

# Display access information
echo "=================================================="
echo -e "${GREEN}✅ Monitoring Stack Deployed!${NC}"
echo "=================================================="
echo ""
echo "📊 Access Information:"
echo ""
echo "  Prometheus:"
echo "    kubectl port-forward svc/prometheus 9090:9090 -n monitoring"
echo "    Then visit: http://localhost:9090"
echo ""
echo "  Grafana:"
if [ "$ENVIRONMENT" = "development" ] || [ "$ENVIRONMENT" = "dev" ]; then
    echo "    kubectl port-forward svc/grafana 3000:3000 -n monitoring"
    echo "    Then visit: http://localhost:3000"
    echo "    Username: admin"
    echo "    Password: admin123"
else
    echo "    Visit: https://grafana.school.local"
    echo "    (Make sure DNS is configured and TLS cert is valid)"
fi
echo ""
echo "  Loki:"
echo "    kubectl port-forward svc/loki 3100:3100 -n monitoring"
echo "    Then visit: http://localhost:3100"
echo ""

# Show useful commands
echo "📝 Useful Commands:"
echo ""
echo "  View logs:"
echo "    kubectl logs -f deployment/${ENVIRONMENT}-prometheus-monitoring -n monitoring"
echo "    kubectl logs -f deployment/${ENVIRONMENT}-grafana -n monitoring"
echo "    kubectl logs -f deployment/${ENVIRONMENT}-loki -n monitoring"
echo "    kubectl logs -f daemonset/promtail -n monitoring"
echo ""
echo "  Get Grafana password:"
echo "    kubectl get secret grafana-admin-password -n monitoring -o jsonpath='{.data.password}' | base64 -d; echo"
echo ""
echo "  Edit Prometheus config:"
echo "    kubectl edit configmap prometheus-config -n monitoring"
echo ""
echo "  Check PVCs:"
echo "    kubectl get pvc -n monitoring"
echo ""
