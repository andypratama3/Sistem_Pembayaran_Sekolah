#!/bin/bash

###########################################
# ProductSchool Kubernetes Secrets Generator
# Creates K8s Secrets from .env file
###########################################

set -e

ENVIRONMENT=${1:-development}
ENV_FILE="./src/.env"

if [ ! -f "$ENV_FILE" ]; then
    echo "Error: $ENV_FILE not found"
    exit 1
fi

# Extract values from .env
APP_KEY=$(grep "^APP_KEY=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
DB_DATABASE=$(grep "^DB_DATABASE=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
DB_USERNAME=$(grep "^DB_USERNAME=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
DB_PASSWORD=$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
DB_ROOT_PASSWORD=$(grep "^DB_ROOT_PASSWORD=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
REDIS_PASSWORD=$(grep "^REDIS_PASSWORD=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
MAIL_USERNAME=$(grep "^MAIL_USERNAME=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
MAIL_PASSWORD=$(grep "^MAIL_PASSWORD=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
MIDTRANS_SERVER_KEY=$(grep "^MIDTRANS_SERVER_KEY=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')
MIDTRANS_CLIENT_KEY=$(grep "^MIDTRANS_CLIENT_KEY=" "$ENV_FILE" | cut -d= -f2- | tr -d ' ')

# Validate required fields
if [ -z "$APP_KEY" ] || [ -z "$DB_PASSWORD" ]; then
    echo "Error: APP_KEY or DB_PASSWORD not found in $ENV_FILE"
    exit 1
fi

# Namespace selection
NAMESPACE="school-management"
case $ENVIRONMENT in
    dev|development)
        SECRET_NAME="dev-app-secrets"
        ;;
    staging)
        SECRET_NAME="staging-app-secrets"
        ;;
    prod|production)
        SECRET_NAME="prod-app-secrets"
        ;;
    *)
        echo "Unknown environment: $ENVIRONMENT"
        echo "Use: development, staging, or production"
        exit 1
        ;;
esac

echo "📝 Creating Kubernetes Secrets for: $ENVIRONMENT"
echo "=================================================="
echo ""

# Create or update secret
kubectl create secret generic $SECRET_NAME \
    --from-literal=APP_KEY="$APP_KEY" \
    --from-literal=DB_DATABASE="$DB_DATABASE" \
    --from-literal=DB_USERNAME="$DB_USERNAME" \
    --from-literal=DB_PASSWORD="$DB_PASSWORD" \
    --from-literal=DB_ROOT_PASSWORD="$DB_ROOT_PASSWORD" \
    --from-literal=REDIS_PASSWORD="$REDIS_PASSWORD" \
    --from-literal=MAIL_USERNAME="$MAIL_USERNAME" \
    --from-literal=MAIL_PASSWORD="$MAIL_PASSWORD" \
    --from-literal=MIDTRANS_SERVER_KEY="$MIDTRANS_SERVER_KEY" \
    --from-literal=MIDTRANS_CLIENT_KEY="$MIDTRANS_CLIENT_KEY" \
    --from-literal=CACHE_REDIS_PASSWORD="$REDIS_PASSWORD" \
    --namespace=$NAMESPACE \
    --dry-run=client -o yaml | kubectl apply -f -

echo "✅ Secrets created successfully in namespace: $NAMESPACE"
echo ""
echo "Secret name: $SECRET_NAME"
echo "Next: Update your Kustomization to reference this secret"
