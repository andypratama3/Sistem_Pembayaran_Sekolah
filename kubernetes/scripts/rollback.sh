#!/bin/bash

###########################################
# ProductSchool Kubernetes Rollback Script
# Rolls back to previous deployment version
###########################################

set -e

ENVIRONMENT=${1:-development}

case $ENVIRONMENT in
    dev|development)
        DEPLOYMENT="dev-school-app"
        ;;
    staging)
        DEPLOYMENT="staging-school-app"
        ;;
    prod|production)
        DEPLOYMENT="prod-school-app"
        ;;
    *)
        echo "Unknown environment: $ENVIRONMENT"
        exit 1
        ;;
esac

NAMESPACE="school-management"

echo "🔄 Rolling back deployment: $DEPLOYMENT"
echo "Namespace: $NAMESPACE"
echo ""

# Show current revision
echo "📜 Rollout history:"
kubectl rollout history deployment/$DEPLOYMENT -n $NAMESPACE
echo ""

# Perform rollback
echo "Rolling back to previous version..."
if kubectl rollout undo deployment/$DEPLOYMENT -n $NAMESPACE; then
    echo "✅ Rollback initiated"
else
    echo "❌ Rollback failed"
    exit 1
fi

# Wait for rollout
echo ""
echo "Waiting for new rollout..."
if kubectl rollout status deployment/$DEPLOYMENT -n $NAMESPACE --timeout=5m; then
    echo "✅ Rollback completed successfully"
    echo ""
    echo "Current pods:"
    kubectl get pods -n $NAMESPACE -l app=school-app
else
    echo "❌ Rollback timed out"
    exit 1
fi
