## Dimension 5: Modern Architecture Patterns & East African Tech Context

### Key Findings
- Offline-first mobile architecture using Flutter + Drift (SQLite) with background sync engines is the recommended approach for MkulimaForum's mobile clients, enabling full functionality in areas with intermittent connectivity [^20^][^24^].
- USSD fallback via Africa's Talking provides coverage for feature phone users across East Africa, with costs significantly lower than SMS-based alternatives and no internet required [^19^][^23^].
- Laravel 13 (released March 2026) requires PHP 8.3+ and introduces native PHP Attributes, first-party JSON:API resources, the AI SDK, and Reverb database driver eliminating Redis dependency for small deployments [^66^][^67^][^71^].
- pgvector with pgvectorscale extension now outperforms dedicated vector databases at up to 50M vectors (471 QPS vs Qdrant's 41 QPS), making it ideal for self-hosted African deployments where operational simplicity is critical [^28^][^29^].
- Tanzania's Personal Data Protection Act 2022 (effective May 2023) prohibits cross-border data transfers except in compliance with specific conditions; Kenya's DPA 2019 mandates registration for data controllers/processors [^62^][^63^].
- Multi-tenant SaaS using shared database with `tenant_id` column + PostgreSQL Row Level Security (RLS) + Eloquent global scopes is the recommended pattern for region-based tenant isolation in Laravel [^33^][^36^].
- Laravel Reverb (first-party WebSocket server) reduces real-time infrastructure costs by 90%+ compared to Pusher while delivering 40% lower latency, with single-server deployments requiring no Redis [^60^][^65^].
- 58-72% of mobile money fraud in East Africa is attributed to social engineering, with SIM swap scams (43%) and agent-assisted fraud (38%) being the most prevalent attack vectors [^37^].
- AWS Local Zone in Nairobi (af-south-1-nbo-1a) and planned Kenya region reduce latency to under 20ms for East African users, with GCP's africa-south1 (Johannesburg) already operational [^69^][^72^].
- PWA approach enables 1-5MB install size (vs 50-200MB native apps), making it ideal for low-end Android devices common in African markets, with offline-first Service Worker caching [^65^][^74^][^75^].

---

### Offline-First Architecture

#### Core Principles
The offline-first approach treats the local database as the source of truth: reads happen locally (instant, always works), writes happen locally (changes saved immediately), and sync occurs in the background [^20^]. This is essential for MkulimaForum given that rural agricultural areas in East Africa frequently experience intermittent or absent connectivity.

#### Flutter Implementation Stack
- **Drift (formerly Moor)**: SQLite wrapper for Flutter that supports type-safe queries, migrations, and streaming queries. Best for complex local data models [^20^].
- **SharedPreferences**: Lightweight key-value storage for simple data like user settings and auth tokens [^24^].
- **WorkManager**: Background processing for sync tasks that persist across app restarts [^25^].
- **Connectivity Service**: Monitors network state and triggers sync when connectivity returns [^24^].

#### Sync Engine Architecture
```
┌─────────────────────────────────────────────────────┐
│                    Your App                         │
├─────────────────────────────────────────────────────┤
│  SyncEngine                                         │
│  ├── OutboxService (queued operations)              │
│  ├── PushService (send to server)                   │
│  ├── PullService (fetch from server)                │
│  └── ConflictService (resolve conflicts)            │
├─────────────────────────────────────────────────────┤
│  Drift Database + SyncDatabaseMixin                 │
├─────────────────────────────────────────────────────┤
│  TransportAdapter (REST, GraphQL, etc.)             │
└─────────────────────────────────────────────────────┘
```

**Key Pattern**: Database table as sync queue (not in-memory queue) - survives crashes, provides full audit trail, and enables retry tracking with exponential backoff [^25^].

#### CRDTs for Conflict Resolution
Conflict-free Replicated Data Types (CRDTs) ensure mathematical convergence of data across devices without requiring a central server. Key properties [^79^][^82^]:
- **Commutative**: Order of operations doesn't matter
- **Associative**: Grouping of operations doesn't matter
- **Idempotent**: Applying the same operation multiple times has the same effect as once

For MkulimaForum, use **state-based CRDTs** via libraries like Yjs or Automerge for collaborative features (shared farming notes, community discussions) [^79^]. For simple counters (like upvotes), use **G-Counter** (grow-only counter) CRDTs.

#### USSD Fallback Architecture

**Why USSD**: Unstructured Supplementary Service Data (USSD) works on all devices (from old Nokia feature phones to smartphones), requires no internet, and creates real-time sessions with mobile network operators [^23^]. This is critical for MkulimaForum since feature phones still dominate rural African markets.

**Architecture**:
```
User dials *123*456#
    → Mobile Network Operator (Safaricom, Airtel, Vodacom)
        → USSD Gateway (Africa's Talking / Twilio)
            → MkulimaForum Laravel Backend
                → Returns menu text (182 char max)
```

**Key Providers**:
- **Africa's Talking**: Best for African markets, offers SMS, USSD, Voice, Airtime, and Payments APIs. Sandboxed testing free. Covers 300M+ users across Africa [^19^][^70^].
- **Twilio**: Global provider with wider SDK support (C#, Java, Node.js, PHP, Python, Ruby, Go). Higher cost but more documentation [^19^].

**Recommended**: Africa's Talking for MkulimaForum due to better local coverage and lower costs ($0.0075/SMS vs Twilio's $0.01/SMS, with even better USSD rates) [^19^].

**Hybrid Approach**: Smartphone users get the full Flutter app with offline-first sync. Feature phone users access core features (market prices, weather alerts, expert Q&A) via USSD menus. Both converge to the same Laravel backend [^23^].

#### PWA Layer for Low-End Smartphones
Progressive Web Apps provide a middle ground - installable, offline-capable, but requiring only a basic browser [^65^][^74^][^75^]:

- **Storage footprint**: 1-5MB vs 50-200MB for native apps [^75^]
- **Caching strategies**: Stale-While-Revalidate for instant loading, Cache-First for static assets, Network-First for real-time API data [^65^]
- **Push notifications**: Supported on Safari 16.4+, Chrome for Android
- **IndexedDB**: For offline data storage of market prices, farming guides
- **Key success metrics**: Twitter Lite saw +65% page views, Pinterest +60% engagement after PWA launch [^75^]

---

### Modern Laravel Stack (2025-2026)

#### Version Roadmap
| Version | PHP Requirement | Release Date | Bug Fixes Until | Security Until |
|---------|----------------|--------------|-----------------|----------------|
| Laravel 11 | 8.2 - 8.4 | March 2024 | Sept 2025 | March 2026 |
| Laravel 12 | 8.2 - 8.5 | Feb 2025 | Aug 2026 | Feb 2027 |
| Laravel 13 | 8.3 - 8.5 | March 2026 | Q3 2027 | Q1 2028 |

**Recommendation**: Laravel 13 for new projects starting Q2 2026, or Laravel 12 for immediate development [^28^][^66^][^77^].

#### Laravel 13 Key Features
1. **Native PHP Attributes**: Configure models, jobs, commands using `#[Attribute]` syntax instead of class properties - cleaner, more expressive, fully backward compatible [^66^][^67^]
2. **First-Party JSON:API Resources**: Built-in support for JSON:API specification responses [^71^]
3. **Laravel AI SDK (Stable)**: Unified API for text generation, embeddings, agents, vector stores [^71^]
4. **Reverb Database Driver**: No Redis needed for single-server deployments; Redis still required for horizontal scaling [^64^]
5. **Passkey Authentication**: WebAuthn support for passwordless login [^67^]
6. **Cache::touch()**: Extend TTL without fetching/re-storing values - single Redis EXPIRE command [^66^]

#### Laravel Reverb (Real-Time WebSockets)
Laravel's official first-party WebSocket server, replacing third-party dependencies [^60^][^64^][^65^]:

| Feature | Reverb | Pusher | Ably |
|---------|--------|--------|------|
| Cost | Low/fixed ($~5/mo on Laravel Cloud) | Usage-based ($100+/mo/app) | Usage-based |
| Control | Full self-hosted | Limited | Limited |
| Vendor Lock-in | None | Yes | Yes |
| Latency | 40% lower than Pusher | Baseline | Similar to Pusher |
| Setup | Medium (php artisan reverb:start) | Easy | Easy |
| Scaling | Redis pub/sub for horizontal | Built-in | Built-in |

**Installation**: `composer require laravel/reverb && php artisan reverb:install` [^60^]
**Single server**: No Redis required as of Laravel 13 [^64^]
**Horizontal scaling**: Redis pub/sub distributes connections across nodes [^64^]

#### Laravel Octane Performance
Octane serves Laravel using high-powered application servers (FrankenPHP, RoadRunner, Swoole), booting the application once and keeping it in memory [^84^]:

| Server | Best For | Notes |
|--------|----------|-------|
| FrankenPHP | Modern deployments, HTTP/2, HTTP/3 | Newest, written in Go, easiest setup |
| RoadRunner | Production stability | Go-based, mature, zero code changes |
| Swoole | Maximum performance | PHP extension, requires careful memory management |
| OpenSwoole | Swoole fork, open source | Community-maintained |

**Benchmarks** (16 threads, 100 connections, 30s) [^80^]:
- Octane with any server provides 5-10x throughput improvement over PHP-FPM
- RoadRunner and FrankenPHP offer best balance of performance and stability
- For MkulimaForum: Start with FrankenPHP (simplest) or RoadRunner (most stable)

#### Laravel Pulse
Application performance monitoring and insight tool. Tracks:
- Slow queries and their frequency
- Queue throughput and job processing times
- Cache hit rates
- Exception frequency
- Server resources (CPU, memory)

#### Laravel Prompts
CLI input library with rich interactive elements - useful for admin commands and deployment scripts [^76^].

---

### Modern Flutter Stack (2025-2026)

#### Flutter 3.24+ Key Features
- **Impeller Rendering Engine**: Hardware-accelerated graphics on all devices. Replaces Skia on iOS/macOS and is progressing on Android. Eliminates shader compilation jank [^21^][^22^][^27^].
- **Flutter GPU API**: Low-level graphics API for custom renderers, 3D scenes, particle systems. Early preview, requires Impeller [^27^].
- **WebAssembly Compilation (Wasm)**: Stable channel support. Near-native performance for compute-heavy operations in web/PWA builds [^22^].
- **Multi-View Embedding (Web)**: Render Flutter in multiple HTML elements for integrating into existing web apps [^21^].
- **TreeView & CarouselView Widgets**: Built-in tree and carousel components [^21^].

#### Dart 3.5+ Features
- **Records**: Anonymous composite types with named/positional fields
- **Patterns**: Destructuring and matching for records and collections
- **Class Modifiers**: `final`, `interface`, `base`, `sealed` for better API design
- **Enhanced switch expressions**: Exhaustive pattern matching

#### Performance Recommendations for Low-End Devices
1. Use **Impeller** on supported platforms (reduces startup jank)
2. Keep widget tree shallow
3. Use `const` constructors aggressively
4. Implement pagination (page size 20-50 items) [^24^]
5. Use `Image.network` with caching and placeholder skeletons
6. Profile with Flutter DevTools - target <16ms/frame (60fps)

---

### Vector Database Comparison

| Feature | pgvector (+ pgvectorscale) | Qdrant | Weaviate |
|---------|---------------------------|--------|----------|
| **Hosting** | Self-hosted (Postgres) | Self-hosted or Cloud | Self-hosted or Cloud |
| **Open Source** | Yes (Postgres extension) | Yes (Rust) | Yes (Go) |
| **Index Type** | HNSW, IVFFlat | HNSW (payload-indexed) | HNSW + inverted index |
| **Hybrid Search** | Manual (tsvector + vector) | Native (RRF/linear fusion) | Native BM25 + dense |
| **Filter Performance** | Post-filter (improved in 0.8+) | Pre-filter, best-in-class | Pre-filter, strong |
| **Multi-tenancy** | Row-Level Security (RLS) | Payload filter or collections | Native tenant support |
| **Scale (practical)** | <100M vectors | 100M+ (on-disk HNSW) | 100M+ (cluster) |
| **Self-hosted cost at 10M** | $120-180/month (32GB instance) | $120-180/month | $400-600/month |
| **Ops complexity** | Low (existing Postgres) | Medium | High |
| **QPS at 50M vectors** | **471 QPS** [^28^] | 41 QPS [^29^] | ~50-80 QPS |
| **p95 Latency at 50M** | **28ms** [^28^] | Higher | Higher |
| **African deployment fit** | **Excellent** - minimal ops | Good - containerized | Moderate - resource heavy |

**Recommendation for MkulimaForum**: **pgvector** with pgvectorscale extension. Reasons:
1. Already running PostgreSQL for relational data - zero additional infrastructure
2. Transactional consistency between vectors and relational data (ACID)
3. Self-hosted on African cloud providers without vendor lock-in
4. As of 2026, outperforms dedicated vector DBs at moderate scale [^28^]
5. No additional authentication, backup, or monitoring systems needed

**When to consider Qdrant**: If vector-only workload exceeds 100M vectors or requires complex filtered search with multiple metadata constraints [^61^].

---

### Multi-tenancy Patterns

#### Recommended Approach: Shared Database + Row-Level Security

For MkulimaForum's region-based tenant model (TZ, KE, UG, RW), the shared database with `tenant_id` column + PostgreSQL RLS is optimal [^33^][^36^]:

**Architecture**:
```sql
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),        -- "MkulimaForum Tanzania"
    slug VARCHAR(255),        -- "tz"
    domain VARCHAR(255),      -- "tz.mkulimaforum.com"
    country_code CHAR(2),     -- "TZ"
    currency VARCHAR(3),      -- "TZS"
    timezone VARCHAR(50)      -- "Africa/Dar_es_Salaam"
);

CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id),
    email VARCHAR(255) NOT NULL,
    -- composite unique: same email allowed across tenants
    UNIQUE(tenant_id, email)
);

-- Row Level Security
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
CREATE POLICY tenant_isolation ON users
  USING (tenant_id = current_setting('app.current_tenant_id')::int);
```

**Laravel Implementation**:
```php
// Global Scope automatically filters all queries
class TenantScope implements Scope {
    public function apply(Builder $builder, Model $model) {
        $tenantId = auth()->user()?->tenant_id ?? app('current_tenant')?->id;
        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }
}

// Base model all tenant-aware models extend
abstract class TenantAwareModel extends Model {
    protected static function booted() {
        static::addGlobalScope(new TenantScope);
        static::creating(fn($model) => 
            $model->tenant_id ??= app('current_tenant')?->id
        );
    }
}
```

**Why this pattern for MkulimaForum**:
- Cost-effective: Single database serves all regions
- Simplified operations: One migration run for all tenants
- Cross-tenant analytics: Possible with explicit authorization (for aggregate reporting)
- Data sovereignty: RLS provides database-level enforcement; can migrate to separate databases later for specific countries if regulations require physical separation
- Scales to thousands of tenants per database instance [^33^]

**Tenant Resolution** (middleware determines current tenant):
- Subdomain: `tz.mkulimaforum.com`
- Path-based: `mkulimaforum.com/tz/`
- Header-based: `X-Tenant-ID: tz` (for API requests)

---

### Data Sovereignty & Compliance

#### Tanzania (TZ)
- **Law**: Personal Data Protection Act, 2022 (PDPA), effective 1 May 2023 [^62^]
- **Regulator**: Personal Data Protection Commission (PDPC)
- **Key Requirements**:
  - Data controllers and processors must register with PDPC
  - Personal data must be processed lawfully, fairly, transparently
  - Cross-border transfer prohibited except in compliance with PDPA Part V [^62^]
  - Data subjects have right to access, correction, erasure, and objection
  - Data breach notification required
- **Data Localization**: Not strictly mandated but cross-border transfers require compliance with PDPA conditions
- **Sector-specific**: EPOCA (telecoms), NPS Act (financial services) have additional requirements [^62^]

#### Kenya (KE)
- **Law**: Data Protection Act, 2019, effective 25 November 2019 [^63^]
- **Regulator**: Office of the Data Protection Commissioner (ODPC)
- **Key Requirements**:
  - Mandatory registration of data controllers/processors (thresholds apply) [^63^]
  - Data Protection Impact Assessment (DPIA) required for high-risk processing
  - Cross-border transfers require adequacy determination or appropriate safeguards
  - Comprehensive guidance notes for: communications sector, health data, digital credit providers, biometric data, children's data [^63^]
  - Specific guidance for MSMEs
- **Compliance**: ODPC has issued 15+ guidance notes covering most industry verticals

#### Uganda (UG)
- **Law**: Data Protection and Privacy Act, 2019
- **Key Principles**: Lawful processing, purpose limitation, data minimization, accuracy, storage limitation
- **Cross-border transfers**: Require adequacy determination or appropriate safeguards (standard contractual clauses)
- **Data subject rights**: Access, rectification, erasure, restriction, objection
- **Registration**: Data collectors and processors must register with National Information Technology Authority

#### Rwanda (RW)
- **Law**: Law No. 058/2021 Relating to the Protection of Personal Data and Privacy (2021)
- **Regulator**: National Cyber Security Authority
- **Key Requirements**:
  - Data localization encouraged for sensitive categories
  - Cross-border transfers permitted to countries with adequate protection
  - Mandatory breach notification within 72 hours
  - DPO appointment required for large-scale processing

#### Compliance Architecture for MkulimaForum

| Aspect | Implementation |
|--------|---------------|
| Tenant Isolation | PostgreSQL RLS + Laravel Global Scopes |
| Data Encryption | AES-256 at rest, TLS 1.3 in transit |
| Audit Logging | Immutable activity logs per tenant |
| Right to Erasure | Soft delete with grace period, then physical purge |
| Cross-border Transfer | Standard Contractual Clauses + adequacy assessment |
| Consent Management | Granular consent per data processing purpose |
| DPO Contact | Per-tenant DPO designation in registration |
| Breach Notification | Automated detection, 72-hour SLA |

---

### Africa Cloud Infrastructure

#### AWS Africa
- **Region**: `af-south-1` (Cape Town, South Africa) - launched 2020 [^73^]
- **Local Zones**: 
  - Nairobi, Kenya (`af-south-1-nbo-1a`) - announced, reduces latency for East Africa [^69^]
  - Lagos, Nigeria (`af-south-1-los-1a`) - for West Africa [^69^]
  - Johannesburg (`af-south-1-jnb-1a`) [^69^]
- **Latency from East Africa**: Cape Town region ~40-60ms; Nairobi Local Zone expected <20ms
- **Services**: Full AWS stack including EC2, RDS, S3, Lambda, ECS

#### Google Cloud Africa
- **Region**: `africa-south1` (Johannesburg, South Africa) - launched 2023 [^87^]
- **Partnerships**: Vodacom (multi-year collaboration), Liquid C2 (Liquid G distribution program) [^87^]
- **Latency**: Similar to AWS Cape Town for East Africa

#### Microsoft Azure
- **Region**: South Africa North (Johannesburg), South Africa West (Cape Town)
- **Kenya**: Nairobi edge location planned

#### Local & Regional Cloud Providers
| Provider | Locations | Notes |
|----------|-----------|-------|
| Vodacom Business | South Africa (12 providers) | Full cloud stack, enterprise focus [^86^] |
| Liquid Intelligent Technologies | 28 countries | IaaS and edge services, pan-African [^86^] |
| Teraco | Johannesburg, Cape Town, Durban | Africa's largest data center, interconnection hub [^86^] |
| Equinix | Johannesburg | Cloud exchange, data sovereignty focus [^86^] |
| Wananchi | East Africa | Regional provider with local presence |
| AccessKenya | Kenya | Local hosting and cloud services |

#### Latency Considerations
From Nairobi/Tanzania:
- To AWS Cape Town (`af-south-1`): ~45-65ms
- To AWS Nairobi Local Zone: ~15-25ms (when live)
- To GCP Johannesburg: ~50-70ms
- To EU (Frankfurt): ~180-220ms
- To US East (Virginia): ~250-300ms

**Recommendation for MkulimaForum**: 
- Primary: AWS `af-south-1` with CloudFront edge caching
- Future: AWS Nairobi Local Zone when available for latency-sensitive operations
- Database: PostgreSQL on RDS in `af-south-1` with read replicas
- CDN: CloudFront with edge locations across East Africa

---

### Security Architecture

#### Threat Landscape for East African Fintech/Agritech

**Top Attack Vectors** [^37^][^42^]:
1. **Social Engineering (58-72% of fraud)**: Phishing, vishing (voice), smishing (SMS), pretexting
2. **SIM Swap Scams (43% of attacks)**: Fraudsters port victim's number to gain access to accounts [^37^][^39^]
3. **Agent-Assisted Fraud (38%)**: Corrupt mobile money agents facilitating theft [^37^]
4. **Fake Payment Notifications**: Scammers send fake SMS claiming payment received [^35^]
5. **Mobile Malware**: Fake apps mimicking legitimate banking/payment apps [^35^]
6. **Business Email Compromise (BEC)**: Targeting agricultural cooperatives and suppliers [^42^]

#### Security Patterns for MkulimaForum

**Authentication**:
- **Passkey/WebAuthn**: Laravel 13 built-in support - eliminates password phishing [^67^]
- **FIDO2 Hardware Keys**: For admin/cooperative manager accounts [^40^]
- **Authenticator Apps**: Google Authenticator, Authy (replace SMS 2FA) [^40^]
- **Biometric Authentication**: 72% fraud reduction demonstrated in Kenya [^37^]
- **PIN + Device Binding**: For farmer mobile accounts

**SIM Swap Prevention**:
- Never use SMS OTP as sole authentication factor [^39^][^40^]
- Implement carrier "number-lock/port-freeze" programs
- Detect SIM changes via device fingerprinting
- Require out-of-band verification for high-value transactions

**API Security**:
- Rate limiting per tenant + per user (prevent enumeration attacks)
- JWT tokens with short expiry (15-30 minutes)
- Refresh token rotation
- API key scoped to tenant

**Mobile App Security**:
- Certificate pinning (prevent MITM on public WiFi)
- Root/jailbreak detection
- Obfuscation of sensitive code
- Secure storage (Android Keystore / iOS Keychain)

**Fraud Detection**:
- AI-powered transaction monitoring (Laravel AI SDK can help) [^71^]
- Anomaly detection: unusual login locations, rapid successive transactions
- Velocity checks: flag >X transactions per hour from new accounts
- Cooperative-level risk scoring

---

### Real-Time Architecture

#### Use Cases for MkulimaForum
- Instant price alerts when market prices change
- Live expert Q&A notifications
- Cooperative group chat/messaging
- Real-time bidding for agricultural commodities
- Location tracking for logistics/delivery

#### WebSocket Options Comparison

| Feature | Laravel Reverb | Pusher | Ably | Socket.io |
|---------|---------------|--------|------|-----------|
| **Monthly Cost** | ~$5-50 fixed | $100-1000+ | $100-1000+ | Free (self-hosted) |
| **Self-hosted** | Yes | No | No | Yes |
| **Laravel Native** | First-party package | Official SDK | Community SDK | Community |
| **Scaling** | Redis pub/sub | Built-in | Built-in | Redis adapter |
| **Authentication** | Laravel Auth built-in | Webhook auth | Token auth | Custom middleware |
| **Presence Channels** | Yes | Yes | Yes | Via Redis |
| **Private Channels** | Yes | Yes | Yes | Custom implementation |
| **Message History** | No (implement via DB) | Limited | Yes | Via Redis |
| **Latency** | Lowest (same datacenter) | ~80-150ms to Africa | ~80-150ms | Depends on hosting |

**Strong Recommendation**: Laravel Reverb [^60^][^64^][^65^]
- 90% cost reduction vs Pusher ($1,200/year → ~$60/year) [^65^]
- 40% lower latency by running in same datacenter [^65^]
- Full Laravel Echo compatibility (drop-in replacement) [^64^]
- No vendor lock-in
- For MkulimaForum: Run Reverb on same server as Laravel app (single-server, no Redis needed in Laravel 13)

#### Notification Architecture
```
Event occurs (price change, new message)
    → Laravel Event dispatched
        → Broadcast via Reverb (real-time to active users)
        → Queue push notification (for offline users via Firebase/OneSignal)
        → Persist to notification table (for in-app notification center)
```

---

### API Design Standards

#### Recommended: JSON:API + OpenAPI 3.1

**JSON:API** (now first-party in Laravel 13 [^71^]):
- Standardized response format (data, relationships, included, meta)
- Sparse fieldsets (request only needed fields)
- Compound documents (include related resources)
- Pagination, filtering, sorting conventions
- Content negotiation via `Accept: application/vnd.api+json`

**OpenAPI 3.1** [^88^]:
- Machine-readable API specification
- Auto-generates documentation (Swagger UI, ReDoc)
- Client SDK generation
- Contract testing validation

**API Versioning Strategy**:
- URL-based: `/api/v1/` for stable, `/api/v2/` for new major versions
- Header-based: `Accept: application/vnd.api+json; version=1`
- Deprecation: 6-month notice before version sunset
- Laravel 13 JSON:API resources handle versioning natively

**Mobile-Optimized API Patterns**:
1. **BFF (Backend for Frontend)**: Separate API optimized for mobile vs web
2. **Field Selection**: `?fields[post]=title,body,created_at` reduces payload
3. **Compound Documents**: `?include=author,comments` reduces N+1 requests
4. **Delta Sync**: `/sync?since=timestamp` returns only changed data (critical for offline-first)
5. **Compression**: Brotli/gzip for JSON responses
6. **Pagination**: Cursor-based for stable ordering on mobile

**REST vs GraphQL for Mobile**:
- **REST with JSON:API** recommended for MkulimaForum
- Simpler caching (HTTP cache headers)
- Easier offline support (predictable endpoints)
- Better tooling in Flutter (retrofit, chopper)
- GraphQL adds complexity not justified for this use case

---

### Recommended Technical Stack for MkulimaForum

#### Backend
| Component | Technology | Version | Notes |
|-----------|-----------|---------|-------|
| Framework | Laravel | 13.x | PHP 8.3+ required |
| Language | PHP | 8.3+ | JIT optimizations, typed constants |
| Database | PostgreSQL | 16+ | RLS for multi-tenancy, pgvector for AI |
| Cache | Redis | 7+ | Sessions, cache, queue, Reverb horizontal scaling |
| Queue | Laravel Queue + Redis | - | Background jobs, notifications |
| WebSocket | Laravel Reverb | 1.x | Self-hosted, real-time features |
| API Format | JSON:API | - | First-party Laravel 13 support |
| Documentation | OpenAPI 3.1 | - | Swagger UI integration |
| Search | Laravel Scout + Meilisearch | - | Full-text search across tenants |
| AI/ML | Laravel AI SDK | 1.x | Embeddings, agents, vector operations |
| Vector DB | pgvector + pgvectorscale | 0.8+ | Same PostgreSQL instance |
| Server | FrankenPHP or RoadRunner | - | Via Laravel Octane |
| Monitoring | Laravel Pulse + Prometheus | - | Application + infrastructure |
| Testing | PHPUnit + Pest | 3.x | Unit, integration, Dusk (browser) |

#### Mobile
| Component | Technology | Version | Notes |
|-----------|-----------|---------|-------|
| Framework | Flutter | 3.24+ | Impeller rendering, Wasm web |
| Language | Dart | 3.5+ | Records, patterns, enhanced switch |
| Local DB | Drift (SQLite) | - | Type-safe, migrations, streaming |
| State Management | Riverpod / BLoC | - | Reactive, testable |
| HTTP Client | Dio | - | Interceptors, retries, cache |
| Offline Sync | Custom SyncEngine | - | CRDTs for conflicts, background sync |
| Background Work | WorkManager | - | Persistent across restarts |
| Push Notifications | Firebase Cloud Messaging | - | Cross-platform |
| Maps | Flutter Map / Google Maps | - | Offline tile caching |
| Analytics | Firebase Analytics | - | User behavior, crash reporting |

#### Web (PWA)
| Component | Technology | Notes |
|-----------|-----------|-------|
| Framework | Flutter Web (Wasm) | Near-native performance |
| Service Worker | flutter_service_worker | Built-in with Flutter |
| Local Storage | IndexedDB | Offline data caching |
| Install | Web App Manifest | Add to home screen |

#### USSD Fallback
| Component | Technology | Notes |
|-----------|-----------|-------|
| Gateway | Africa's Talking | 300M+ African users |
| Backend Handler | Laravel USSD Controller | Session management |
| Menu Builder | Custom Laravel package | Hierarchical menu DSL |
| Session Store | Redis | 3-minute USSD session timeout |

#### Infrastructure
| Component | Technology | Notes |
|-----------|-----------|-------|
| Cloud | AWS af-south-1 | Cape Town primary |
| Local Zone | AWS Nairobi (future) | <20ms for East Africa |
| CDN | CloudFront | Edge caching across Africa |
| Containers | Docker + ECS/Fargate | Or Laravel Cloud ($5/mo starter) |
| Database | RDS PostgreSQL | Multi-AZ, automated backups |
| Storage | S3 | Image/video storage per tenant |
| DNS | Route 53 | Geo-routing per country |
| SSL | Let's Encrypt / ACM | Auto-renewal |
| CI/CD | GitHub Actions | Test, build, deploy pipeline |

#### Security Stack
| Component | Technology | Notes |
|-----------|-----------|-------|
| Authentication | Laravel Passkey + PIN | WebAuthn for biometrics |
| 2FA (Admin) | Authenticator apps | Google Authenticator, Authy |
| API Security | Laravel Sanctum | Token-based API auth |
| Encryption | AES-256-GCM | At rest (RDS) + in transit (TLS 1.3) |
| WAF | AWS WAF | DDoS, SQL injection, XSS |
| Secrets | AWS Secrets Manager | API keys, DB credentials |
| Audit | Laravel Activity Log | Immutable per-tenant logs |

---

### Sources

[^19^] https://www.courier.com/integrations/compare/africas-talking-vs-twilio - Africa's Talking vs Twilio comparison
[^20^] https://777genius.medium.com/building-offline-first-flutter-apps-a-complete-sync-solution-with-drift-d287da021ab0 - Offline-First Flutter with Drift
[^21^] https://iconflux.com/blog/flutter-3-24 - Flutter 3.24 features
[^22^] https://www.techvoot.com/blog/flutter-3-24-and-dart-3-5-new-features-and-updates - Flutter 3.24 and Dart 3.5
[^23^] https://www.twilio.com/en-us/blog/communication-humanitarian-operations-ussd-flex - Twilio USSD for humanitarian operations
[^24^] https://dev.to/anurag_dev/implementing-offline-first-architecture-in-flutter-part-2-building-sync-mechanisms-and-handling-4mb1 - Flutter offline sync mechanisms
[^25^] https://geekyants.com/en-us/blog/offline-first-flutter-implementation-blueprint-for-real-world-apps - Offline-First Flutter blueprint
[^26^] https://blog.logrocket.com/offline-first-frontend-apps-2025-indexeddb-sqlite/ - Offline-first frontend apps 2025
[^27^] https://blog.flutter.dev/announcing-flutter-3-24-and-dart-3-5-204b7d20c45d - Announcing Flutter 3.24 and Dart 3.5
[^28^] https://dev.to/polliog/postgresql-as-a-vector-database-when-to-use-pgvector-vs-pinecone-vs-weaviate-4kfi - pgvector vs alternatives 2026
[^29^] https://www.firecrawl.dev/blog/best-vector-databases - Best vector databases 2026
[^33^] https://dev.to/addwebsolutionpvtltd/building-multi-tenant-saas-with-row-level-security-in-laravel-3kd3 - Multi-tenant SaaS with RLS in Laravel
[^34^] https://supertokens.com/blog/multi-tenant-architecture - Multi-tenant SaaS architecture patterns
[^35^] https://www.eset.com/afr/about/newsroom/press-releases-afr/blog/mobile-scams-are-rising-in-africa-heres-how-to-protect-yourself/ - Mobile scams in Africa
[^36^] https://www.intelligentgraphicandcode.com/development/multi-tenant-laravel - Multi-Tenant Laravel: One Codebase, Many Clients
[^37^] https://papers.ssrn.com/sol3/Delivery.cfm/5257020.pdf - Mobile Money Social Engineering Attacks in African Countries
[^39^] https://cioafrica.co/sim-swap-fraud-a-new-wave-of-attacks-targeting-financial-online-services-in-africa/ - SIM swap fraud in Africa
[^40^] https://www.group-ib.com/resources/knowledge-hub/sim-swap/ - SIM swap protection tips
[^42^] https://www.interpol.int/content/download/23094/file/INTERPOL_Africa_Cyberthreat_Assessment_Report_2025.pdf - INTERPOL Africa Cyberthreat Assessment 2025
[^60^] https://laracopilot.com/blog/laravel-reverb-websockets-guide/ - Laravel Reverb WebSockets Guide
[^61^] https://tensoria.fr/en/blog/vector-database-comparison - Vector database comparison 2026
[^62^] https://www.dlapiperdataprotection.com/?t=law&c=TZ - Tanzania data protection laws
[^63^] https://www.dlapiperdataprotection.com/index.html?t=law&c=KE - Kenya data protection laws
[^64^] https://www.curotec.com/services/technologies/laravel/laravel-reverb/ - Laravel Reverb for self-hosted WebSocket
[^65^] https://sadiqueali.medium.com/laravel-reverb-vs-pusher-i-saved-1-200-year-and-got-40-lower-latency-965b2a5e70c5 - Reverb vs Pusher cost and latency comparison
[^66^] https://www.syntacticsinc.com/news-articles-cat/laravel-13-release/ - What's New in Laravel 13
[^67^] https://pola5h.github.io/blog/laravel-13-new-features/ - Laravel 13 New Features
[^69^] https://holori.com/list-of-all-aws-regions-and-availability-zones/ - AWS regions including Africa Local Zones
[^70^] https://medium.com/@onejohi/integrating-sms-services-to-your-express-app-with-africas-talking-apis-36c9fc8bfd2f - Africa's Talking integration tutorial
[^71^] https://laravel-news.com/laravel-13-released - Laravel 13 Released
[^72^] https://www.cloudping.info/ - Cloud latency monitoring
[^73^] https://www.cio.com/article/193405/heres-how-amazons-south-africa-data-centres-will-impact-enterprises.html - AWS South Africa region impact
[^74^] https://wezom.com/blog/progressive-web-apps-vs-native-apps-in-2025 - PWA vs Native 2025
[^75^] https://senorit.de/en/blog/progressive-web-apps-guide-2025 - Progressive Web Apps in 2026
[^79^] https://velt.dev/blog/crdt-implementation-guide-conflict-free-apps - CRDT Implementation Guide
[^80^] https://medium.com/beyn-technology/hola-frankenphp-laravel-octane-servers-comparison-pushing-the-boundaries-of-performance-d3e7ad8e652c - Laravel Octane servers benchmark
[^82^] https://crdt.tech/ - About CRDTs
[^84^] https://laravel.com/docs/13.x/octane - Laravel Octane documentation
[^86^] https://heirstechnologies.com/wp-content/uploads/2025/11/Heirs-Tech-Industry-Report_Africas-Digital-Future_Cloud-Connectivity-AI-in-the-Next-Decade.pdf - Africa's Digital Future: Cloud & AI Report
[^87^] https://htxt.co.za/2025/12/google-cloud-begins-conquest-of-africa/ - Google Cloud Africa expansion
[^88^] https://swagger.io/specification/ - OpenAPI 3.1 Specification
