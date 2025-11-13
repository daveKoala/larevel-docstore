#!/bin/bash

# Test script to demonstrate tenant-specific order service customization
# This shows how AcMe Corporation orders get prefixed with [AcMe]

BASE_URL="http://localhost:8001/api"

echo "========================================="
echo "Tenant Order Service Customization Test"
echo "========================================="
echo ""

# Test 1: Login as AcMe user (wile@acme.com)
echo "1. Logging in as AcMe user (wile@acme.com)..."
ACME_RESPONSE=$(curl -s -X POST "${BASE_URL}/login" \
  -H "Content-Type: application/json" \
  -H "X-Tenant-Id: AcMe" \
  -d '{"email":"wile@acme.com","password":"password"}')

ACME_TOKEN=$(echo $ACME_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
echo "✓ AcMe user logged in. Token: ${ACME_TOKEN:0:20}..."
echo ""

# Test 2: Get AcMe project GUID
echo "2. Getting AcMe project GUID..."
ACME_PROJECTS=$(curl -s -X GET "${BASE_URL}/projects" \
  -H "Authorization: Bearer ${ACME_TOKEN}")

ACME_PROJECT_GUID=$(echo $ACME_PROJECTS | grep -o '"guid":"[^"]*"' | head -1 | cut -d'"' -f4)
echo "✓ AcMe Project GUID: ${ACME_PROJECT_GUID}"
echo ""

# Test 3: Create order with AcMe tenant context
echo "3. Creating order for AcMe (should be prefixed with [AcMe])..."
ACME_ORDER=$(curl -s -X POST "${BASE_URL}/orders" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${ACME_TOKEN}" \
  -H "X-Tenant-Id: AcMe" \
  -d "{\"project_guid\":\"${ACME_PROJECT_GUID}\",\"details\":\"Test order for rocket-powered roller skates\"}")

echo "AcMe Order Response:"
echo $ACME_ORDER | jq '.data.details'
echo ""

# Test 4: Login as Beta user
echo "4. Logging in as Beta user (admin@beta.com)..."
BETA_RESPONSE=$(curl -s -X POST "${BASE_URL}/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@beta.com","password":"password"}')

BETA_TOKEN=$(echo $BETA_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
echo "✓ Beta user logged in. Token: ${BETA_TOKEN:0:20}..."
echo ""

# Test 5: Get Beta project GUID
echo "5. Getting Beta project GUID..."
BETA_PROJECTS=$(curl -s -X GET "${BASE_URL}/projects" \
  -H "Authorization: Bearer ${BETA_TOKEN}")

BETA_PROJECT_GUID=$(echo $BETA_PROJECTS | grep -o '"guid":"[^"]*"' | head -1 | cut -d'"' -f4)
echo "✓ Beta Project GUID: ${BETA_PROJECT_GUID}"
echo ""

# Test 6: Create order without tenant-specific customization
echo "6. Creating order for Beta (should NOT have prefix - uses default service)..."
BETA_ORDER=$(curl -s -X POST "${BASE_URL}/orders" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${BETA_TOKEN}" \
  -d "{\"project_guid\":\"${BETA_PROJECT_GUID}\",\"details\":\"Standard order without customization\"}")

echo "Beta Order Response:"
echo $BETA_ORDER | jq '.data.details'
echo ""

echo "========================================="
echo "Summary:"
echo "- AcMe orders: Prefixed with [AcMe] via custom service"
echo "- Beta orders: No prefix (using default service)"
echo "- This demonstrates tenant-specific customization"
echo "  without modifying core code!"
echo "========================================="
