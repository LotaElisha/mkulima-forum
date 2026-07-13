#!/usr/bin/env bash
# Post-deploy smoke test for Mkulima Forum.
# Usage: ./scripts/smoke.sh [BASE_URL]   (default: http://localhost:8000)
# Exits non-zero on the first failure. Read-only — safe to run in production.
set -u

BASE="${1:-http://localhost:8000}"
PASS=0
FAIL=0

check() {
  local name="$1" url="$2" expect="${3:-200}" needle="${4:-}"
  local body code
  body=$(curl -s -m 15 -w '\n%{http_code}' "$url")
  code=$(echo "$body" | tail -1)
  body=$(echo "$body" | sed '$d')

  if [ "$code" != "$expect" ]; then
    echo "✗ $name — expected HTTP $expect, got $code ($url)"
    FAIL=$((FAIL+1))
    return
  fi
  if [ -n "$needle" ] && ! echo "$body" | grep -q "$needle"; then
    echo "✗ $name — HTTP $code but response missing '$needle'"
    FAIL=$((FAIL+1))
    return
  fi
  echo "✓ $name"
  PASS=$((PASS+1))
}

echo "== Mkulima Forum smoke test → $BASE =="

# Core
check "API health"              "$BASE/api/health"                          200 '"status":"ok"'
check "Landing page"            "$BASE/"                                    200 "AI Plant Scanner"

# Public modules (all must answer honestly, never 500)
check "Weather report"          "$BASE/api/weather/report?location=Dodoma"  200 '"available"'
check "Market prices"           "$BASE/api/market-prices"                   200 '"data"'
check "Market price filters"    "$BASE/api/market-prices/filters"           200 '"commodities"'
check "Global search"           "$BASE/api/search?q=mahindi"                200 '"results"'
check "Forum categories"        "$BASE/api/forum/categories"                200
check "Forum threads"           "$BASE/api/forum/threads"                   200
check "Marketplace products"    "$BASE/api/marketplace/products"            200
check "Marketplace categories"  "$BASE/api/marketplace/categories"          200
check "Input registry lookup"   "$BASE/api/inputs/verify?q=test"            200 '"registry_count"'
check "Counterfeit alerts"      "$BASE/api/inputs/alerts"                   200 '"data"'
check "Input checklist"         "$BASE/api/inputs/checklist"                200 '"items"'
check "Feature flags status"    "$BASE/api/features/status"                 200
check "Service providers"       "$BASE/api/services/providers"              200
check "Warehouses"              "$BASE/api/warehouses"                      200
check "Transporters"            "$BASE/api/logistics/transporters"          200

# Auth boundaries (must reject, not error)
check "Protected route rejects guests" "$BASE/api/payments/escrows"         401
check "Admin route rejects guests"     "$BASE/api/admin/dashboard"          401
check "Removed fake escrow API is gone" "$BASE/api/escrow/my-escrows"       404

echo "== $PASS passed, $FAIL failed =="
[ "$FAIL" -eq 0 ] || exit 1
