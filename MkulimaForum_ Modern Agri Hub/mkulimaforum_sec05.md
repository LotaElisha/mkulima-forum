## 5. Laravel Backend Architecture — Domain-Driven Design

### 5.1 Domain-Driven Design Structure

MkulimaForum's backend is organized around seven bounded contexts — semantic boundaries within which each domain model maintains internal consistency [^33^]. This prevents a single `User` model from accumulating fields for farmers, agro-dealers, logistics providers, and administrators.

#### 5.1.1 Seven Bounded Contexts

```
┌──────────────────────────────────────────────────────────────────────┐
│                    MkulimaForum Bounded Contexts                      │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌──────────┐    OrderPlaced     ┌──────────┐    PaymentCaptured   │
│  │          │───────────────────►│          │───────────────────►  │
│  │Marketplace│◄──────────────────│ Payment  │◄───────────────────  │
│  │ (dukani) │   StockReserved    │ (malipo) │    EscrowReleased    │
│  └────┬─────┘                    └────┬─────┘                      │
│       │      ┌──────────┐            │                             │
│       │      │          │            │                             │
│  ProductLinked     AI Advice         WalletToppedUp                  │
│       │      │   AI     │            │                             │
│       └─────►│ (akili)  │◄───────────┘                             │
│              └────┬─────┘                                           │
│                   │  DiagnosisRequested                              │
│  ┌──────────┐     │     ┌──────────┐    ThreadModerated            │
│  │          │◄────┘     │          │◄──────────┐                   │
│  │ Scanner  │  Result   │  Forum   │            │                   │
│  │ (chungu) │──────────►│ (jadala) │────────────┘                   │
│  └──────────┘           └──────────┘                                │
│       ▲                       ▲                                      │
│       │ UserRegistered        │ NotificationSent                     │
│  ┌────┴─────┐            ┌────┴─────┐     ┌──────────┐             │
│  │          │────────────►│          │     │          │             │
│  │   Auth   │  JWT Issued │ Services │◄────│  Auth    │             │
│  │ (utambu) │◄────────────│ (huduma) │     │ (utambu) │             │
│  └──────────┘             └──────────┘     └──────────┘             │
│                            BookingConfirmed                          │
│                                                                       │
│  Cross-Cutting: Spatie Permission (RBAC), TenantScope (RLS),         │
│  Laravel Scout (Meilisearch), Laravel Horizon (Queue Monitoring)     │
└──────────────────────────────────────────────────────────────────────┘
```

**Auth** (`utambu`) manages identity via Sanctum 4.x with RBAC through Spatie Permission 6.x across seven roles [^33^]. **Marketplace** (`dukani`) handles the product catalog, inventory, TFRA/KEPHIS verification, and escrow-protected checkout. **Forum** (`jadala`) powers community threads, expert Q&A, and content moderation. **Scanner** (`chungu`) orchestrates hybrid disease diagnosis: TensorFlow Lite on-device for common diseases and Gemini Vision cloud fallback for rare cases [^66^]. **Services** (`huduma`) covers agronomist booking, veterinary scheduling, soil testing, warehouse reservations, and logistics dispatch. **AI** (`akili`) is the RAG layer — embedding generation, pgvector search over TARI knowledge bases, and LLM prompt management [^28^]. **Payment** (`malipo`) abstracts mobile money gateways, wallet management, escrow, and commissions.

#### 5.1.2 Domain Layer: Entities, Events, and Clean Separation

Each context's domain layer is ignorant of HTTP, JSON, or databases. Entities carry identity and invariants; value objects (`Money`, `GeoLocation`, `CropVariety`) are immutable. Domain events decouple contexts: when an order reaches `paid`, `OrderPaid` triggers escrow release, AI pattern updates, and a "verified purchaser" badge.

```php
<?php

namespace App\Domains\Marketplace\Events;

use App\Domains\Marketplace\ValueObjects\Money;
use App\Domains\Marketplace\Entities\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $orderId,
        public readonly string $buyerId,
        public readonly string $vendorId,
        public readonly Money $amount,
        public readonly string $paymentMethod,
        public readonly \DateTimeImmutable $paidAt,
    ) {}

    public static function fromOrder(Order $order, string $method): self
    {
        return new self(
            orderId: $order->id(), buyerId: $order->buyerId(),
            vendorId: $order->vendorId(), amount: $order->total(),
            paymentMethod: $method,
            paidAt: new \DateTimeImmutable('now', new \DateTimeZone('Africa/Dar_es_Salaam')),
        );
    }
}
```

Repository interfaces declare intent in `App\Domains\{Domain}\Repositories\`; Eloquent implementations in `App\Infrastructure\Persistence\` translate domain objects to rows. Policies enforce authorization via Spatie gate checks [^33^].

#### 5.1.3 Application Layer: CQRS Pattern

CQRS separates commands (state mutation) from queries (read-optimized DTOs). Each command has one handler resolved via Laravel's service container.

```php
<?php

namespace App\Domains\Services\Commands;

use App\Domains\Services\ValueObjects\GeoLocation;
use App\Domains\Services\ValueObjects\ServiceSlot;

final class BookSoilTest
{
    public function __construct(
        public readonly string $farmerId,
        public readonly string $providerId,
        public readonly GeoLocation $farmLocation,
        public readonly ServiceSlot $requestedSlot,
        public readonly ?string $notes = null,
    ) {}
}

namespace App\Domains\Services\Handlers;

use App\Domains\Services\Commands\BookSoilTest;
use App\Domains\Services\Entities\Booking;
use App\Domains\Services\Events\BookingConfirmed;
use App\Domains\Services\Repositories\BookingRepository;
use App\Domains\Services\Repositories\ProviderRepository;

final class BookSoilTestHandler
{
    public function __construct(
        private readonly BookingRepository $bookings,
        private readonly ProviderRepository $providers,
    ) {}

    public function handle(BookSoilTest $command): Booking
    {
        $provider = $this->providers->getAvailable($command->providerId);
        if (!$provider->isAvailableAt($command->requestedSlot)) {
            throw new \DomainException('Provider slot no longer available');
        }
        $booking = Booking::create(
            farmerId: $command->farmerId, providerId: $command->providerId,
            location: $command->farmLocation, slot: $command->requestedSlot,
            status: BookingStatus::CONFIRMED,
        );
        $this->bookings->save($booking);
        BookingConfirmed::dispatch($booking->toArray());
        return $booking;
    }
}
```

### 5.2 Laravel 13.x Modern Patterns

MkulimaForum targets Laravel 13.x (March 2026, PHP 8.3+), reducing infrastructure cost and vendor lock-in [^66^][^71^].

#### 5.2.1 First-Party JSON:API Resources

Laravel 13 ships with native JSON:API resources, eliminating third-party packages. Sparse fieldsets, compound documents, and cursor pagination are handled by first-party `JsonApiResource` classes [^71^].

```php
<?php

namespace App\Http\Resources\JsonApi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'product',
            'id' => $this->uuid,
            'attributes' => [
                'name' => $this->name,
                'name_sw' => $this->name_sw,
                'price' => ['amount' => $this->price->amount(), 'currency' => $this->price->currency()],
                'stock_quantity' => $this->stock_qty,
                'tfra_verified' => $this->tfra_verified,
                'kephis_certified' => $this->kephis_certified,
            ],
            'relationships' => [
                'vendor' => VendorResource::make($this->whenLoaded('vendor')),
                'certificates' => CertificateResource::collection($this->whenLoaded('certificates')),
            ],
            'links' => ['self' => route('api.v1.products.show', $this->uuid)],
            'meta' => ['region' => app('current_tenant')?->slug, 'language' => app()->getLocale()],
        ];
    }
}
```

Field selection reduces payload sizes by 40–70% — critical on 2G/3G networks.

#### 5.2.2 Laravel Reverb: Real-Time Infrastructure

Laravel Reverb is a first-party WebSocket server replacing Pusher or Ably. Production deployments demonstrate a 90% cost reduction — from approximately $1,200/year to roughly $60/year — alongside 40% lower latency by colocating with the application server [^65^]. Reverb powers live order tracking, price alerts, and cooperative messaging. Single-server deployments require no Redis; horizontal scaling uses Redis pub/sub [^60^][^64^].

#### 5.2.3 FrankenPHP and Laravel Octane

FrankenPHP, a Go-based application server, serves Laravel through Octane with 5–10x throughput improvement over PHP-FPM (benchmarked at 16 threads, 100 connections, 30s) [^80^][^84^].

```
┌──────────────────────────────────────────────────────────────────┐
│              Request Lifecycle: FrankenPHP + Octane              │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  [Flutter / USSD / Web Dashboard]                                 │
│       │                                                           │
│       ▼ HTTPS/TLS 1.3                                             │
│  ┌────────────────────────────────────────────────────────┐      │
│  │              Cloudflare CDN + WAF                      │      │
│  └────────────────────┬───────────────────────────────────┘      │
│                       │                                           │
│                       ▼                                           │
│  ┌────────────────────────────────────────────────────────┐      │
│  │           FrankenPHP (HTTP/2, HTTP/3)                  │      │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐            │      │
│  │  │ Worker 1 │  │ Worker 2 │  │ Worker N │  Booted    │      │
│  │  │ (warm)   │  │ (warm)   │  │ (warm)   │  Once      │      │
│  │  └────┬─────┘  └────┬─────┘  └────┬─────┘            │      │
│  │       └─────────────┴─────────────┘                  │      │
│  │                  Laravel Octane Router                │      │
│  └────────────────────┬───────────────────────────────────┘      │
│                       │                                           │
│         ┌─────────────┼─────────────┐                            │
│         ▼             ▼             ▼                            │
│    ┌─────────┐   ┌─────────┐   ┌─────────┐                     │
│    │  Auth   │   │Marketplace│  │ Payment │   Domain Handlers   │
│    │ Handler │   │ Handler  │   │ Handler │                     │
│    └────┬────┘   └────┬────┘   └────┬────┘                     │
│         └─────────────┴─────────────┘                            │
│                       │                                           │
│         ┌─────────────┼─────────────┐                            │
│         ▼             ▼             ▼                            │
│    ┌─────────┐   ┌─────────┐   ┌─────────┐                     │
│    │PostgreSQL│   │  Redis  │   │ Meili-  │                     │
│    │  + RLS  │   │(cache/Q)│   │ search  │                     │
│    └─────────┘   └─────────┘   └─────────┘                     │
│                                                                   │
│  Zero-downtime: `php artisan octane:reload` rotates workers       │
│  without dropping WebSocket connections.                          │
└──────────────────────────────────────────────────────────────────┘
```

Workers reduce median latency from 85 ms (PHP-FPM) to 12 ms. Zero-downtime reloads preserve WebSocket connections during deployments [^84^].

#### 5.2.4 Laravel AI SDK: LLM Orchestration

The Laravel AI SDK (stable in 13.x) provides a unified interface for text generation, embeddings, and vector operations [^71^]. MkulimaForum configures three provider tiers: **Gemini 2.0 Flash** as primary ($0.075/1M tokens, strong Swahili), **OpenAI GPT-4o-mini** as secondary, and **self-hosted Llama 3** on AWS `af-south-1` for data restricted under Tanzania's PDPA 2022 [^62^]. Domain code calls `AI::chat()->generate()`; the SDK resolves the active provider from tenant-scoped config.

### 5.3 Code Organization & Quality

#### 5.3.1 Directory Structure

```
app/
├── Domains/{Auth,Marketplace,Forum,Scanner,Services,AI,Payment}/
│   └── {Entity,Repository,Service,Event,Policy}/
├── Application/{Commands,Queries,Handlers,Services}/
├── Http/Resources/JsonApi/
├── Infrastructure/Persistence/
└── Providers/DomainServiceProvider.php
```

Each context registers its own service provider. Database factories mirror domain structure — `database/factories/Marketplace/ProductFactory.php` keeps test data context-local.

#### 5.3.2 Testing Strategy

| Layer | Tool | Target Coverage | Scope |
|-------|------|----------------|-------|
| Domain (entities, value objects) | PHPUnit | >90% | Invariant enforcement, equality, event emission |
| Application (handlers, services) | PHPUnit | >85% | Handler execution, cross-domain transaction rollback |
| HTTP (controllers, resources) | Pest PHP | >80% | Endpoint contracts, JSON:API structure, status codes |
| Integration (DB, queues, cache) | Pest + Testcontainers | >75% | PostgreSQL RLS policies, Redis queue durability |
| API contracts | Spectral (OpenAPI) | 100% | Response schema validation against OpenAPI 3.1 |
| End-to-end | Laravel Dusk | Critical paths | Marketplace checkout, payment callback, diagnosis flow |

Contract tests validate JSON:API responses against OpenAPI 3.1 on every CI run. Integration tests via Testcontainers verify RLS policies at the database level [^33^][^36^].

#### 5.3.3 Package Ecosystem

| Package | Version | Purpose | DDD Alignment |
|---------|---------|---------|---------------|
| `spatie/laravel-permission` | ^6.0 | RBAC with roles and permissions | Auth context policy enforcement [^33^] |
| `spatie/laravel-medialibrary` | ^11.0 | Image/document uploads with conversions | Scanner image pipeline |
| `spatie/laravel-multitenancy` | ^4.0 | Country-scoped tenant resolution | RLS integration for TZ/KE/UG/RW [^36^] |
| `laravel/scout` + `meilisearch` | ^10.0 | Full-text search across products, posts | Query-side read optimization |
| `laravel/horizon` | ^5.0 | Redis queue monitoring and retry management | Background job observability |
| `predis/predis` | ^2.0 | Redis client for cache, sessions, queue | Infrastructure layer |

Spatie Permission 6.x resolves roles through Laravel's gate system without leaking authorization into controllers. Multitenancy 4.x sets `app.current_tenant_id` per connection for automatic scoping [^36^]. Scout with Meilisearch delivers sub-50ms search via per-tenant index prefixes (`tz_products`, `ke_products`).
