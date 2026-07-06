## 4. System Architecture — High-Level Design

MkulimaForum's architecture targets four East African countries from launch day, serving farmers with intermittent connectivity and low-end devices. This chapter defines the architectural foundation upon which all subsequent technical decisions build.

### 4.1 Architectural Philosophy

#### 4.1.1 Design Principles

Six principles govern every architectural decision:

**Domain-Driven Design (DDD) with Bounded Contexts.** Each domain — authentication, marketplace, disease scanning, forum, payments — operates as a bounded context with its own models and service boundary [^33^], preventing coupling and enabling independent evolution.

**API-First with JSON:API Standard.** All services expose RESTful APIs conforming to the JSON:API specification, a first-party feature in Laravel 13 [^71^], providing sparse fieldsets for mobile optimization and compound documents that reduce N+1 queries.

**Offline-First as Default.** The Flutter app treats the local Drift (SQLite) database as the source of truth: reads and writes occur locally; sync happens in the background [^20^]. Conflict-free Replicated Data Types (CRDTs) ensure data convergence across devices without server coordination [^79^][^82^].

**Trust-by-Design.** KYC verification, escrow payments, and product authentication form a foundational layer. With 58-72% of mobile money fraud in East Africa from social engineering [^37^], every transaction must be verifiable.

**Voice-First for Inclusion.** Voice interfaces in Swahili, Luganda, and Kinyarwanda serve as the primary interaction mode where smartphone penetration is 41.8% and a 24% mobile internet gender gap exists.

**Multi-Country from Day One.** Tanzania launches first, but the architecture supports Kenya, Uganda, and Rwanda from inception [^7^]. Each country has distinct mobile money rails, regulatory bodies, crop profiles, and compliance requirements.

#### 4.1.2 Technology Stack

Table 1 summarizes the core selections.

| Component | Technology | Version | Justification |
|-----------|-----------|---------|---------------|
| Backend Framework | Laravel | 13.x | First-party JSON:API, AI SDK, Reverb; PHP 8.3+ [^66^][^71^] |
| App Server | FrankenPHP | Latest | 5-10x throughput via Octane; HTTP/2, HTTP/3 [^80^][^84^] |
| Mobile | Flutter | 3.24+ | Impeller rendering, Wasm compilation [^21^][^22^] |
| Local DB | Drift (SQLite) | 2.x+ | Type-safe; database-as-sync-queue pattern [^20^] |
| Database | PostgreSQL | 16+ | RLS multi-tenancy, pgvector, PostGIS [^28^][^33^] |
| Vectors | pgvector + pgvectorscale | 0.8+ | 471 QPS at 50M vectors; zero extra infra [^28^][^29^] |
| Cache/Queue | Redis | 7.x | Sessions, cache, queues, Reverb scaling |
| Search | Meilisearch | Latest | Laravel Scout; typo-tolerant per-tenant |
| Real-Time | Laravel Reverb | 1.x+ | 40% lower latency, 90% cost cut vs Pusher [^60^][^65^] |
| Push | Firebase FCM | Latest | Cross-platform; topic-based regional alerts |
| CDN | Cloudflare | — | Edge caching, DDoS, WAF |
| Cloud | AWS af-south-1 | — | 45-65 ms from East Africa [^72^][^73^] |
| USSD | Africa's Talking | — | 300M+ African users [^19^] |

pgvector achieves 471 QPS at 28 ms p95 at 50M vectors, outperforming Qdrant (41 QPS) and Weaviate (50-80 QPS) while eliminating separate infrastructure [^28^][^29^]. At 5-15 million vectors across four countries, MkulimaForum has substantial headroom.

#### 4.1.3 C4 Context and Container Diagrams

**C4 Context Diagram** — system within its actor ecosystem.

```
+----------------------------------------------------------------------------------+
|                          C4 CONTEXT: MkulimaForum                                |
+----------------------------------------------------------------------------------+
|                                                                                  |
|  +-------------+ +-------------+ +-------------+ +-------------+                |
|  |  Mkulima    | | Agrodealer  | | Agri-Expert | | Government  |                |
|  |  (Farmer)   | | (Seller)    | | /Vet        | |  Partner    |                |
|  +------+------+ +------+------+ +------+------+ +------+------+                |
|         |               |               |               |                        |
|         |Voice/Swahili  |Product mgmt   |Booking        |Data feed               |
|         |USSD/SMS       |Inventory      |Disease alerts |KYC verify              |
|         |Flutter app    |Sales dash     |Forum replies  |Analytics               |
|         +---------------+---------------+---------------+                        |
|                         |                                                        |
|                +--------v---------+                                               |
|                |  MkulimaForum    |                                               |
|                |  Platform        |                                               |
|                +--------+---------+                                               |
|                         |                                                        |
|          +--------------+--------------+                                          |
|          |              |              |                                          |
|  +-------v------+ +-----v-------+ +---v----------+                               |
|  | Mobile Money | | AI Cloud    | | Regulatory   |                               |
|  | (M-Pesa etc) | | (Gemini)    | | (TFRA etc)   |                               |
|  +--------------+ +-------------+ +--------------+                               |
+----------------------------------------------------------------------------------+
```

**C4 Container Diagram** — deployable containers within the system.

```
+----------------------------------------------------------------------------------+
|                        C4 CONTAINER: MkulimaForum                                |
+----------------------------------------------------------------------------------+
|  CLIENT LAYER                       ADMIN LAYER                                  |
|  +------------------+ +------------------+ +------------------+                 |
|  | Flutter Mobile   | | PWA (Low-end)    | | Web Admin      |                  |
|  +--------+---------+ +--------+---------+ +--------+---------+                 |
|           |                    |                    |                             |
|           +---------+----------+--------------------+                             |
|                     |  HTTPS / TLS 1.3                                             |
|                     v                                                              |
|  +--------------------------------------------------------------------------+     |
|  | API GATEWAY (Laravel 13 + FrankenPHP): routing, rate limit, auth, BFF   |     |
|  +-----------------------------------+--------------------------------------+     |
|                                      |                                             |
|          +---------------------------+---------------------------+                 |
|          |                           |                           |                 |
|  +-------v--------+ +--------------v-------------+ +-----------v---------+        |
|  | SYNC: REST/    | | ASYNC: Redis + Horizon     | | REAL-TIME: Reverb   |        |
|  | JSON:API       | | (background jobs)           | | WebSocket           |        |
|  +-------+--------+ +--------------+-------------+ +-----------+---------+        |
|          |                         |                           |                  |
|          |           +------------v------------+              |                  |
|          |           | EVENT BUS (Redis Pub/Sub)|              |                  |
|          |           +------------+------------+              |                  |
|          |                         |                           |                  |
|  +-------v-------------------------v---------------------------v---------+        |
|  | MICROSERVICES: Auth, User, Marketplace, Disease, Forum, Services,    |        |
|  | AI, Payment, Notification, Analytics                                  |        |
|  +-----------------------------------+-----------------------------------+        |
|                                      |                                             |
|  +--------v-----------v-------------v-------------v----------v----------+        |
|  | PostgreSQL 16+  | Redis 7.x  | Meilisearch  | AWS S3                 |        |
|  | (RLS, pgvector) | (cache,    | (full-text)   | (media)                |        |
|  +-----------------+  queues    +---------------+----------------------+        |
|                                                                                  |
|  EXTERNAL: Firebase FCM | Africa's Talking USSD | Cloudflare CDN + WAF          |
+----------------------------------------------------------------------------------+
```

The container architecture uses three messaging tiers. Synchronous REST handles user-facing requests. Asynchronous Redis queues with Laravel Horizon process background jobs [^60^]. Real-time communication uses Laravel Reverb, achieving 40% lower latency and 90% cost reduction versus third-party providers [^60^][^65^].

### 4.2 Microservices Architecture

#### 4.2.1 Service Decomposition

MkulimaForum decomposes into ten bounded-context microservices [^33^]:

- **Auth Service** — Passkey/WebAuthn, PIN fallback, SIM-swap detection [^37^][^67^]
- **User Service** — Profiles, KYC, role management, reputation scoring
- **Marketplace Service** — Catalog, inventory, checkout, TFRA/KEPHIS verification
- **Disease Scanner Service** — TF Lite on-device inference (20 diseases), Gemini Vision cloud fallback [^6^]
- **Forum Service** — Threads, pest alerts, moderation, knowledge base
- **Services Marketplace Service** — Provider discovery, booking, 4-tier vetting
- **AI Orchestration Service** — RAG pipeline, voice STT/TTS, embeddings [^71^]
- **Payment Service** — Mobile money abstraction, escrow, wallets
- **Notification Service** — Push, SMS, USSD, email dispatch
- **Analytics Service** — Government reporting, platform metrics, fraud detection

#### 4.2.2 Service Communication

Table 2 defines inter-service patterns.

| Source | Target | Pattern | Protocol | Purpose |
|--------|--------|---------|----------|---------|
| Mobile App | API Gateway | Sync | REST/JSON:API | User-facing CRUD |
| API Gateway | All Services | Sync | REST/JSON:API | Routing, auth |
| Auth Service | User Service | Sync | REST/JSON:API | Profile lookup |
| Marketplace | Payment Service | Async | Redis Queue + Horizon | Payment post-checkout |
| Payment Svc | Notification Svc | Event-driven | Redis Pub/Sub | Confirmation dispatch |
| Disease Scanner | AI Orchestration | Async | Redis Queue | Image analysis |
| AI Orchestration | Notification Svc | Event-driven | Redis Pub/Sub | Diagnosis alert |
| Forum Service | Notification Svc | Event-driven | Redis Pub/Sub | Reply alerts |
| All Services | Analytics Service | Async | Redis Queue | Event streaming |
| Notification Svc | Mobile App | Real-time | Laravel Reverb | Active session push |
| Notification Svc | Mobile App | Push | Firebase FCM | Offline device push |
| USSD Gateway | Auth + Marketplace | Sync | REST/JSON:API | Feature phone menus |

Three patterns govern communication. Synchronous REST serves immediate-response paths (auth, browsing). Asynchronous queues handle latency-tolerant operations: disease image analysis (3-10 seconds), payment reconciliation, bulk notifications. Event-driven Pub/Sub decouples publishers from subscribers — a `PaymentConfirmed` event allows the Notification and Analytics Services to act independently.

#### 4.2.3 API Gateway

The gateway implements cross-cutting concerns. **Country-code routing** directs requests by subdomain (`tz.mkulimaforum.com`) or `X-Country-Code` header, setting the PostgreSQL RLS context. **Per-second rate limiting** enforces tiered quotas scoped per user per tenant: 100/minute for browsing, 10/minute for orders, 10/hour for AI diagnosis. **Backend-for-Frontend (BFF)** transforms verbose JSON:API responses into mobile-optimized payloads, reducing response size by 60-70% for low-end devices [^75^].

### 4.3 Multi-Tenancy & Regional Architecture

#### 4.3.1 Country-Scoped Tenant Isolation

MkulimaForum uses shared-database multi-tenancy with PostgreSQL Row-Level Security (RLS). Each table carries a `country_code` column (TZ, KE, UG, RW) as the tenant key [^33^][^36^].

```
+----------------------------------------------------------------------------------+
|                         MULTI-TENANT DATA FLOW                                   |
+----------------------------------------------------------------------------------+
|                                                                                  |
|  +---------+     +---------------+     +--------------------+                     |
|  | Client  |     | Subdomain:    |     | Tenant Resolution   |                    |
|  | Request |---->| tz.mkulima    |---->| Middleware sets     |                    |
|  |         |     | X-Country: TZ |     | app.current_tenant  |                    |
|  +---------+     +---------------+     +----------+---------+                     |
|                                                   |                              |
|                                                   v                              |
|                                       +-----------v-----------+                  |
|                                       | PostgreSQL RLS Policy |                  |
|                                       | USING (country_code = |                  |
|                                       | current_setting(...)) |                  |
|                                       +-----------+-----------+                  |
|                                                   |                              |
|                                       +-----------v-----------+                  |
|                                       | TenantAwareModel      |                  |
|                                       | addGlobalScope(new    |                  |
|                                       |   TenantScope)        |                  |
|                                       +-----------+-----------+                  |
|                                                   |                              |
|                                       +-----------v-----------+                  |
|                                       | Result: TZ rows only  |                  |
|                                       +-----------------------+                  |
+----------------------------------------------------------------------------------+
```

The `TenantAwareModel` base class applies a global Eloquent scope filtering all queries by the resolved tenant, with `country_code` auto-populated on creation [^33^]. Even if application code bypasses the scope, RLS enforces isolation at the database layer.

#### 4.3.2 Data Sovereignty

Data residency is a compliance requirement and trust signal [^9^]. Tanzania's PDPA 2022 prohibits cross-border transfers except under specific conditions [^62^]; Kenya's DPA 2019 mandates data controller registration [^63^]. Primary deployment targets AWS `af-south-1` (Cape Town) at 45-65 ms latency [^72^][^73^]. AWS Nairobi Local Zone (`af-south-1-nbo-1a`) targets under 20 ms [^69^]. CloudFront caches assets across East Africa. Data is encrypted at rest (AES-256-GCM) and in transit (TLS 1.3). The roadmap includes in-country hosting for strict localization markets, with the shared-database pattern enabling per-tenant migration without affecting others.

#### 4.3.3 Regional Customization

Each tenant maintains independent **mobile money configs** — Tanzania: M-Pesa, Tigo Pesa, Airtel Money; Kenya: Safaricom M-Pesa via Daraja 3.0 (12,000 TPS); Uganda: MTN MoMo; Rwanda: MTN MoMo, Airtel-Tigo. **Regulatory modules** load per-country: TFRA for Tanzanian agrodealers, KEPHIS for Kenyan seeds, NARO for Uganda, RAB for Rwanda. **Crop disease knowledge bases** feed country-specific RAG pipelines — Tanzania weights TARI Fall Armyworm research; Kenya prioritizes KALRO maize and coffee leaf rust. **Language packs** default to `sw_TZ`, `sw_KE`/`en_KE`, `en_UG`/`lg_UG`, and `rw_RW`/`fr_RW` respectively.

This architecture enables country expansion without code changes — only configuration, knowledge base seeding, and regulatory module activation. The RLS-based pattern scales to thousands of tenants per instance [^33^], providing headroom well beyond the four-country launch.
