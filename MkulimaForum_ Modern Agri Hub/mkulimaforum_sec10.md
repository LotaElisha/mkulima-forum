## 10. API Design — RESTful, Standards, Versioning

### 10.1 API Standards & Conventions

#### 10.1.1 JSON:API First-Party Compliance

MkulimaForum adopts JSON:API as its request/response contract. Laravel 13 provides first-party JSON:API resources [^71^], eliminating third-party dependencies. Sparse fieldsets (`?fields[post]=title,body`) reduce payload size by 40–70% over constrained rural networks. Compound documents (`?include=author,comments`) sideload related resources in a single round trip, eliminating N+1 queries. Cursor pagination replaces offset-based paging for stable ordering. Standard error envelopes return machine-readable `status`, `title`, `detail`, and `source.pointer` fields. Content negotiation enforces `Accept: application/vnd.api+json`; mismatches receive `406 Not Acceptable`.

**Table 10.1 — JSON:API Feature Matrix**

| Feature | Implementation | Benefit |
|---------|---------------|---------|
| Sparse fieldsets | `?fields[entity]=f1,f2` | 40–70% payload reduction [^71^] |
| Compound documents | `?include=rel1,rel2` | Eliminates N+1; single RTT |
| Cursor pagination | `page[after]=cursor` | Stable ordering under concurrent writes |
| Standard errors | `errors: [{ status, title, detail }]` | Machine-readable error handling |
| Content negotiation | `Accept: application/vnd.api+json` | Contract enforcement |

Sparse fieldsets are the highest-impact optimization: a forum thread listing that would require five round trips collapses to one call with `?include=author,comments.author,comments.reactions&fields[thread]=title,body,created_at`.

#### 10.1.2 Versioning

Versions are embedded in the URL path (`/v1/`, `/v2/`) for visibility, complemented by `Accept: application/vnd.api+json; version=1` header negotiation. Deprecation responses carry `Deprecation: true` and `Sunset: <HTTP-date>` headers with a six-month transition window. Within a major version, backward compatibility is preserved — fields may be added but never removed or retyped.

#### 10.1.3 Request/Response Patterns

All responses use the JSON:API envelope (`data`, `links`, `meta`). Status codes are precise: `201 Created`, `204 No Content`, `422 Unprocessable Entity` with per-field errors, and `429 Too Many Requests`. Idempotency keys (`Idempotency-Key: <uuid>`) cache responses for 24 hours, preventing duplicates on unreliable networks. Brotli compression (gzip fallback) reduces JSON payload by 60–80%.

---

### 10.2 Endpoint Organization

#### 10.2.1 Domain-Based Routing

Endpoints are organized by domain module, mirroring the Laravel DDD folder structure. Each domain is served by a dedicated route file and guarded by middleware: public, standard (Sanctum), elevated (Sanctum + verified identity), or admin (role check).

**Table 10.2 — API Endpoint Reference (Selected Key Endpoints)**

| Domain | Path Prefix | Key Endpoints | Access Tier |
|--------|-------------|--------------|-------------|
| Auth | `/v1/auth/*` | `POST /register`, `POST /verify-otp`, `POST /biometric` | Public / Sanctum |
| Marketplace | `/v1/marketplace/*` | `GET /products`, `POST /orders`, `GET /vendors/{id}` | Standard |
| Forum | `/v1/forum/*` | `GET /threads`, `POST /threads`, `POST /reply` | Standard |
| Scanner | `/v1/scanner/*` | `POST /diagnose`, `GET /history` | Standard |
| Services | `/v1/services/*` | `GET /agronomists`, `POST /bookings` | Elevated |
| AI | `/v1/ai/*` | `POST /ask`, `POST /voice-query` | Standard |
| Payments | `/v1/payments/*` | `POST /wallet/deposit`, `POST /callback` | Sanctum + PIN |
| Notifications | `/v1/notifications/*` | `GET /inbox`, `POST /preferences` | Standard |
| Admin | `/v1/admin/*` | `GET /users`, `POST /moderation` | Admin role |

#### 10.2.2 Mobile-Optimized BFF

The mobile BFF transforms responses for low-bandwidth networks. Delta sync via `/sync?since=timestamp` returns only changed records. Batch endpoints accept operation arrays for offline-first conflict resolution. List endpoints default to 20-item pages to keep responses under 50 KB.

#### 10.2.3 Authentication & Security

Sanctum stateful tokens power authentication: 15-minute access tokens with 30-day rotating refresh. Laravel 13 Passkey/WebAuthn [^67^] enables biometric login. Device fingerprinting detects SIM swap attacks by comparing the current device signature against the baseline; mismatches invalidate the session. Rate limiting is tiered: 100 req/min for standard accounts, 500 req/min for premium [^37^].

---

### 10.3 Developer Experience

#### 10.3.1 OpenAPI 3.1

OpenAPI 3.1 [^88^] is auto-generated from Laravel routes, form request validators, and JSON:API resource classes. The spec is served at `/v1/docs/openapi.json` with Swagger UI rendering at `/v1/docs/ui`, driving SDK generation and contract testing. Rate limit visibility is embedded in `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and `X-RateLimit-Reset` headers on every response.

#### 10.3.2 Webhooks

Integrators subscribe to events (`order.created`, `payment.received`) via the developer portal, specifying a URL and event filter. Delivery uses HMAC-SHA256 signatures in `X-Webhook-Signature` headers. Failed deliveries retry with exponential backoff (1 min, 2 min, 4 min, 8 min, 16 min) over 24 hours. Event idempotency is guaranteed by unique `event_id` values; receivers deduplicate against a processed-events cache. Delivery logs are retained for 30 days.

---

### API Architecture Overview

The diagram below shows the request flow from client through gateway and domain services to the data layer. Sanctum authentication, rate limiting, and tenant resolution (via `X-Region-ID`) are applied at the gateway before domain routing.

```
                    ┌─────────────────────────────────────────────────────┐
                    │                  CLIENT LAYER                       │
                    │  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
                    │  │  Flutter │  │  Web App │  │  USSD Gateway    │ │
                    │  │  (BFF)   │  │  (React) │  │  (Africa's Tlk)  │ │
                    │  └────┬─────┘  └────┬─────┘  └────────┬─────────┘ │
                    └───────┼─────────────┼────────────────┼───────────┘
                            │             │                │
                            └─────────────┴────────────────┘
                                          │ HTTPS / TLS 1.3
                    ┌─────────────────────▼─────────────────────────────┐
                    │              API GATEWAY (Laravel 13)              │
                    │  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
                    │  │ Sanctum  │  │  Tenant  │  │ Rate Limiter     │ │
                    │  │   Auth   │  │ Resolver │  │ (100/500 rpm)    │ │
                    │  └────┬─────┘  └────┬─────┘  └────────┬─────────┘ │
                    └───────┼─────────────┼────────────────┼───────────┘
                            │             │                │
              ┌─────────────┼─────────────┼────────────────┼───────────┐
              │   ┌─────────▼──┐ ┌────────▼──┐  ┌────────▼────┐       │
              │   │ /v1/auth   │ │/v1/market │  │ /v1/forum   │       │
              │   │  Domain    │ │  Domain   │  │  Domain     │       │
              │   └─────┬──────┘ └─────┬─────┘  └─────┬───────┘       │
              │   ┌─────▼──────┐ ┌─────▼──────┐ ┌─────▼───────┐       │
              │   │ /v1/ai     │ │/v1/payment │ │ /v1/services│       │
              │   │  Domain    │ │  Domain    │  │  Domain     │       │
              │   └─────┬──────┘ └─────┬──────┘ └─────┬───────┘       │
              │         │              │              │                 │
              │   ┌─────▼──────────────▼──────────────▼───────┐        │
              │   │          JSON:API Resource Layer          │        │
              │   │   (sparse fieldsets, compound docs,       │        │
              │   │    cursor pagination, error envelopes)     │        │
              │   └─────┬─────────────────────────────┬───────┘        │
              └─────────┼─────────────────────────────┼────────────────┘
                        │                             │
          ┌─────────────▼──────────┐  ┌──────────────▼──────────────┐
          │   DATA LAYER           │  │   EXTERNAL SERVICES         │
          │  ┌──────────────────┐  │  │  ┌──────────────────────┐   │
          │  │  PostgreSQL 16   │  │  │  │  Gemini API          │   │
          │  │  (RLS, pgvector) │  │  │  │  M-Pesa / Tigo Pesa  │   │
          │  └──────────────────┘  │  │  │  Firebase Cloud Msg  │   │
          │  ┌──────────────────┐  │  │  └──────────────────────┘   │
          │  │  Redis 7         │  │  └─────────────────────────────┘
          │  │  (cache, queues) │  │
          │  └──────────────────┘  │
          └────────────────────────┘
```

Each domain controller transforms Eloquent models through a JSON:API resource class. The `ProductResource` below implements sparse fieldset filtering:

```php
<?php

namespace App\Domains\Marketplace\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type'          => 'product',
            'id'            => $this->uuid,
            'attributes'    => $this->filterFields([
                'name'          => $this->name,
                'description'   => $this->description,
                'price_tzs'     => $this->price_tzs,
                'stock_qty'     => $this->stock_qty,
                'tfra_verified' => $this->tfra_verified,
                'created_at'    => $this->created_at->toIso8601String(),
            ], $request),
            'relationships' => [
                'vendor' => [
                    'data'  => ['type' => 'vendor', 'id' => $this->vendor_uuid],
                    'links' => [
                        'related' => route('v1.vendors.show', $this->vendor_uuid),
                    ],
                ],
            ],
            'links' => ['self' => route('v1.products.show', $this->uuid)],
            'meta'  => [
                'region'   => $this->region_code,
                'language' => app()->getLocale(),
            ],
        ];
    }

    protected function filterFields(array $fields, Request $request): array
    {
        $sparse = $request->input('fields.product');

        if (! $sparse) {
            return $fields;
        }

        $allowed = array_map('trim', explode(',', $sparse));

        return array_intersect_key($fields, array_flip($allowed));
    }
}
```

The `filterFields` method intersects requested fields against available attributes. A call with `?fields[product]=name,price_tzs` reduces the response from ~1.2 KB to ~180 bytes. Compound document support at the collection level conditionally adds related resources to the `included` array per the JSON:API full linkage specification.
