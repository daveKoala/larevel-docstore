#!/bin/bash

# Order API Test Script
# This script tests the Order API endpoints

TOKEN="2|mzr5yJ8BzR86NTdYoGpC0gQaJn2uAEC8fYIJxuZOd88c810f"
BASE_URL="http://localhost:8001/api"
PROJECT_GUID="829581953890297445"  # Rocket Skates Development

echo "================================"
echo "Testing Order API Endpoints"
echo "================================"
echo ""

# Test 1: Create an Order
echo "1. Creating an order..."
CREATE_RESPONSE=$(curl -s -X POST "${BASE_URL}/orders" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"project_guid\":\"${PROJECT_GUID}\",\"details\":\"Test order created via API\"}")

echo "$CREATE_RESPONSE" | jq '.'
ORDER_GUID=$(echo "$CREATE_RESPONSE" | jq -r '.data.guid')
echo "Created order with GUID: $ORDER_GUID"
echo ""

# Test 2: List Orders
echo "2. Listing all orders..."
curl -s -X GET "${BASE_URL}/orders" \
  -H "Authorization: Bearer ${TOKEN}" | jq '.'
echo ""

# Test 3: Get Single Order
echo "3. Getting single order by GUID..."
curl -s -X GET "${BASE_URL}/orders/${ORDER_GUID}" \
  -H "Authorization: Bearer ${TOKEN}" | jq '.'
echo ""

# Test 4: Try to update (should return not implemented)
echo "4. Attempting to update order..."
curl -s -X PUT "${BASE_URL}/orders/${ORDER_GUID}" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"details\":\"Updated details\"}" | jq '.'
echo ""

# Test 5: Try to delete (should return not implemented)
echo "5. Attempting to delete order..."
curl -s -X DELETE "${BASE_URL}/orders/${ORDER_GUID}" \
  -H "Authorization: Bearer ${TOKEN}" | jq '.'
echo ""

echo "================================"
echo "Tests Complete!"
echo "================================"
