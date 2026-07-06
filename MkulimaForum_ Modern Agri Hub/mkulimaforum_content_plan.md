# MkulimaForum Architecture Document — Detailed Content Plan

> **Version:** 1.0  
> **Date:** July 2025  
> **Purpose:** Actionable chapter-by-chapter content specifications for writers  
> **Source Materials:** Requirements Analysis v1.0 | Research Synthesis (7 dimensions, 200+ sources) | 10 Strategic Insights | Cross-Verification Report

---

## How to Use This Document

Each chapter specification includes:
- **Opening hook** — first paragraph guidance
- **Key tables** — exact column headers, row count, and data sources
- **Code examples** — language, filename, purpose, line count
- **Architecture diagrams** — diagram type, title, elements to include
- **Key data points** — specific statistics with citations
- **Connection points** — explicit forward/backward references to other chapters
- **Visual elements** — callout boxes, info cards, charts to insert

---

## Chapter 1: Executive Summary & Vision (~800 words)

### Purpose
Set the strategic stage. A busy stakeholder should understand MkulimaForum's mission, market opportunity, and differentiator in under 5 minutes.

### Opening Hook
Begin with the paradox: "East Africa's agriculture sector contributes 25-40% of GDP, yet 40% of fresh produce never reaches market. Meanwhile, 50,000+ extension officer positions sit unfilled, and the region's 75M+ smallholder farmers remain digitally underserved. MkulimaForum exists to close these gaps."

### Section Structure
1. **The Agricultural Opportunity** (150 words)
2. **The MkulimaForum Vision** (150 words)
3. **What Makes MkulimaForum Different** (200 words)
4. **Platform at a Glance** (150 words)
5. **Document Roadmap** (150 words)

### Key Tables

**Table 1: East African Agricultural Digital Opportunity Dashboard**
| Column Headers | `Country` | `Agriculture GDP Share` | `Farmer Population` | `Smartphone Penetration` | `Extension Ratio` | `Post-Harvest Loss` | `Mobile Money Users` |
|---|---|---|---|---|---|---|---|
| Rows | Tanzania, Kenya, Uganda, Rwanda, **EAC Total** |
| Data | TZ: 26.2% / 11.2M farmers / 41.8% smartphone / 1:1,172 / 40% / 25M+ M-Pesa; KE: 26% / 7.5M farmers / 40-50% / 1:1,380 / 40% / 30M+ M-Pesa; UG: 24% / 8M farmers / ~35% / 1:1,800 / 40% / 10M+ MoMo; RW: 43.7% employment / 1.2M farmers / ~40% / 1:500+ / 35% / 5M+ MoMo |
| Source | Research Synthesis §1.1-1.2 |

**Table 2: Competitive Positioning Matrix**
| Column Headers | `Platform` | `Type` | `Countries` | `Offline` | `AI Features` | `Marketplace` | `Services` | `Voice` | `Community` |
|---|---|---|---|---|---|---|---|---|---|
| Rows | DigiFarm, FarmerChat, Apollo, One Acre Fund, Maathai, MkulimaForum (highlighted row) |
| Source | Research Synthesis §1.3 |

### Code Examples
None in this chapter.

### Architecture Diagrams

**Diagram 1: MkulimaForum Ecosystem Map** (Conceptual diagram)
- Type: Ecosystem/network diagram
- Elements: Central MkulimaForum hub; surrounding nodes: Farmers (primary), Agrodealers, Agronomists, Veterinary Officers, Logistics Providers, Warehouse Operators, Soil Labs, Government Partners (TARI, KALRO, NARO, RAB), Cooperatives/SACCOs, Research Institutions
- Flow arrows: Show data, money, and service flows between actors
- Style: Circular layout with MkulimaForum at center

### Key Data Points (with Citations)
- "East African agriculture contributes **25-40% of GDP** across Partner States" (EAC statistics)
- "**40% post-harvest losses** represent **$4.5B annual loss** in East Africa" (Research Synthesis §1.2)
- "Extension officer ratios are critically low: **1:1,380 in Kenya**, **1:1,172 in Tanzania** vs FAO standard of 1:400" (Research Synthesis §1.2)
- "Smartphone penetration in Tanzania: **41.8%**; feature phone ownership: **77.5%**" (TCRA 2025, Dim 01)
- "Mobile money processes **$1+ trillion annually** across Africa" (GSMA State of the Industry Report 2025)
- "Fall Armyworm causes up to **$13B in annual losses** across Sub-Saharan Africa" (Research Synthesis §1.5)
- "AI extension delivery costs **$1-3/farmer/year** vs **$35/farmer/year** for traditional extension — a 10x reduction" (Cross-Insight #2)
- "Cold chain market: **$12.87B (2025) → $18.29B by 2032** at 5.1% CAGR" (Research Synthesis §1.2)

### Connection Points
- **Forwards to:** Chapter 2 (East African Context — deeper market data), Chapter 3 (Platform Overview — module breakdown), Chapter 4 (System Architecture — how it all fits together)
- **Backwards from:** None (first chapter)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "MkulimaForum at a Glance" — bullet card with: 5 core modules, 4 countries, 5 mobile money providers, 3 AI modalities (vision, voice, RAG), 8 user roles
- **Callout:** "The 50,000 Extension Officer Gap" — highlight the AI replacement opportunity
- **Chart:** Simple bar chart showing EAC agriculture GDP share by country (TZ 26.2%, KE 26%, UG 24%, RW 43.7% employment)

---

## Chapter 2: East African Context & Opportunity (~1,500 words)

### Purpose
Ground the architecture in real market conditions. Demonstrate deep understanding of the operational environment.

### Opening Hook
"Building agricultural technology for East Africa requires understanding that this is not a smaller version of Western markets — it is a fundamentally different operating environment. Internet subscriptions reach 87% of Tanzanians, but actual internet users number just 29%. The gap between connectivity infrastructure and real usage defines every architectural decision in MkulimaForum."

### Section Structure
1. **The Digital Divide** (300 words) — connectivity, device, gender gaps
2. **The Agricultural Landscape** (350 words) — GDP share, farmer demographics, key crops per country
3. **Extension Service Crisis** (250 words) — ratios, cost, AI opportunity
4. **Competitive Landscape** (300 words) — existing platforms, their strengths/gaps
5. **Why Now?** (150 words) — convergence of mobile money maturity, AI cost decline, policy support
6. **Implications for Architecture** (150 words) — how context drives design

### Key Tables

**Table 1: Digital Adoption & Connectivity Gap**
| Column Headers | `Metric` | `Tanzania` | `Kenya` | `Uganda` | `Rwanda` | `Implication for Design` |
|---|---|---|---|---|---|---|
| Rows | Mobile penetration; Smartphone penetration; Internet subscriptions; Actual internet users; Rural internet use; 4G coverage; Women mobile internet; Feature phone ownership |
| Data | TZ: 99.3% mobile / 41.8% smartphone / 56.3M subs (87%) / 20.6M actual (29.1%) / 7.7% rural / 94.2% 4G / 24% women / 77.5% feature phone |
| Source | TCRA 2025, FinAccess 2024, Dim 01 |

**Table 2: Agriculture by Country — Key Metrics**
| Column Headers | `Country` | `Ag GDP Share` | `Farmer Pop` | `Key Crops` | `Extension Ratio` | `Research Partner` | `Regulatory Body` |
|---|---|---|---|---|---|---|---|
| Source | Research Synthesis §1.2, §6.1 |

**Table 3: Platform Competitive Analysis**
| Column Headers | `Platform` | `Users/Scale` | `Countries` | `Offline?` | `Key Strength` | `Critical Gap` | `MkulimaForum Advantage` |
|---|---|---|---|---|---|---|---|
| Rows | DigiFarm (1.6M reg), FarmerChat (830K+), Apollo (100K+), One Acre Fund (1M target), Maathai, iProcure, Wefarm (1.8M) |
| Source | Research Synthesis §1.3 |

**Table 4: Pest & Disease Priority Threats**
| Column Headers | `Disease/Threat` | `Annual Impact` | `Geographic Scope` | `Digital Response Gap` |
|---|---|---|---|---|
| Rows | Fall Armyworm ($13B), Banana Xanthomonas Wilt (30-100% yield loss), Coffee Leaf Rust (83-97% Rwanda), Maize Lethal Necrosis, Cassava Brown Streak Disease, Post-harvest losses (40%) |
| Source | Research Synthesis §1.5 |

### Code Examples
None.

### Architecture Diagrams

**Diagram 1: East African Agritech Competitive Landscape**
- Type: Positioning matrix (2x2 or bubble chart)
- X-axis: Scale (users)
- Y-axis: Feature breadth (single-feature → super-app)
- Bubble size: Geographic coverage
- MkulimaForum positioned as high-breadth, planned high-scale

### Key Data Points (with Citations)
- "Tanzania: **99.3% mobile penetration** but only **41.8% smartphone penetration** (TCRA 2025)"
- "Rural internet use in Tanzania: **7.7%** vs urban **27.3%** (2022 Census)"
- "Women's mobile internet access: **24%** (SSA average) vs **35%** for men — a 9-point gender gap" (GSMA 2025)
- "Extension officer ratio in Uganda: **1:1,800** vs FAO standard of 1:400" (Research Synthesis §1.2)
- "Traditional extension costs **~$35/farmer/year**; AI extension costs **~$1-3/farmer/year**" (Cross-Insight #2)
- "Kenya has **15,000+ SACCOS** with **14M members** and **$2B+ annual transactions**" (FinAccess 2024)
- "Cold chain penetration: only **5%** of produce passes through cold chain" (Research Synthesis §1.2)
- "EAC intra-regional agricultural trade: **~65% of trade volume**" (EAC statistics)

### Connection Points
- **Forwards to:** Chapter 3 (Platform Overview — modules solve the problems identified here), Chapter 4 (System Architecture — designed for these constraints)
- **Backwards from:** Chapter 1 (Executive Summary)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "The Connectivity Paradox" — 87% subscriptions vs 29% actual users in Tanzania
- **Info Box:** "Extension Officer Math" — 1:1,380 ratio means 1 officer serves 3.4x more farmers than recommended
- **Callout:** "The Gender Gap" — 24% women vs 35% men mobile internet; voice-first design as inclusion strategy
- **Chart:** Bar chart comparing extension ratios across 4 countries vs FAO standard
- **Chart:** Pie chart of device types (smartphone vs feature phone) across target countries

---

## Chapter 3: Platform Overview — MkulimaForum Modules (~1,200 words)

### Purpose
Introduce the five core modules and eight user roles at the heart of the platform. This is the "what we build" chapter before the "how we build it" chapters.

### Opening Hook
"MkulimaForum is not a feature — it is an ecosystem. Five integrated modules serve eight distinct user types across four countries, unified by a single account, wallet, and trust layer. Where other platforms solve one problem, MkulimaForum connects them all."

### Section Structure
1. **Module Architecture Overview** (200 words) — how 5 modules interlock
2. **Agrodealer Marketplace** (150 words) — inputs, verified dealers, escrow
3. **Plant Disease Scanner** (150 words) — hybrid AI diagnosis
4. **Farmers Forum** (150 words) — community, expert verification, voice
5. **Services Marketplace** (200 words) — agronomist, veterinary, logistics, warehouse, soil testing
6. **AI Agronomist** (150 words) — RAG-powered, voice-enabled, always available
7. **User Roles & Personas** (200 words) — 8 roles, their needs, and journeys

### Key Tables

**Table 1: MkulimaForum Module Matrix**
| Column Headers | `Module` | `Primary Users` | `Key Feature` | `Offline?` | `Revenue Model` | `Priority` |
|---|---|---|---|---|---|---|
| Rows | Agrodealer Marketplace (farmers, agrodealers / multi-vendor + escrow / partial / 3-5% commission / P0); Plant Disease Scanner (farmers / hybrid AI diagnosis / YES / free + premium / P0); Farmers Forum (all / threaded discussions + voice / partial / advertising / P0); Services Marketplace (farmers, providers / booking + scheduling / partial / 8-15% commission / P0); AI Agronomist (farmers / RAG + voice chat / partial / freemium / P0) |
| Source | Requirements §1-2 |

**Table 2: User Roles & Capabilities Matrix**
| Column Headers | `Role` | `Can Access` | `Can Create/Manage` | `Verification Required` | `Monetization` |
|---|---|---|---|---|---|
| Rows | Farmer; Agrodealer; Agronomist; Veterinary Officer; Logistics Provider; Warehouse Operator; Admin; Extension Officer |
| Source | Requirements §5 Stakeholder table, IF-002 RBAC |

**Table 3: Module Interconnection Map**
| Column Headers | `Module` | `Feeds Data To` | `Consumes Data From` | `Shared Components` |
|---|---|---|---|---|
| Example rows | Disease Scanner → AI Agronomist (diagnosis history), Marketplace (product recommendations); AI Agronomist → Forum (auto-FAQ), Services (booking referrals); Services → Marketplace (input orders), Wallet (payments) |
| Source | Cross-Insight #8 (data flywheel) |

### Code Examples
None.

### Architecture Diagrams

**Diagram 1: MkulimaForum Module Hub**
- Type: Hub-and-spoke architectural diagram
- Central: MkulimaForum Core (Auth, Wallet, Notifications, Search)
- 5 spokes: Marketplace, Disease Scanner, Forum, Services, AI Agronomist
- Annotation: Data flows between modules (dashed arrows showing the data flywheel)

### Key Data Points (with Citations)
- "5 core modules serve 8 user roles across 4 countries"
- "Disease scanner links to marketplace: each diagnosis can recommend verified treatment products"
- "Services marketplace creates data flywheel: soil test → agronomist booking → warehouse reservation → logistics dispatch"
- "Forum posts feed RAG knowledge base, improving AI advice for all farmers" (Cross-Insight #8)

### Connection Points
- **Forwards to:** Chapter 4 (System Architecture — technical implementation of these modules), Chapter 5 (Laravel Backend), Chapters 9-12 (deep dives into each module)
- **Backwards from:** Chapter 2 (East African Context — modules solve the problems identified)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "The Data Flywheel" — circular diagram showing: Forum posts → RAG KB → AI Advice → Farmer actions → Service bookings → Better recommendations
- **Info Box:** "One Account, One Wallet, Eight Roles" — a farmer can also be an agrodealer; role switching within same account
- **Callout:** "Trust by Design" — every module incorporates KYC, verification, and escrow

---

## Chapter 4: System Architecture — High-Level Design (~1,800 words)

### Purpose
Present the complete system architecture using C4 model approach. Show how all components interact across layers.

### Opening Hook
"MkulimaForum's architecture is designed for the reality of East African infrastructure: intermittent connectivity, low-end devices, multiple mobile money providers, and four regulatory regimes. Every layer — from the Flutter app running on a $80 Android phone to the Laravel backend in AWS Cape Town — is optimized for these constraints."

### Section Structure
1. **Architectural Principles** (250 words) — 6 guiding principles
2. **C4 Context Diagram** (200 words) — system boundary, external actors
3. **C4 Container Diagram** (400 words) — app, API, databases, external services
4. **Layered Architecture** (350 words) — presentation, application, domain, infrastructure
5. **Multi-Tenancy Design** (250 words) — country-scoped tenants
6. **Offline-First Strategy** (200 words) — sync, conflict resolution, queues
7. **Integration Architecture** (150 words) — external APIs and services

### Key Tables

**Table 1: Architectural Principles**
| Column Headers | `Principle` | `Rationale` | `Implementation` |
|---|---|---|---|
| Rows | Offline-First (rural connectivity / Drift + Hive + sync engine); Trust-by-Design (counterfeit inputs, fraud / KYC + escrow + verification); Voice-First Inclusion (literacy, gender gap / VSL microservice); Multi-Country from Day 1 (4 markets, 4 regulators / PostgreSQL RLS + tenant scoping); AI-Everywhere (extension gap / RAG + edge ML + cloud LLM); Cost Efficiency (farmer affordability / Gemini Flash $0.075/1M tokens) |
| Source | Cross-Insights #1-10 |

**Table 2: Technology Stack Summary**
| Column Headers | `Layer` | `Technology` | `Version` | `Justification` |
|---|---|---|---|---|
| Rows | Backend Framework (Laravel / 11.x / streamlined, fast); Mobile Frontend (Flutter / 3.24+ / Impeller, offline-first); Database (PostgreSQL / 16+ / pgvector, PostGIS, RLS); Cache/Queue (Redis / 7.x / dual use, Horizon); Vector DB (pgvector / latest / 471 QPS at 50M vectors); Search (Meilisearch / 1.x / Swahili support, faceted); State Management (BLoC / 8.x / predictable, testable); Local DB (Drift + Hive / latest / type-safe SQLite); Real-Time (Laravel Reverb / latest / 90% cost reduction); Container Server (FrankenPHP / latest / 5-10x throughput); Auth (Laravel Sanctum / 4.x / lightweight tokens); RBAC (Spatie Permission / 6.x / mature); Maps (Mapbox / latest / offline tiles, cost-effective) |
| Source | Requirements §4, Research Synthesis §2.1 |

**Table 3: External Service Integrations**
| Column Headers | `Service` | `Purpose` | `Integration Type` | `Fallback` | `Cost at Scale` |
|---|---|---|---|---|---|
| Rows | Gemini 2.0 Flash (AI responses / REST API / OpenAI / $21/mo @ 50K queries); Africa's Talking (SMS/USSD / REST API / Twilio / $0.0075/SMS); M-Pesa Daraja 3.0 (KE payments / REST API / Airtel Money / MNO fees); MTN MoMo Open API (UG+RW payments / REST API / Airtel / MNO fees); Open-Meteo (weather / REST API / NASA POWER / free); Mapbox (maps/routing / SDK / OSM / ~$2,325/mo); Google Cloud TTS (Swahili voice / REST API / Azure / $16/M chars); iSDAsoil (soil data / REST API / none / free); FCM (push notifications / SDK / SMS / free tier) |
| Source | Requirements §4, Research Synthesis §2.1, §4 |

### Code Examples

**Code 1: Multi-Tenancy — Tenant Resolution Middleware**
```php
// app/Http/Middleware/ResolveTenant.php
// ~25 lines
// Purpose: Resolve country tenant from subdomain, path, or header
// Show: handle() method with fallback chain (subdomain → header → JWT claim → default)
```

**Code 2: PostgreSQL RLS Policy for Tenant Isolation**
```sql
-- database/migrations/tenant_rls.sql
-- ~20 lines
-- Purpose: Row-level security enforcing country_code scoping
-- Show: CREATE POLICY, ENABLE ROW LEVEL SECURITY, tenant-scoped SELECT
```

### Architecture Diagrams

**Diagram 1: C4 Context Diagram**
- Type: C4 Context (System Context)
- Elements: MkulimaForum System (center); Actors: Smallholder Farmer, Agrodealer, Agronomist, Veterinary Officer, Logistics Provider, Warehouse Operator, Platform Admin, Government Partner; External Systems: M-Pesa/MTN MoMo, Africa's Talking, Gemini API, Mapbox, Open-Meteo, TARI/KALRO/NARO/RAB, Google Cloud Storage
- Style: Standard C4 notation

**Diagram 2: C4 Container Diagram**
- Type: C4 Container
- Containers: Flutter Mobile App, Flutter Web (PWA), USSD Gateway, Laravel API, Laravel Reverb (WebSockets), PostgreSQL Primary, PostgreSQL Read Replicas, Redis Cache/Queue, Meilisearch, FrankenPHP/Octane, S3/GCS Storage
- Annotations: Protocols, technologies, responsibilities

**Diagram 3: Multi-Tenant Data Flow**
- Type: Data flow diagram
- Shows: Request → Load Balancer → Tenant Resolution → RLS Policy → Scoped Query → Response
- Per-country flow: tz.mkulimaforum.com → TZ tenant → TZ data only

**Diagram 4: Offline-First Sync Architecture**
- Type: Component diagram
- Components: Flutter App → Drift Local DB → SyncEngine (OutboxService + PushService + PullService + ConflictService) → REST API → PostgreSQL
- Show: CRDT conflict resolution path

### Key Data Points (with Citations)
- "FrankenPHP delivers **5-10x throughput** over PHP-FPM" (Research Synthesis §2.1)
- "Laravel Reverb reduces real-time costs by **90%** vs Pusher ($1,200/yr → ~$60/yr)" (Research Synthesis §2.1)
- "pgvector achieves **471 QPS at 28ms p95** with 50M vectors" (Research Synthesis §2.1)
- "AWS af-south-1 (Cape Town) offers **45-65ms latency** from East Africa" (Research Synthesis §2.1)
- "PWA fallback: **1-5MB** vs 50-200MB native app" (Research Synthesis §6.1)
- "BLoC state management + Drift SQLite enables offline-first with type-safe queries" (Research Synthesis §2.1)

### Connection Points
- **Forwards to:** Chapter 5 (Laravel Backend — deeper backend patterns), Chapter 6 (Database Architecture), Chapter 7 (AI/ML Integration), Chapter 8 (Flutter Frontend)
- **Backwards from:** Chapter 3 (Platform Overview — modules being architected)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "C4 Model Primer" — brief explanation of Context/Container/Component/Code levels for non-technical readers
- **Callout:** "The Offline-First Imperative" — 72 hours of offline functionality for critical features
- **Callout:** "Trust Architecture" — KYC + escrow + verification as foundational layers
- **Chart:** Technology stack layers visual (presentation → application → domain → infrastructure)

---

## Chapter 5: Laravel Backend Architecture (~2,000 words)

### Purpose
Deep dive into the backend framework decisions, patterns, and implementations. Show production-ready Laravel architecture.

### Opening Hook
"Laravel 11 powers MkulimaForum's backend — not because it is the only option, but because it offers the fastest path from idea to production in East African conditions. With per-second rate limiting, built-in health checks, 15-20% faster bootstrapping than Laravel 10, and a package ecosystem that handles multi-tenancy, RBAC, and media management, it is the pragmatic choice for a team building for four countries simultaneously."

### Section Structure
1. **Why Laravel 11** (200 words) — key improvements, ecosystem
2. **Application Structure** (250 words) — modular monolith, domain separation
3. **Authentication & Authorization** (250 words) — Sanctum, RBAC, multi-factor
4. **Multi-Tenancy Implementation** (300 words) — RLS, scopes, tenant resolution
5. **API Design Patterns** (300 words) — BFF, JSON:API, delta sync, field selection
6. **Background Processing** (200 words) — queues, Horizon, scheduled tasks
7. **Real-Time with Reverb** (200 words) — WebSockets, broadcasting, presence
8. **Testing & Quality** (150 words) — PHPUnit, coverage targets, CI/CD
9. **Key Service Classes** (150 words) — patterns for extensibility

### Key Tables

**Table 1: Laravel Package Ecosystem**
| Column Headers | `Package` | `Purpose` | `Version` | `Key Feature` |
|---|---|---|---|---|
| Rows | spatie/laravel-permission (RBAC / 6.x / role-permission middleware); spatie/laravel-multitenancy (multi-tenant / 4.x / region-based tenancy); spatie/laravel-media-library (images / 11.x / auto conversions, responsive); laravel/scout (search / latest / Meilisearch driver); laravel/sanctum (auth / 4.x / API tokens); laravel/horizon (queue monitoring / latest / dashboard); laravel/reverb (WebSockets / latest / first-party); openai-php/client (LLM / latest / provider abstraction) |
| Source | Requirements §4 |

**Table 2: API Design Pattern Comparison**
| Column Headers | `Pattern` | `Implementation` | `Benefit` | `Use Case` |
|---|---|---|---|---|
| Rows | BFF (Backend-for-Frontend / Separate mobile API / Reduced payload / Flutter app); Delta Sync (`/sync?since=timestamp` / Only changed data / Offline-first sync); Field Selection (`?fields=title,body` / Reduced payload / Slow connections); Compound Documents (`?include=author,comments` / Eliminates N+1 / Forum posts); Cursor Pagination (Cursor-based / Stable ordering / Infinite scroll); Brotli Compression (Brotli/gzip / Faster transfers / All endpoints) |
| Source | Research Synthesis §6.6 |

**Table 3: Queue Architecture**
| Column Headers | `Queue` | `Purpose` | `Priority` | `Workers` | `Retry Policy` |
|---|---|---|---|---|---|
| Rows | default (general API tasks / medium / 2 / 3x exponential); payments (mobile money requests / critical / 4 / 5x 60s); notifications (FCM + SMS + email / high / 3 / 2x 30s); ai-ml (Gemini API calls / medium / 2 / 3x 10s); uploads (image processing / low / 1 / 2x 60s); sync (offline data sync / high / 3 / 3x 10s); kyc (document verification / low / 1 / manual); reports (analytics generation / lowest / 1 / 1x 300s) |
| Source | Derived from requirements |

### Code Examples

**Code 1: Tenant Resolution Middleware**
```php
// app/Http/Middleware/ResolveTenant.php
// ~40 lines
// Shows: subdomain extraction (tz.mkulimaforum.com), header fallback (X-Country-Code),
// JWT claim fallback, default to TZ, setTenant() on app container
```

**Code 2: RBAC Permission Definition**
```php
// database/seeders/RolesAndPermissionsSeeder.php
// ~50 lines
// Shows: Role::create(['name' => 'farmer']), Permission::create(['name' => 'marketplace:buy']),
// $role->givePermissionTo(...), Spatie middleware usage in routes
```

**Code 3: Delta Sync API Endpoint**
```php
// app/Http/Controllers/Api/SyncController.php
// ~35 lines
// Shows: public function deltaSync(Request $request) with since timestamp,
// sync token generation, chunking (100 records), client_watermark tracking
```

**Code 4: Laravel Reverb Broadcasting Event**
```php
// app/Events/OrderStatusUpdated.php
// ~25 lines
// Shows: implements ShouldBroadcast, channel definition, payload with country scoping,
// broadcastOn() with private channel
```

**Code 5: Multi-Tenant RLS Policy**
```sql
-- database/migrations/xxxx_add_tenant_rls.php
-- ~30 lines
-- Shows: ALTER TABLE orders ENABLE ROW LEVEL SECURITY,
-- CREATE POLICY tenant_isolation ON orders USING (country_code = current_setting('app.current_tenant')),
-- DB::statement("SET app.current_tenant = 'tz'")
```

### Architecture Diagrams

**Diagram 1: Laravel Application Architecture**
- Type: Layered architecture diagram
- Layers: Routes → Middleware (Auth, Tenant, Locale, Throttle) → Controllers → Services (Domain) → Repositories → Models (Eloquent) → PostgreSQL
- Side: Events → Listeners → Queues; Broadcasting → Reverb

**Diagram 2: Multi-Tenancy Request Flow**
- Type: Sequence diagram
- Steps: Client Request → Load Balancer → TenantResolver Middleware → RLS SET statement → Scoped Query → Response
- Show: How tz.mkulimaforum.com gets only Tanzania data

**Diagram 3: Queue Architecture**
- Type: Component diagram
- Components: API → Redis Queues → Horizon Workers (multiple pools) → PostgreSQL
- Label: Priority lanes, worker allocation, retry policies

### Key Data Points (with Citations)
- "Laravel 11 is **15-20% faster** to bootstrap than Laravel 10" (Laravel 11 release notes)
- "FrankenPHP + Octane = **5-10x throughput** over PHP-FPM" (Research Synthesis §2.1)
- "Laravel Reverb = **90% cost reduction** vs Pusher for WebSockets" (Research Synthesis §2.1)
- "Spatie Permission package: **mature, cache-friendly** RBAC" (Research Synthesis §2.1)
- "Target: **>80% test coverage** (PHPUnit + integration)" (Requirements NF-018)
- "API response time: **<500ms** p95 for all endpoints, **<200ms** for cached reads" (Requirements NF-002)

### Connection Points
- **Forwards to:** Chapter 6 (Database Architecture — PostgreSQL specifics), Chapter 13 (API Design — detailed API specs), Chapter 17 (DevOps — deployment of Laravel)
- **Backwards from:** Chapter 4 (System Architecture — backend is a core container)
- **Parallel with:** Chapter 8 (Flutter Frontend — consumes this backend)

### Visual Elements
- **Info Box:** "Laravel 11 Key Improvements" — streamlined structure, per-second rate limiting, health checks
- **Callout:** "The BFF Pattern" — why mobile-specific API endpoints matter for bandwidth-constrained users
- **Callout:** "Queue Priority Lanes" — payments process before analytics
- **Chart:** Package dependency graph (Laravel core → Spatie packages → AI client → Payment gateways)

---

## Chapter 6: Database Architecture (~1,500 words)

### Purpose
Detail PostgreSQL schema design, multi-tenancy via RLS, partitioning strategy, and vector search with pgvector.

### Opening Hook
"MkulimaForum's database stores everything from marketplace orders to disease scan embeddings, from forum threads to farm GPS boundaries. A single PostgreSQL 16 instance handles relational data, geospatial queries, and vector similarity search — eliminating the operational complexity of managing multiple databases in East African cloud regions."

### Section Structure
1. **Why PostgreSQL 16** (150 words) — PostGIS, pgvector, JSONB, RLS, partitioning
2. **Schema Design Overview** (300 words) — core tables, relationships, conventions
3. **Multi-Tenancy via RLS** (250 words) — tenant_id, country_code, policies
4. **Partitioning Strategy** (200 words) — time-based for orders and transactions
5. **Geospatial with PostGIS** (200 words) — farm boundaries, routing, pest alerts
6. **Vector Search with pgvector** (200 words) — RAG embeddings, similarity search
7. **Read Replicas & Performance** (150 words) — read replica strategy, connection pooling
8. **Migration & Seeding Strategy** (50 words)

### Key Tables

**Table 1: Core Schema Overview**
| Column Headers | `Table` | `Purpose` | `Key Columns` | `Relationships` | `Partitioned?` |
|---|---|---|---|---|---|
| Rows | users (auth + profile / phone, country_code, roles, kyc_status → roles); farms (farmer farms / gps_boundary PostGIS, size_acres, soil_type → users); products (marketplace / name, price, currency, stock, dealer_id → users); orders (transactions / status, total, currency, escrow_status → users, products); disease_scans (AI diagnosis / image_path, diagnosis, confidence, crop_type → users, farms); forum_posts (community / title, body, category, votes → users); service_bookings (services / type, status, scheduled_at, provider_id → users); embeddings (RAG / content_type, vector(1536), metadata → polymorphic); wallets (payments / balance, currency, escrow_balance → users); kyc_documents (verification / doc_type, status, verified_by → users) |
| Source | Derived from requirements §1-2 |

**Table 2: pgvector Performance Benchmarks**
| Column Headers | `Solution` | `QPS at 50M Vectors` | `Latency p95` | `Infra Cost` | `ACID?` |
|---|---|---|---|---|---|
| Rows | pgvector + pgvectorscale (**471** / **28ms** / $0 (same DB) / Yes); Qdrant (41 / 45ms / separate cluster / Partial); Weaviate (~200 / 35ms / separate cluster / Partial); Pinecone (~350 / 30ms / $70-200/mo / No) |
| Source | Research Synthesis §2.1, §3.2 |

**Table 3: Partitioning Strategy**
| Column Headers | `Table` | `Partition Key` | `Partition Type` | `Retention` | `Rationale` |
|---|---|---|---|---|---|
| Rows | orders (created_at / monthly range / 24 months / high write volume); transactions (created_at / monthly range / 36 months / financial audit trail); disease_scans (created_at / monthly range / 12 months / high volume, image cleanup); activity_logs (created_at / daily range / 90 days / high volume, ephemeral); forum_posts (country_code / list / permanent / per-country moderation) |
| Source | Requirements NF-014, original architecture §4.2 |

**Table 4: Geospatial Data Types**
| Column Headers | `Entity` | `Geometry Type` | `SRID` | `Index` | `Query Type` |
|---|---|---|---|---|---|
| Rows | Farm boundary (Polygon / 4326 / GIST / area, intersection); Warehouse location (Point / 4326 / GIST / nearest-neighbor); Delivery route (LineString / 4326 / GIST / path distance); Pest alert zone (Polygon / 4326 / GIST / containment, overlap); Agrodealer location (Point / 4326 / GIST / nearest-neighbor, radius) |
| Source | Requirements IF-012, Research Synthesis §5.3 |

### Code Examples

**Code 1: pgvector Similarity Search Query**
```sql
-- ~15 lines
-- Purpose: Find most similar agricultural knowledge embeddings
-- Shows: SELECT with embedding <=> query_embedding (L2 distance),
-- ORDER BY distance LIMIT 5, with tenant scoping
```

**Code 2: RLS Policy Setup**
```sql
-- ~25 lines
-- Purpose: Complete tenant isolation setup
-- Shows: ENABLE ROW LEVEL SECURITY, CREATE POLICY, SET ROLE,
-- per-country enforcement
```

**Code 3: PostGIS Farm Boundary Query**
```sql
-- ~15 lines
-- Purpose: Find farms within pest alert zone
-- Shows: ST_Within(farm.boundary, alert.zone), ST_Area, ST_Distance
```

**Code 4: Eloquent Model with Tenant Scope**
```php
// app/Models/Traits/TenantAware.php
// ~20 lines
// Shows: booted() static method adding global scope,
// where country_code = current tenant, apply automatically
```

### Architecture Diagrams

**Diagram 1: Entity-Relationship Diagram (Core Tables)**
- Type: ER diagram
- Entities: users, farms, products, orders, disease_scans, forum_posts, service_bookings, embeddings, wallets, kyc_documents
- Relationships: Show foreign keys, polymorphic associations, many-to-many via pivot tables
- Highlight: tenant_id on all tables, country_code as partition key

**Diagram 2: Multi-Tenancy RLS Enforcement**
- Type: Data flow / sequence diagram
- Shows: Query → RLS Policy Check → country_code match → Result filtering
- Annotation: Database-level enforcement, no application bypass

**Diagram 3: Partitioning Visual**
- Type: Timeline diagram
- Shows: orders_2025_01, orders_2025_02, ... with attach/detach operations
- Highlight: Automated partition creation via cron

### Key Data Points (with Citations)
- "pgvector + pgvectorscale: **471 QPS at 28ms p95** with 50M vectors — 11x faster than Qdrant" (Research Synthesis §2.1)
- "Zero additional infrastructure cost for vector search — same PostgreSQL instance" (Research Synthesis §2.1)
- "PostGIS handles farm boundaries, routing, pest alert geofencing natively" (Requirements IF-012)
- "Monthly partitioning for orders enables **efficient archival** and **query performance**" (Requirements NF-014)
- "Connection pooling via PgBouncer for **50K-100K concurrent users**" (Requirements NF-004)

### Connection Points
- **Forwards to:** Chapter 7 (AI/ML — pgvector for RAG embeddings), Chapter 9-12 (module data models), Chapter 14 (Payments — wallet/escrow schema)
- **Backwards from:** Chapter 4 (System Architecture — database is core container), Chapter 5 (Laravel — Eloquent models)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "Why Single Database?" — operational simplicity in African cloud regions; fewer vendors, simpler DR
- **Callout:** "pgvector vs Dedicated Vector DB" — performance comparison at 50M vectors
- **Callout:** "PostGIS for Agriculture" — geospatial queries for farms, warehouses, logistics, pest alerts
- **Chart:** Partitioning timeline showing monthly partitions for orders table



---

## Chapter 7: AI/ML Integration Architecture (~2,000 words)

### Purpose
The deepest technical chapter. Cover the full AI stack: RAG pipeline, disease scanner, voice AI, soil analysis, and LLM orchestration. This is MkulimaForum's biggest differentiator.

### Opening Hook
"MkulimaForum's AI architecture exists to solve a simple, devastating problem: there are not enough extension officers. At a ratio of 1:1,380 in Kenya and 1:1,172 in Tanzania, the human expertise gap cannot be closed by hiring alone. AI fills the gap — but only if it works offline, speaks Swahili, costs less than $1 per farmer per year, and understands East African crops. This chapter details how MkulimaForum achieves all four."

### Section Structure
1. **AI Architecture Overview** (200 words) — 3-layer AI (edge, application, cloud)
2. **LLM Selection & Cost Analysis** (250 words) — Gemini 2.0 Flash primary, fallbacks
3. **RAG Pipeline Design** (350 words) — knowledge ingestion, embedding, retrieval, generation
4. **Plant Disease Scanner** (350 words) — hybrid on-device + cloud, model selection, accuracy
5. **Voice Service Layer (VSL)** (300 words) — STT → LLM → TTS pipeline, Swahili support
6. **Soil Analysis & Crop Recommendations** (200 words) — XGBoost, iSDAsoil integration
7. **AI Agronomist Module** (200 words) — personalized advice, crop calendar, weather integration
8. **Continuous Learning & Feedback** (150 words) — farmer feedback loops, model improvement

### Key Tables

**Table 1: LLM Cost Comparison at 50K Queries/Month**
| Column Headers | `Model` | `Input Cost/1M` | `Output Cost/1M` | `Context Window` | `Swahili Quality` | `Monthly Cost @ 50K q` | `Role` |
|---|---|---|---|---|---|---|---|
| Rows | Gemini 2.0 Flash ($0.075 / $0.30 / 1M tokens / Excellent / **$21** / **Primary**); GPT-4o ($2.50 / $10.00 / 128K / Good / $700 / Complex fallback); Claude 3.5 Sonnet ($3.00 / $15.00 / 200K / Good / $945 / Long-context fallback); GPT-4o-mini ($0.15 / $0.60 / 128K / Good / $42 / Budget fallback); Llama 3 self-hosted ($0 / $0 / 128K / Fine-tune dependent / Infra cost only / Offline, data sovereignty) |
| Source | Research Synthesis §3.1 |

**Table 2: Disease Detection Model Comparison**
| Column Headers | `Model` | `Size` | `Top-1 Accuracy` | `Platform` | `Offline?` | `East African Crops?` | `Use Case` |
|---|---|---|---|---|---|---|---|
| Rows | MobileNetV3-Small (**2.54 MB** / **67.7%** / Android NNAPI / Yes / Partial / Primary on-device); MobileNetV3-Large (2.96 MB / 73% / Android NNAPI+GPU / Yes / Partial / Higher accuracy edge); DenseNet201 PlantVillage (~30 MB / 96% / Flutter/Android / Yes / No / Lab-accurate, rare diseases); PlantVillage Nuru (~5-15 MB / 65-93% / Android TFLite / Yes / Yes / Field-tested); **Gemini Vision** (Cloud / 80-90% / Cloud API / No / Yes / **Complex fallback**); Custom fine-tuned (Variable / 70-85% expected / TFLite / Yes / Yes / Target: 20 diseases) |
| Source | Research Synthesis §3.3 |

**Table 3: Voice AI Service Comparison**
| Column Headers | `Service` | `STT Swahili` | `TTS Swahili` | `Offline?` | `WER` | `Cost` | `Best For` |
|---|---|---|---|---|---|---|---|
| Rows | Whisper Small fine-tuned (Yes / No / Partial / **~17%** / Free self-host / Primary online STT); Whisper Tiny (Yes / No / Yes / ~25-30% / Free / Offline fallback); Google Cloud Speech (Yes sw-KE sw-TZ / Yes WaveNet / No / Good / $0.006/min STT / Cloud STT); Azure Speech (Yes / Yes Daudi M Rehema F / No / Good / $1/hr STT $16/M chars TTS / Cloud TTS); African Whisper (Yes / No / Yes / 15-20% / Free open-source / Optimized offline) |
| Source | Research Synthesis §3.4 |

**Table 4: RAG Knowledge Sources**
| Column Headers | `Source` | `Type` | `Language` | `Update Frequency` | `Trust Level` | `Access` |
|---|---|---|---|---|---|---|
| Rows | TARI research papers (Research / Swahili+English / Quarterly / Government / API+PDF); FAO guidelines (Guidelines / English / Annual / International / Open access); KEPHIS alerts (Alerts / English / Weekly / Government / RSS+API); iSDAsoil data (Soil / API / Real-time / Scientific / Free REST API); Open-Meteo weather (Weather / API / Hourly / Scientific / Free); Farmer forum posts (Community / Swahili+English / Real-time / Peer / Internal); KALRO/NARO/RAB research (Research / English / Quarterly / Government / API+PDF) |
| Source | Research Synthesis §3.2 |

**Table 5: Soil Variable Accuracy (iSDAsoil)**
| Column Headers | `Variable` | `Accuracy (CCC)` | `Use Case` | `Resolution` |
|---|---|---|---|---|
| Rows | pH (0.90 / Lime recommendation / 30m); Organic Carbon (0.85 / Fertilizer planning / 30m); Nitrogen (0.80 / N-fertilizer / 30m); Phosphorus (0.65 / P-fertilizer / 30m); Potassium (0.75 / K-fertilizer / 30m); Clay content (0.88 / Water management / 30m) |
| Source | Research Synthesis §3.6 |

**Table 6: AI Feature Matrix by Module**
| Column Headers | `AI Feature` | `Technology` | `Module` | `Offline?` | `Cost/Farmer/Year` |
|---|---|---|---|---|---|
| Rows | Disease scan (MobileNetV3 + Gemini Vision / Disease Scanner / Partial / ~$0.10); Crop recommendations (RAG + Gemini Flash / AI Agronomist / Partial / ~$0.50); Voice chat (Whisper + Gemini + TTS / AI Agronomist / No / ~$0.30); Soil analysis (XGBoost + iSDAsoil / Services / Yes / Free); Weather advice (RAG + Open-Meteo / AI Agronomist / Partial / ~$0.20); Pest alert geofencing (PostGIS + ML / Forum+Scanner / Partial / ~$0.05); Forum FAQ suggestions (RAG + Gemini / Forum / No / ~$0.20); Price predictions (Time series / Marketplace / No / ~$0.10) |
| Source | Derived from requirements + research |

### Code Examples

**Code 1: RAG Pipeline — Embedding + Retrieval + Generation**
```python
# app/Services/AI/RagPipeline.py
# ~50 lines
# Shows: embed_query() using multilingual model, pgvector similarity search
# with country_code filter, retrieve top-5 chunks, construct prompt with context,
# call Gemini Flash API, return response with citations
```

**Code 2: TensorFlow Lite Model Integration in Flutter**
```dart
// lib/services/ai/disease_scanner.dart
// ~45 lines
// Shows: TFLite interpreter initialization, image preprocessing
# (resize 224x224, normalize), run inference, parse output labels,
# confidence threshold check (>70%), fallback to Gemini Vision if <70%
```

**Code 3: pgvector Similarity Search**
```sql
-- ~15 lines
-- Purpose: Retrieve relevant agricultural knowledge
-- Shows: SELECT content, metadata, embedding <=> $1 as distance
-- FROM knowledge_embeddings WHERE country_code = 'tz'
-- ORDER BY embedding <=> $1 LIMIT 5
```

**Code 4: Whisper Swahili STT Integration**
```python
# app/Services/Voice/SpeechToText.py
# ~35 lines
# Shows: load_model("whisper-small"), transcribe(audio_file, language="sw"),
// return transcript with confidence score, fallback to Google Cloud if WER > 25%
```

**Code 5: iSDAsoil API Query + Fertilizer Recommendation**
```python
# app/Services/AI/SoilAnalysis.py
// ~40 lines
// Shows: query iSDAsoil API with lat/lng, parse nutrient levels,
// XGBoost prediction for crop recommendation, generate fertilizer plan,
// return structured result with Swahili translation
```

**Code 6: Gemini 2.0 Flash API Call with RAG Context**
```python
# app/Services/AI/GeminiClient.py
// ~30 lines
// Shows: construct prompt with system instruction + retrieved context + user query,
// API call with temperature=0.3, max_tokens=512, safety filters,
// parse response, handle rate limiting, fallback chain
```

### Architecture Diagrams

**Diagram 1: MkulimaForum AI Stack Architecture**
- Type: System diagram (3 layers)
- Layer 1 — Edge: TensorFlow Lite (disease scanner), Whisper Tiny (STT), XGBoost (soil), Drift SQLite (cache)
- Layer 2 — Application: RAG Pipeline, VSL (Voice Service Layer), AI Agronomist Service
- Layer 3 — Cloud: Gemini 2.0 Flash, Whisper Small (cloud STT), Google Cloud TTS, iSDAsoil API, Open-Meteo
- Arrows: Show data flow and fallback paths

**Diagram 2: RAG Pipeline Flow**
- Type: Data flow diagram
- Steps: User Query → Intent Classification → Embedding Generation → pgvector Similarity Search → Reranking → Context Assembly → Gemini Flash Generation → Response with Citations → Cache Result
- Side branch: Low confidence → Human Agronomist Review Queue

**Diagram 3: Disease Scanner Hybrid Architecture**
- Type: Decision tree / flowchart
- Start: Farmer captures image → TFLite inference → Confidence >= 70%? → Yes: Show result (offline)
- No: Upload to Gemini Vision → Confidence >= 85%? → Yes: Show result + treatment
- No: Flag for human agronomist review → Add to improvement dataset
- Loop: Farmer feedback → Active learning → Model retraining

**Diagram 4: Voice Service Layer (VSL) Architecture**
- Type: System diagram
- Pipeline: Audio Input → STT (Whisper) → Text Normalization → Intent Router → (RAG query / Direct LLM / Command handler) → Response Generation → TTS (Google Cloud sw-TZ) → Audio Output
- Parallel: USSD voice callback path via Africa's Talking

### Key Data Points (with Citations)
- "Gemini 2.0 Flash: **$0.075/1M input tokens**, 1M context window, excellent Swahili — **30-60x cheaper** than GPT-4o" (Research Synthesis §3.1)
- "AI extension delivery: **$1-3/farmer/year** vs **$35 traditional** — 10x cost reduction" (Cross-Insight #2)
- "MobileNetV3-Small: **2.54MB**, runs on any Android device, **67.7% top-1 accuracy**" (Research Synthesis §3.3)
- "Models suffer **10-40% accuracy drop** in real field conditions vs lab datasets" (Research Synthesis §3.3)
- "Whisper fine-tuned for Swahili: **~17% WER** (Word Error Rate)" (Research Synthesis §3.4)
- "pgvector: **471 QPS at 28ms p95** at 50M vectors — zero additional infrastructure" (Research Synthesis §2.1)
- "XGBoost crop recommendation: **99.09% accuracy** on agricultural datasets" (Research Synthesis §3.6)
- "iSDAsoil: **30m resolution**, free, covers all sub-Saharan Africa" (Research Synthesis §3.6)
- "iSDAsoil pH accuracy: **0.90 CCC** (highly reliable for lime recommendations)" (Research Synthesis §3.6)

### Connection Points
- **Forwards to:** Chapter 11 (Plant Disease Scanner — detailed module), Chapter 8 (Flutter — how AI integrates into frontend)
- **Backwards from:** Chapter 4 (System Architecture), Chapter 5 (Laravel — backend services), Chapter 6 (Database — pgvector)
- **Parallel with:** None — this is the most cross-referenced chapter

### Visual Elements
- **Info Box:** "The $1 Extension Officer" — Gemini Flash costs $21/month for 50K queries, serving unlimited farmers
- **Callout:** "Field vs Lab Accuracy" — 10-40% accuracy drop justifies hybrid on-device + cloud approach
- **Callout:** "Active Learning Loop" — farmer thumbs up/down improves model over time
- **Chart:** LLM cost comparison bar chart (Gemini $21 vs GPT-4o-mini $42 vs GPT-4o $700 vs Claude $945)
- **Chart:** Disease model accuracy comparison (lab conditions vs field conditions)

---

## Chapter 8: Flutter Frontend Architecture (~1,500 words)

### Purpose
Detail the mobile app architecture: state management, offline-first patterns, UI framework, and device optimizations for East African conditions.

### Opening Hook
"MkulimaForum's Flutter frontend must work on devices ranging from the latest Samsung Galaxy to a $80 Tecno Spark running Android 10 with 2GB RAM. It must function for 72 hours without internet, display in Swahili and English, and compress images to under 2MB before upload. This chapter details how Material 3, BLoC pattern, and Drift SQLite make this possible."

### Section Structure
1. **Why Flutter 3.24+** (200 words) — Impeller, Material 3, single codebase, offline support
2. **Project Structure & Architecture** (200 words) — clean architecture layers
3. **State Management with BLoC** (250 words) — predictable, testable, offline-friendly
4. **Offline-First with Drift** (300 words) — local DB, sync engine, conflict resolution
5. **UI/UX Design System** (200 words) — Material 3, dark mode, glassmorphism, accessibility
6. **Image & Media Handling** (150 words) — compression, progressive upload, offline queue
7. **Localization (i18n)** (150 words) — Swahili, English, extensible framework
8. **Performance Optimization** (50 words) — app size, startup time, battery

### Key Tables

**Table 1: Flutter Architecture Layers**
| Column Headers | `Layer` | `Responsibility` | `Key Packages` | `Testing` |
|---|---|---|---|---|
| Rows | Presentation (Widgets, screens, theming / flutter_bloc, material / Widget tests); Domain (Entities, use cases, repository interfaces / freezed, equatable / Unit tests); Data (Repositories, API clients, local DB / drift, dio, hive / Integration tests); Infrastructure (Logging, analytics, platform services / firebase, sentry / Mock tests) |
| Source | Dim 05, Flutter best practices |

**Table 2: Key Flutter Packages**
| Column Headers | `Package` | `Purpose` | `Version` | `Why` |
|---|---|---|---|---|
| Rows | flutter_bloc (State management / 8.x / predictable, testable); drift (Local SQLite / latest / type-safe, streaming); dio (HTTP client / 5.x / interceptors, retry); google_maps_flutter (Maps / latest / mature, custom markers); image_picker (Camera / latest / disease scanning); firebase_messaging (Push notifications / latest / FCM); flutter_tflite (On-device ML / latest / disease scanner); flutter_localization (i18n / latest / ARB files); shimmer (Loading UI / latest / skeleton screens); cached_network_image (Image caching / latest / offline images) |
| Source | Requirements §4 |

**Table 3: Offline-First Sync Strategy**
| Column Headers | `Data Type` | `Storage` | `Sync Trigger` | `Conflict Resolution` | `Max Offline` |
|---|---|---|---|---|---|
| Rows | User profile (Hive / on login + every sync / server wins / 72h); Product catalog (Drift / daily sync / server wins / 72h); Forum posts (Drift / real-time + delta / CRDT / 72h); Disease scans (Drift + file system / when online / client wins / 7d); Orders/cart (Drift / when online / server wins / 72h); Wallet balance (Hive / every sync / server wins / 24h); Weather data (Hive / every 6h / server wins / 72h); Map tiles (File cache / on view / LRU eviction / 30d) |
| Source | Requirements IF-003, Research Synthesis §6.1 |

**Table 4: Supported Device Tiers**
| Column Headers | `Tier` | `Specs` | `Target` | `Optimization` | `Experience Level` |
|---|---|---|---|---|---|
| Rows | Tier 1 — Premium (4+ GB RAM, 64GB+ storage, Android 12+ / ~15% / Full features / Optimal); Tier 2 — Mid-range (2-4 GB RAM, 32-64GB, Android 10+ / ~40% / Core features / Compressed images, reduced animations); Tier 3 — Low-end (1-2 GB RAM, 16-32GB, Android 9+ / ~35% / Essential features only / Minimal UI, no animations); Tier 4 — Feature phone (No Android / ~43% market / USSD only / Voice callbacks, SMS alerts) |
| Source | Requirements NF-017, AC-007, Dim 01 |

### Code Examples

**Code 1: Drift Database Schema + Sync**
```dart
// lib/data/local/app_database.dart
// ~40 lines
// Shows: @DriftDatabase(tables: [Products, ForumPosts, DiseaseScans, Orders]),
// sync() method with since timestamp, conflict resolution with server wins
```

**Code 2: BLoC Pattern for Offline-First Feature**
```dart
// lib/presentation/forum/bloc/forum_bloc.dart
// ~45 lines
// Shows: ForumBloc extends Bloc<ForumEvent, ForumState>,
// emit loading → fetch from Drift → emit cached → fetch from API → emit updated,
// offline queue for new posts
```

**Code 3: Image Compression & Upload**
```dart
// lib/services/media/image_service.dart
// ~30 lines
// Shows: compressImage(File, maxSizeMB: 2), upload with progress,
// offline queue if no connectivity, retry with exponential backoff
```

**Code 4: Localization Setup**
```dart
// lib/l10n/app_localizations.dart
// ~25 lines
// Shows: ARB files for sw, en, fr; locale resolution fallback;
// MaterialApp localizationsDelegates; RTL support consideration
```

### Architecture Diagrams

**Diagram 1: Flutter Clean Architecture**
- Type: Layered diagram
- Layers: UI Layer (Widgets) → Bloc Layer (State Management) → Use Case Layer → Repository Layer → Data Layer (API + Local DB)
- Arrows: Dependency direction (inward only)
- Side: SyncEngine connecting Data Layer to both API and Drift

**Diagram 2: Offline-First Sync Architecture**
- Type: Component diagram
- Components: Flutter App → Drift (local) ↔ SyncEngine ↔ REST API → PostgreSQL
- Show: Outbox queue, push/pull services, CRDT conflict resolution
- Annotations: Background sync via WorkManager

**Diagram 3: BLoC State Flow**
- Type: State diagram
- States: Initial → Loading → CachedLoaded → FreshLoaded / Error
- Events: Fetch, Refresh, Create, Update, Delete
- Show: Offline transitions, error recovery

### Key Data Points (with Citations)
- "Flutter 3.24+: Impeller rendering engine eliminates shader jank" (Flutter release notes)
- "Target APK size: **<30MB**; installed size **<80MB**" (Requirements NF-017)
- "App launch time: **<2 seconds** on mid-range Android" (Requirements NF-001)
- "Drift (SQLite): type-safe queries, streaming, migrations — industry standard for offline-first Flutter" (Research Synthesis §2.1)
- "Background location tracking: **<5% battery/hour**" (Requirements NF-007)
- "Material 3: dynamic theming, CarouselView, TreeView, predictive back gestures" (Flutter 3.24)

### Connection Points
- **Forwards to:** Chapters 9-12 (module-specific UI implementations), Chapter 15 (Real-Time — GPS tracking UI)
- **Backwards from:** Chapter 4 (System Architecture — app container), Chapter 7 (AI/ML — on-device models)
- **Parallel with:** Chapter 5 (Laravel Backend — consumes API)

### Visual Elements
- **Info Box:** "Impeller vs Skia" — why Impeller matters for smooth UI on low-end devices
- **Callout:** "The 72-Hour Offline Promise" — critical features work without connectivity
- **Callout:** "APK < 30MB" — optimization strategies (separate ABI builds, asset compression)
- **Chart:** Device tier distribution in target markets (Tier 1: 15%, Tier 2: 40%, Tier 3: 35%, Tier 4: 43% feature phones)

---

## Chapter 9: Agrodealer Marketplace Module (~1,200 words)

### Purpose
Deep dive into the multi-vendor agricultural inputs marketplace — the platform's primary revenue engine.

### Opening Hook
"The agricultural input market in East Africa is plagued by counterfeit seeds, unregulated pesticides, and opaque pricing. A Tanzanian farmer buying maize seed has no way to verify if the packet contains genuine certified seed or a cheap imitation. MkulimaForum's marketplace solves this through TFRA/KEPHIS/UNADA-verified dealers, escrow-protected payments, and a review system that builds trust over time."

### Section Structure
1. **Marketplace Overview** (150 words) — scope, target users
2. **Product Catalog** (200 words) — categories, search, filtering, Swahili support
3. **Seller Management** (200 words) — registration, KYC, verification tiers
4. **Shopping & Checkout Flow** (200 words) — cart, mobile money, escrow
5. **Order Management** (150 words) — fulfillment, tracking, delivery confirmation
6. **Commission & Monetization** (150 words) — fee structure, disbursement
7. **Trust & Safety** (150 words) — reviews, moderation, dispute resolution

### Key Tables

**Table 1: Product Categories**
| Column Headers | `Category` | `Subcategories` | `Verification Required` | `Regulatory Body` | `Examples` |
|---|---|---|---|---|---|
| Rows | Seeds (Maize, beans, rice, sorghum, millet / Yes / TFRA/KEPHIS/NARO / Certified seed packets); Fertilizers (Organic, NPK, Urea, DAP / Yes / TFRA/PCPB/UNADA / Yara, Minjingu); Pesticides (Insecticides, herbicides, fungicides / Yes / TFRA/PCPB / Syngenta, Bayer); Tools (Hoes, sprayers, irrigation / No / None / Manual and powered); Livestock supplies (Feeds, vaccines, supplements / Partial / Veterinary board / Depends on product) |
| Source | Requirements EF-001, RC-006 |

**Table 2: Seller Verification Tiers**
| Column Headers | `Tier` | `Requirements` | `Badge` | `Product Limits` | `Commission` | `Withdrawal` |
|---|---|---|---|---|---|---|
| Rows | Unverified (Phone verified only / Gray / 5 listings / 8% / Weekly); Basic KYC (ID + farm/business GPS / Blue / 50 listings / 5% / Weekly); Verified Dealer (TFRA/KEPHIS/UNADA license / Gold / Unlimited / 3.5% / Daily); Premium Partner (License + warehouse + insurance / Platinum / Unlimited / 3% / Instant) |
| Source | Requirements IF-008, Research Synthesis §5.2 |

**Table 3: Commission Structure**
| Column Headers | `Transaction Type` | `Buyer Fee` | `Seller Commission` | `Escrow Fee` | `Benchmark` |
|---|---|---|---|---|---|
| Rows | Product purchase (Free / 3-5% / Included / Jumia: 5-15%); Input financing (Free / 2-5% platform / 1% / Apollo: embedded); Bulk order >$500 (Free / 2.5% / Included / iProcure: 25% discounts); Cross-border (1% FX / 4% / 1.5% / EAC average: 3-5%) |
| Source | Requirements IF-016, Research Synthesis §4.4 |

### Code Examples

**Code 1: Product Search with Meilisearch + Facets**
```php
// app/Http/Controllers/Api/Marketplace/SearchController.php
// ~30 lines
// Shows: Product::search($query)->where('country_code', $tenant)
// ->where('category', $filter)->orderBy('price', $sort)->paginate(20),
// Swahili typo-tolerant search, facet counts
```

**Code 2: Escrow Payment Flow**
```php
// app/Services/Payments/EscrowService.php
// ~40 lines
// Shows: createEscrow($orderId, $amount), hold funds in sub-wallet,
// release on delivery confirmation, refund on dispute,
// commission deduction, seller wallet credit
```

**Code 3: Seller Verification Status Check**
```php
// app/Services/Kyc/SellerVerificationService.php
// ~25 lines
// Shows: checkVerificationLevel($userId), TFRA API integration,
// document expiry check, auto-downgrade on expired license
```

### Architecture Diagrams

**Diagram 1: Marketplace Transaction Flow**
- Type: Sequence diagram
- Actors: Buyer, Flutter App, Laravel API, Meilisearch, Mobile Money Gateway, Escrow Wallet, Seller
- Flow: Search → View Product → Add to Cart → Checkout → STK Push → Escrow Hold → Seller Notification → Fulfill → Delivery Confirm → Release Payment → Seller Wallet

**Diagram 2: Seller Verification State Machine**
- Type: State diagram
- States: Applied → Documents Submitted → Under Review → Verified (Basic/Verified/Premium) → Rejected → Appeal
- Transitions: Auto-checks, manual review, API verification

### Key Data Points (with Citations)
- "iProcure shows **94% fill rate** and **25% discounts** when trust is established" (Research Synthesis §1.3)
- "30% of Tanzanian farmers are **>1 hour from nearest agro-dealer**" (Research Synthesis §5.1)
- "Marketplace commission: **3-5%** for verified sellers" (Requirements IF-016)
- "Escrow protection required by BoT, BoU, CBK regulations" (Research Synthesis §4.2)
- "Buyer payment processing: free to buyer; seller pays 0.5-1%" (Research Synthesis §4.4)

### Connection Points
- **Forwards to:** Chapter 14 (Payment & Financial — escrow implementation), Chapter 13 (API Design — marketplace endpoints)
- **Backwards from:** Chapter 3 (Platform Overview), Chapter 5 (Laravel Backend)
- **Parallel with:** Chapters 10-12 (other modules)

### Visual Elements
- **Info Box:** "The Counterfeit Seed Problem" — why verification matters; TFRA/KEPHIS as trust anchors
- **Callout:** "Escrow = Trust" — every transaction protected until delivery confirmed
- **Callout:** "30% of farmers >1 hour from agrodealer" — marketplace as accessibility solution
- **Chart:** Commission comparison — MkulimaForum (3-5%) vs Jumia (5-15%) vs traditional distributor markup (20-40%)

---

## Chapter 10: Farmers Forum & Community Platform (~1,000 words)

### Purpose
Detail the community platform — threaded discussions, expert verification, voice posts, AI-powered FAQ, content moderation.

### Opening Hook
"Agricultural knowledge in East Africa has always traveled through community — neighbors sharing advice at the market, extension officers visiting farms, elders predicting rain patterns. MkulimaForum's Farmers Forum digitizes this tradition while adding expert verification, AI-powered suggestions, and voice participation for farmers who cannot type."

### Section Structure
1. **Forum Architecture** (150 words) — categories, threading, sub-forums per region
2. **Content Types** (150 words) — text, images, voice notes, polls
3. **Expert Verification System** (150 words) — agronomist badges, verification flow
4. **AI-Powered Features** (150 words) — RAG FAQ suggestions, content moderation
5. **Offline-First Forum** (150 words) — browse cached, draft offline, sync when online
6. **Community Moderation** (150 words) — AI flagging, human review, reputation
7. **Voice Participation** (100 words) — voice notes, Swahili STT

### Key Tables

**Table 1: Forum Categories**
| Column Headers | `Category` | `Description` | `Moderation` | `AI Enabled` | `Language` |
|---|---|---|---|---|---|
| Rows | Pests & Diseases (Report sightings, ask for help / High / FAQ suggestions, diagnosis links / Sw+En); Markets & Prices (Discuss prices, find buyers / Medium / Price trend alerts / Sw+En); Weather & Climate (Rain patterns, forecasts / Low / Weather integration / Sw+En); Techniques & Methods (Farming methods, innovation / Medium / Crop calendar links / Sw+En); Livestock (Animal health, breeding / High / Vet service links / Sw+En); Equipment & Tools (Tool reviews, maintenance / Low / Marketplace links / Sw+En); Cooperative Talk (SACCO discussions, bulk buying / Medium / None / Sw+En); General (Community announcements / High / Auto-moderation / Sw+En) |
| Source | Requirements EF-003 |

**Table 2: Expert Badge System**
| Column Headers | `Badge` | `Requirements` | `Verification` | `Permissions` | `Display` |
|---|---|---|---|---|---|
| Rows | Certified Agronomist (Degree + professional cert + KALRO/NARO/TARI/RAB / API check / Answer as expert, priority ranking); Veterinary Officer (TVB/MLFD registration / Board verification / Answer as vet, emergency flag); Extension Officer (Government appointment letter / Ministry API / Official responses, data collection); Verified Farmer (3+ years activity + 50+ helpful votes / Community / Trusted contributor badge); Agrodealer (TFRA/PCPB/UNADA license / Regulatory API / Product recommendations); Top Contributor (100+ posts + 4.5+ rating / Algorithm / Featured profile) |
| Source | Requirements IF-008, EF-003 |

**Table 3: Content Moderation Pipeline**
| Column Headers | `Stage` | `Method` | `Response Time` | `Action` |
|---|---|---|---|---|
| Rows | Auto-scan on submit (AI toxicity/spam model / <500ms / Flag or auto-reject); Community flagging (User report button / <2 hours / Human review queue); Pattern detection (AI behavioral analysis / Daily / Suspicious account review); Human review (Moderator panel / <4 hours / Remove, warn, suspend); Appeal process (User appeal form / <24 hours / Senior moderator review) |
| Source | Requirements IF-024 |

### Code Examples

**Code 1: Forum Post with Voice Note**
```dart
// lib/presentation/forum/create_post_screen.dart
// ~35 lines
// Shows: text input + voice recorder button,
// voice note attachment (max 60 seconds),
// category selection, submit with offline queue
```

**Code 2: AI FAQ Suggestion Engine**
```python
// app/Services/Forum/FaqSuggestionService.py
// ~30 lines
// Shows: incoming post embedding → pgvector similarity search
// → suggest existing answers → auto-link related threads
// → reduce duplicate questions
```

### Architecture Diagrams

**Diagram 1: Forum Data Flow**
- Type: Data flow diagram
- Flow: User creates post → AI moderation scan → Store in Drift (offline) → Sync to API → PostgreSQL → Broadcast to subscribers → RAG knowledge base update
- Side: Expert badge verification flow

**Diagram 2: Voice Post Journey**
- Type: User journey / sequence diagram
- Steps: Tap microphone → Record (60s max) → Whisper STT → Review transcript → Post (text + audio attachment) → Other users see text + play audio

### Key Data Points (with Citations)
- "Forum posts feed the RAG knowledge base, improving AI advice for all" (Cross-Insight #8)
- "Voice notes bypass literacy barriers for **60%+ of target market**" (Cross-Insight #5)
- "AI content moderation reduces manual review workload by **60-80%**" (industry benchmark)
- "Offline forum browsing: 72 hours of cached content" (Requirements NF-003)

### Connection Points
- **Forwards to:** Chapter 7 (AI/ML — RAG FAQ suggestions), Chapter 11 (Disease Scanner — forum pest reports)
- **Backwards from:** Chapter 3 (Platform Overview)
- **Parallel with:** Chapters 9, 11, 12 (other modules)

### Visual Elements
- **Info Box:** "The Voice Note Revolution" — voice posts as primary input for low-literacy users
- **Callout:** "Expert Badges Build Trust" — verified agronomist answers carry more weight
- **Callout:** "Forum → AI Flywheel" — every post makes the AI smarter for everyone

---

## Chapter 11: Plant Disease Scanner Module (~1,200 words)

### Purpose
Deep dive into the hybrid on-device + cloud disease scanner — MkulimaForum's "hero feature" for farmer acquisition.

### Opening Hook
"A farmer in Morogoro notices yellow streaks on her cassava leaves. She opens MkulimaForum, taps the scanner, and captures a photo. Within 2 seconds — even without internet — the app identifies Cassava Brown Streak Disease with 78% confidence. It links her to resistant NARO varieties in the marketplace and books a nearby agronomist. This is not future technology; this is MkulimaForum's disease scanner, running on a $100 Android phone with no data connection."

### Section Structure
1. **The Disease Scanner Vision** (150 words) — farmer acquisition, offline priority
2. **Hybrid AI Architecture** (250 words) — TFLite edge + Gemini Vision cloud
3. **Supported Diseases** (200 words) — 20+ diseases across East African crops
4. **On-Device Model** (200 words) — MobileNetV3, quantization, NNAPI
5. **Cloud Fallback** (150 words) — Gemini Vision for rare/uncertain cases
6. **Severity Assessment** (100 words) — progression staging, treatment urgency
7. **Treatment Recommendations** (100 words) — product linking, agronomist booking
8. **Active Learning Loop** (50 words) — feedback, retraining, improvement

### Key Tables

**Table 1: Supported Diseases by Crop (Priority List)**
| Column Headers | `Crop` | `Disease` | `Offline?` | `Severity Levels` | `Marketplace Link` | `Agronomist?` |
|---|---|---|---|---|---|---|
| Rows | Maize (Fall Armyworm / Yes / 1-5 / Pesticides / Yes); Maize (Maize Lethal Necrosis / Yes / 1-4 / Resistant seeds / Yes); Cassava (Brown Streak Disease / Yes / 1-5 / Resistant varieties / Yes); Cassava (Mosaic Disease / Yes / 1-4 / Clean cuttings / Yes); Banana (Bacterial Wilt (BXW) / Yes / 1-5 / Clean tools / Yes); Banana (Fusarium Wilt / Partial / 1-4 / Resistant varieties / Yes); Coffee (Leaf Rust / Yes / 1-5 / Fungicides / Yes); Coffee (Berry Disease / Partial / 1-4 / Fungicides / Yes); Tea (Blister Blight / Yes / 1-4 / Fungicides / Yes); Beans (Angular Leaf Spot / Yes / 1-3 / Fungicides / No); Potato (Late Blight / Yes / 1-5 / Fungicides / Yes); Rice (Blast / Yes / 1-4 / Fungicides / Yes) |
| Source | Research Synthesis §1.5, requirements EF-002 |

**Table 2: Model Selection Decision Matrix**
| Column Headers | `Scenario` | `Model` | `Confidence` | `Response Time` | `Data Required` | `Action` |
|---|---|---|---|---|---|---|
| Rows | Common disease, offline (TFLite MobileNetV3 / 65-75% / <2s / None / Show diagnosis + treatment); Common disease, online (TFLite first → confirm with Gemini / 75-85% / <5s / 1 image / Enhanced treatment); Rare/uncertain (Gemini Vision only / 80-90% / 3-8s / Upload required / Full report + agronomist referral); Low confidence any (<70% TFLite, <85% Gemini) / N/A / Variable / Variable / Human agronomist review); Critical severity (Any model / Any / <2s + priority / N/A / Urgent agronomist alert) |
| Source | Research Synthesis §3.3 |

**Table 3: Active Learning Feedback Loop**
| Column Headers | `Stage` | `Trigger` | `Data Collected` | `Outcome` |
|---|---|---|---|---|
| Rows | Diagnosis (User views result / Disease type, confidence, crop, GPS / Baseline); Feedback (User thumbs up/down / Correct/incorrect label / Training signal); Expert review (Confidence <70% or user flagged / Agronomist label / Gold standard); Image quality (Upload attempt / Resolution, lighting, blur / Quality scoring); Retraining (Monthly batch / All feedback + expert labels / Model update); Deployment (CI/CD pipeline / A/B test / Gradual rollout) |
| Source | Requirements NF-015 |

### Code Examples

**Code 1: TFLite Inference in Flutter**
```dart
// lib/services/ai/disease_scanner_service.dart
// ~50 lines
// Shows: Interpreter.fromAsset('model.tflite'),
// preprocessImage (resize 224x224, normalize [0,1]),
// run(input), parse outputs (label + confidence),
// threshold check (>=0.70), return or fallback
```

**Code 2: Gemini Vision Fallback**
```python
// app/Services/AI/GeminiVisionFallback.py
// ~35 lines
// Shows: encode image to base64, construct prompt with
// East African crop context, call Gemini Vision API,
// parse structured response (disease, confidence, severity, treatment)
```

**Code 3: Severity Assessment Algorithm**
```dart
// lib/services/ai/severity_assessor.dart
// ~30 lines
// Shows: leaf area analysis, color histogram analysis,
// severity score 1-5, treatment urgency calculation,
// link to marketplace products based on disease type
```

### Architecture Diagrams

**Diagram 1: Disease Scanner Hybrid Architecture**
- Type: Decision tree / flowchart
- Start: Image captured → TFLite inference → Confidence?
- >=70%: Show result (offline capable) + severity + treatment + marketplace links
- <70%: Upload to Gemini Vision → Confidence?
- >=85%: Show enhanced result + full report
- <85%: Human agronomist review queue
- All paths: Log feedback for active learning

**Diagram 2: Active Learning Loop**
- Type: Circular diagram
- Loop: Farmer scan → AI diagnosis → Farmer feedback (thumbs up/down) → Expert review (low confidence) → Training dataset → Model retraining → Updated model deployment → Better future diagnoses

### Key Data Points (with Citations)
- "MobileNetV3-Small: **2.54MB**, **67.7% top-1 accuracy**, runs offline on any Android device" (Research Synthesis §3.3)
- "Gemini Vision cloud fallback: **80-90% accuracy** for complex cases" (Research Synthesis §3.3)
- "**10-40% accuracy drop** in field conditions vs lab — hybrid approach essential" (Research Synthesis §3.3)
- "Target: **>=85% top-1 accuracy** with human review below 70% confidence" (Requirements NF-015)
- "Fall Armyworm: **$13B annual losses** across SSA — #1 priority disease" (Research Synthesis §1.5)
- "Cassava Brown Streak: **100% yield loss possible**, devastating Uganda" (Research Synthesis §1.5)
- "Coffee Leaf Rust: **83-97%** of Rwanda coffee farms infected" (Research Synthesis §1.5)

### Connection Points
- **Forwards to:** Chapter 9 (Marketplace — treatment product links), Chapter 12 (Services — agronomist booking from scan)
- **Backwards from:** Chapter 7 (AI/ML — model architecture), Chapter 8 (Flutter — TFLite integration)
- **Parallel with:** Chapters 9, 10, 12

### Visual Elements
- **Info Box:** "The 2-Second Offline Diagnosis" — works without internet on a $100 phone
- **Callout:** "Field vs Lab: The 10-40% Gap" — why real-world testing matters
- **Callout:** "Every Scan Makes AI Smarter" — active learning loop explained
- **Chart:** Accuracy comparison — TFLite lab (67.7%) vs TFLite field (~45-55%) vs Gemini Vision lab (90%) vs Gemini Vision field (~80-85%)

---

## Chapter 12: Services Marketplace (~1,500 words)

### Purpose
Comprehensive coverage of the services layer — agronomist booking, veterinary telemedicine, logistics, warehouse, and soil testing. This is the ecosystem lock-in feature.

### Opening Hook
"When a farmer books a soil test through MkulimaForum, the results recommend specific fertilizers from the marketplace. An AI agronomist suggests planting dates based on the soil data. At harvest, the farmer books warehouse space and arranges transport — all within the same platform, using the same wallet. This is the services marketplace: not a directory, but an integrated ecosystem where every service generates data that improves every other service."

### Section Structure
1. **Services Architecture Overview** (200 words) — unified marketplace, shared infrastructure
2. **Agronomist Booking** (200 words) — profiles, calendar, consultation, ratings
3. **Veterinary Services** (200 words) — tele-vet, farm visits, vaccination schedules
4. **Logistics & Transport** (200 words) — bodaboda to trucks, GPS tracking, fare estimation
5. **Warehouse & Storage** (150 words) — booking, IoT monitoring, seasonal pricing
6. **Soil Testing** (150 words) — sample collection, lab results, AI recommendations
7. **Provider Vetting** (150 words) — 4-tier system, verification, ongoing monitoring
8. **Booking Engine** (150 words) — availability, conflicts, payments, reviews

### Key Tables

**Table 1: Service Categories & Delivery Models**
| Column Headers | `Service` | `Delivery` | `Key Providers` | `Price Range` | `Commission` | `Offline?` |
|---|---|---|---|---|---|---|
| Rows | Agronomist consultation (In-app chat/video + farm visit / Individual agronomists / $5-50/consult / 15% / Partial); Veterinary (Tele-medicine + farm visit / Registered vets / $3-30/consult / 12% / Partial); Logistics — last mile (Boda boda pickup / SafeBoda style / $0.50-3/delivery / 10% / No); Logistics — trucking (Digital freight matching / Lori Systems model / $50-500 / 10% / Partial); Cold storage (Solar cold rooms / SokoFresh model / $1-2/kg/day / 5% / No); Warehouse (Grain storage / Silo Africa model / $0.50-1/kg/month / 5% / No); Soil testing (Lab collection + digital results / KALRO, NARO, private / $10-30/test / 8% / Partial) |
| Source | Research Synthesis §5.1 |

**Table 2: Provider Vetting Tiers**
| Column Headers | `Tier` | `Provider Type` | `Requirements` | `Verification` | `Ongoing` |
|---|---|---|---|---|---|
| Rows | Tier 1 (Individual / Boda riders, community animal health workers / ID, training cert, community ref, 4.0+ rating / Annual re-certification); Tier 2 (Business / Agro-dealers, input suppliers / Business license, product certs, warehouse inspection / Quarterly audit); Tier 3 (Facility / Cold storage, warehouse operators / Facility cert, IoT monitoring, food safety cert / Monthly inspection); Tier 4 (Professional / Vets, agronomists / Professional registration, credentials verified, peer review / Bi-annual renewal) |
| Source | Research Synthesis §5.2 |

**Table 3: Service Booking State Machine**
| Column Headers | `State` | `Trigger` | `Duration` | `Payment` | `Cancellation` |
|---|---|---|---|---|---|
| Rows | Requested (User submits booking / 24h provider response / Hold not charged / Free); Confirmed (Provider accepts + schedules / Until service date / 50% hold / Full refund >24h); In Progress (Service being delivered / Service duration / Remaining 50% charged / Partial refund); Completed (Service finished / Immediate / Release hold / None); Reviewed (User rates / 7 days / Commission deducted / None); Disputed (Issue raised / 72h resolution / Hold frozen / Escalation review) |
| Source | Derived from requirements |

**Table 4: Mapping & Logistics API Costs**
| Column Headers | `Service` | `Primary` | `Fallback` | `Cost at 10K Users` |
|---|---|---|---|---|
| Rows | Maps & tiles (Mapbox / OpenStreetMap+MapLibre / ~$0); Geocoding (Mapbox / OSM Nominatim / ~$0); Directions/routing (Mapbox / OSRM self-hosted / ~$400/mo); Places/search (Mapbox 300ms debounce / Google Places / ~$1,500/mo); GPS tracking (Mapbox Map Matching / Google Roads / ~$425/mo); **Total** (--- / --- / **~$2,325/mo** — 46% cheaper than Google) |
| Source | Research Synthesis §5.3 |

### Code Examples

**Code 1: Service Booking State Machine**
```php
// app/Services/Booking/BookingStateMachine.php
// ~45 lines
// Shows: states array (requested→confirmed→in_progress→completed→reviewed),
// transitions with guards (canCancel, canReschedule),
// event dispatching for notifications, payment holds
```

**Code 2: GPS Tracking Integration**
```dart
// lib/services/logistics/gps_tracking.dart
// ~35 lines
// Shows: Mapbox Map Matching API, real-time location stream,
// route drawing, ETA calculation, offline tile caching
```

**Code 3: Veterinary Emergency Flow**
```php
// app/Services/Veterinary/EmergencyDispatchService.php
// ~30 lines
// Shows: emergency flag, nearest vet query (PostGIS ST_DWithin),
// push + SMS notification, response time SLA (<30 min)
```

### Architecture Diagrams

**Diagram 1: Services Marketplace Integration**
- Type: System diagram showing 5 services as nodes
- Center: Shared Booking Engine + Payment + Review infrastructure
- Services: Agronomist, Veterinary, Logistics, Warehouse, Soil Testing
- Arrows: Show data flows between services (soil test → agronomist → warehouse → logistics)
- Annotation: The data flywheel

**Diagram 2: Booking Flow State Machine**
- Type: State diagram
- States: Requested → Confirmed → In Progress → Completed → Reviewed
- Alternative: Requested → Declined, Confirmed → Cancelled, Completed → Disputed
- Show: Payment holds, notification triggers, SLA timers

**Diagram 3: Logistics Tracking Architecture**
- Type: System diagram
- Flow: Driver GPS → Flutter Background Location → Mapbox → Route Display → ETA Updates → Push Notification to Farmer
- Side: Offline tile cache, fare estimation engine

### Key Data Points (with Citations)
- "Extension ratios critically low: **1:1,380 (KE)**, **1:1,172 (TZ)**" (Research Synthesis §1.2)
- "AI extension costs **10x less** than traditional ($1-3 vs $35/farmer/year)" (Cross-Insight #2)
- "Lori Systems: **20,000 trucks**, **$10B cargo moved**, **22% cost savings**" (Research Synthesis §1.3)
- "SokoFresh: **32+ solar cold rooms**, **5,000+ farmers**, KES 1-2/kg/day" (Research Synthesis §1.3)
- "Cold chain market: **$12.87B → $18.29B by 2032** (5.1% CAGR)" (Research Synthesis §1.2)
- "iSDAsoil: **30m resolution**, free, all sub-Saharan Africa" (Research Synthesis §3.6)
- "Mapbox total cost: **~$2,325/mo** at 10K users — 46% cheaper than Google" (Research Synthesis §5.3)
- "Logistics booking completed in **<5 minutes**" (Requirements stakeholder criteria)

### Connection Points
- **Forwards to:** Chapter 14 (Payment — escrow for services), Chapter 15 (Real-Time — GPS tracking)
- **Backwards from:** Chapter 3 (Platform Overview), Chapter 7 (AI/ML — recommendations)
- **Parallel with:** Chapters 9-11

### Visual Elements
- **Info Box:** "The Data Flywheel" — soil test data improves crop recommendations, vet records improve livestock advice, purchase history personalizes marketplace
- **Callout:** "5 Services, 1 Booking Engine" — shared infrastructure reduces complexity
- **Callout:** "Tier 4 Provider Vetting" — professional registration verification for safety-critical services
- **Chart:** Service commission rates comparison across platforms



---

## Chapter 13: API Design & Standards (~1,000 words)

### Purpose
Define API standards, conventions, and patterns. Show how MkulimaForum's API serves Flutter, PWA, USSD, and third-party integrations.

### Opening Hook
"MkulimaForum's API is the nervous system connecting farmers on $80 Android phones to AI models running in Cape Town data centers. Every design decision — from delta sync to field selection to compound documents — is optimized for bandwidth-constrained, intermittently connected users across four countries."

### Section Structure
1. **API Standards** (150 words) — JSON:API, OpenAPI 3.1, versioning
2. **Authentication** (150 words) — Sanctum tokens, refresh, multi-factor
3. **Delta Sync Pattern** (200 words) — since timestamp, sync tokens, chunks
4. **Field Selection & Compound Documents** (150 words) — reduced payload
5. **Multi-Country API Routing** (150 words) — subdomain, header, locale
6. **Error Handling & Rate Limiting** (100 words) — consistent errors, per-second limiting
7. **Webhooks & Callbacks** (100 words) — mobile money, mobile money notifications

### Key Tables

**Table 1: API Design Patterns**
| Column Headers | `Pattern` | `Implementation` | `Benefit` | `Example` |
|---|---|---|---|---|
| Rows | Delta sync (`/api/v1/sync?since=1699900000` / Only changed data since timestamp / `{"products": [...], "sync_token": "abc"}`); Field selection (`?fields[product]=name,price,image` / Reduced payload size / 50-70% smaller responses); Compound documents (`?include=dealer,reviews` / Eliminates N+1 / Single request for product + dealer + reviews); Cursor pagination (`?cursor=eyJpZCI6MTB9` / Stable ordering / No skipped items on insert); Multi-tenant header (`X-Country-Code: tz` / Per-country routing / tz, ke, ug, rw); Brotli compression (Brotli/gzip / Faster transfers / 20-30% smaller than gzip); Batch requests (`POST /api/v1/batch` / Multiple ops in one / Reduces round-trips) |
| Source | Research Synthesis §6.6 |

**Table 2: Core API Endpoints**
| Column Headers | `Endpoint` | `Method` | `Auth` | `Purpose` | `Response Size` |
|---|---|---|---|---|---|
| Rows | /api/v1/auth/register (POST / No / Phone OTP registration / 1KB); /api/v1/auth/login (POST / No / Phone + PIN login / 2KB); /api/v1/sync (GET / Yes / Delta sync all data / 10-500KB); /api/v1/products (GET / Yes / Search + filter catalog / 50KB); /api/v1/orders (POST / Yes / Create order + escrow / 5KB); /api/v1/disease/scan (POST / Yes / Upload image for diagnosis / 20KB); /api/v1/forum/posts (GET / Yes / Paginated forum feed / 30KB); /api/v1/services/book (POST / Yes / Book service / 5KB); /api/v1/wallet/balance (GET / Yes / Wallet + escrow balance / 1KB); /api/v1/ai/ask (POST / Yes / AI agronomist query / 2KB) |
| Source | Derived from requirements |

**Table 3: Rate Limiting Tiers**
| Column Headers | `Tier` | `Requests/Minute` | `Burst` | `Scope` | `Applies To` |
|---|---|---|---|---|---|
| Rows | Anonymous (10 / 20 / IP / Registration, public catalog); Authenticated (60 / 100 / User ID / Standard API); Premium (120 / 200 / User ID / AI queries, scans); Internal (Unlimited / N/A / Service / Inter-service, admin); Mobile money (30 / 50 / Provider / Payment callbacks); AI (20 / 40 / User / Gemini API calls) |
| Source | Requirements NF-002, Laravel 11 per-second rate limiting |

### Code Examples

**Code 1: Delta Sync Endpoint**
```php
// app/Http/Controllers/Api/SyncController.php
// ~40 lines
// Shows: public function deltaSync(Request $request),
// since timestamp parsing, multi-table sync query,
// sync token generation, chunked response (100 records)
```

**Code 2: Compound Document Response**
```php
// app/Http/Resources/ProductResource.php
// ~25 lines
// Shows: JSON:API structure with data, relationships, included,
// sparse fieldsets, links, meta pagination
```

**Code 3: API Route Definition with Middleware Stack**
```php
// routes/api.php
// ~35 lines
// Shows: Route::middleware(['auth:sanctum', 'tenant', 'locale', 'throttle:60'])
// ->group() with marketplace, forum, services, AI routes
```

### Architecture Diagrams

**Diagram 1: API Request Lifecycle**
- Type: Sequence diagram
- Steps: Flutter App → Request → Cloudflare CDN → Load Balancer → FrankenPHP → TenantResolver → Auth Middleware → Rate Limiter → Controller → Service → Repository → PostgreSQL → Response → Brotli Compress → Client
- Annotations: Timing targets at each step

**Diagram 2: Delta Sync Flow**
- Type: Data flow diagram
- Client: Drift DB with last_sync_timestamp → API /sync?since=t → Server queries changed records → Returns changes + new sync_token → Client applies changes → Updates timestamp

### Key Data Points (with Citations)
- "Laravel 11 introduces **per-second rate limiting**" (Laravel 11 release notes)
- "Brotli compression: **20-30% smaller** than gzip for JSON" (Research Synthesis §6.6)
- "Cursor pagination: **stable ordering** on mobile with inserts" (Research Synthesis §6.6)
- "Compound documents **eliminate N+1** API calls" (Research Synthesis §6.6)
- "API response time target: **<500ms p95**, **<200ms** cached" (Requirements NF-002)

### Connection Points
- **Forwards to:** Chapter 14 (Payment — payment API callbacks), Chapter 17 (DevOps — API deployment)
- **Backwards from:** Chapter 5 (Laravel Backend — routes and controllers)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "JSON:API + OpenAPI 3.1" — why these standards matter for SDK generation and partner integration
- **Callout:** "Delta Sync: The Offline-First Enabler" — how sync enables offline usage
- **Chart:** API response size comparison — full payload vs field selection vs compound documents

---

## Chapter 14: Payment & Financial Architecture (~1,200 words)

### Purpose
Cover mobile money integration, escrow, wallet system, cross-border payments, and regulatory compliance across 4 countries.

### Opening Hook
"In East Africa, mobile money is not a feature — it is the financial system. M-Pesa alone processes $314 billion annually. For MkulimaForum, payment architecture means navigating 5 different mobile money APIs, 4 central banks, 3 regulatory frameworks, and the reality that a farmer in rural Tanzania may only have M-Pesa and no bank account. Every transaction must be escrow-protected, every commission transparent, and every withdrawal instant."

### Section Structure
1. **Payment Architecture Overview** (200 words) — unified payment layer, provider abstraction
2. **Mobile Money by Country** (300 words) — M-Pesa, MTN MoMo, Airtel Money, Tigo Pesa
3. **Escrow System** (200 words) — sub-wallets, hold, release, dispute
4. **Wallet Architecture** (150 words) — main, escrow, savings sub-wallets
5. **Cross-Border Payments** (150 words) — EAC corridors, Onafriq, Phase 2
6. **Commission & Monetization** (100 words) — fee structure, disbursement schedule
7. **Regulatory Compliance** (100 words) — BoT, CBK, BoU, BNR requirements

### Key Tables

**Table 1: Mobile Money API Comparison by Country**
| Column Headers | `Country` | `Primary Rail` | `API` | `TPS` | `Go-Live` | `Aggregator` | `Regulator` |
|---|---|---|---|---|---|---|---|
| Rows | Tanzania (M-Pesa Vodacom / REST / High / 2 weeks / ClickPesa / BoT); Tanzania (Tigo Pesa / REST / High / 2 weeks / ClickPesa / BoT); Tanzania (Airtel Money / REST / Medium / 2 weeks / ClickPesa / BoT); Tanzania (HaloPesa / REST / Medium / 2 weeks / ClickPesa / BoT); Kenya (M-Pesa Safaricom / Daraja 3.0 / **12,000** / 1 week / Direct/Lipana / CBK); Kenya (Airtel Money / REST / Medium / 2 weeks / Direct / CBK); Uganda (MTN MoMo / Open API / Medium / ~10 days / Direct / BoU); Uganda (Airtel Money / REST / Medium / 2 weeks / Direct / BoU); Rwanda (MTN MoMo / momodeveloper.mtn.co.rw / Medium / KYC-dependent / IremboPay / BNR); Rwanda (Airtel Money / REST / Medium / 2 weeks / IremboPay / BNR) |
| Source | Research Synthesis §4.1 |

**Table 2: Unified Payment Gateway Router**
| Column Headers | `Component` | `Purpose` | `Input` | `Output` | `Fallback` |
|---|---|---|---|---|---|
| Rows | GatewayRouter (Route to correct provider / country + provider + amount / Provider instance / Next available provider); M-Pesa adapter (KE+TZ M-Pesa / STK push params / Callback receipt / Airtel Money); MTN MoMo adapter (UG+RW MoMo / Collections request / Success/fail / Airtel); Airtel Money adapter (All countries Airtel / Payment request / Receipt / Next provider); ClickPesa adapter (TZ aggregation / Normalized request / Unified receipt / Direct APIs); IremboPay adapter (RW aggregation / Payment request / Receipt / Direct MoMo); Escrow manager (Hold/release funds / Order status + amount / Wallet updates / Manual review); Commission calculator (Fee computation / Transaction details / Split amounts / Config file) |
| Source | Research Synthesis §4.1, §4.2 |

**Table 3: Escrow Flow States**
| Column Headers | `State` | `Trigger` | `Wallet Action` | `Duration` | `Exception` |
|---|---|---|---|---|---|
| Rows | Payment pending (User initiates checkout / Hold main wallet / 15 min timeout / Auto-cancel); Funds held (STK push confirmed / Move to escrow sub-wallet / Until delivery / Timeout refund); In transit (Seller dispatches / Escrow remains held / Delivery SLA / Auto-release on timeout); Delivered (Buyer confirms / Release to seller wallet / Immediate / Dispute window 48h); Disputed (Buyer flags issue / Freeze escrow / 72h resolution / Escalation to admin); Refunded (Dispute resolved buyer / Return to buyer wallet / 24h / Partial refund option); Released (Dispute resolved seller / Release to seller / 24h / Commission deducted) |
| Source | Requirements IF-007, Research Synthesis §4.2 |

**Table 4: Commission & Fee Structure**
| Column Headers | `Service` | `Platform Fee` | `Provider Fee` | `Benchmark` | `Monthly @ 10K TX` |
|---|---|---|---|---|---|
| Rows | Marketplace purchase (3-5% seller / 0.5-1% processing / Jumia 5-15% / $1,500-2,500); Agronomist booking (15% / 0 / Independent: 20-30% / $750); Veterinary (12% / 0 / Industry: 15-20% / $360); Logistics — last mile (10% / 0 / SafeBoda: 15% / $500); Logistics — trucking (10% / 0 / Lori: 12% / $2,000); Warehouse storage (5% / 0 / SokoFresh: 8% / $250); Cold storage (5% / 0 / Industry: 10% / $400); Soil testing (8% / 0 / Labs: direct / $200); Cross-border (1-2% FX + 4% / 1.5% escrow / Onafriq: 3-5% / Variable) |
| Source | Requirements IF-016, Research Synthesis §4.4 |

**Table 5: Regulatory Requirements by Country**
| Column Headers | `Country` | `Key Law` | `Data Localization` | `Licensing` | `Escrow Required` | `KYC Level` |
|---|---|---|---|---|---|---|
| Rows | Tanzania (PDPA 2022 / Prohibited except with compliance / Non-bank PSP (131 licensed) / Yes / National ID + phone); Kenya (Data Protection Act 2019 + VASPA 2025 / Adequacy determination / PSP under NPS Act / Yes / National ID + phone); Uganda (Data Protection and Privacy Act 2019 / Standard clauses / Partner with licensed FI / Yes / National ID + phone); Rwanda (Law 058/2021 / Encouraged for sensitive / eKash + IremboPay / Yes / National ID + phone) |
| Source | Research Synthesis §4.3 |

### Code Examples

**Code 1: Payment Gateway Router**
```php
// app/Services/Payments/PaymentGatewayRouter.php
// ~40 lines
// Shows: resolveProvider($country, $preferredProvider),
// provider fallback chain, adapter pattern interface,
// unified response normalization
```

**Code 2: M-Pesa Daraja 3.0 STK Push**
```php
// app/Services/Payments/Adapters/MpesaAdapter.php
// ~45 lines
// Shows: stkPush($phone, $amount, $reference),
// generate access token, construct request,
// handle callback, idempotent processing
```

**Code 3: Escrow Sub-Wallet Management**
```php
// app/Services/Payments/EscrowWalletService.php
// ~40 lines
// Shows: createEscrowHold($orderId, $amount),
// releaseToSeller($orderId), refundToBuyer($orderId),
// commission deduction, wallet transaction logging
```

**Code 4: Unified Payment Response Normalization**
```php
// app/DTOs/PaymentResult.php
// ~25 lines
// Shows: normalized status enum (pending, success, failed, reversed),
// transaction_id, receipt_number, timestamp,
// provider_raw_response, normalized for all providers
```

### Architecture Diagrams

**Diagram 1: Payment Flow (Buyer → Escrow → Seller)**
- Type: Sequence diagram
- Actors: Buyer, Flutter App, Laravel API, Payment Gateway Router, M-Pesa/MTN MoMo, Escrow Wallet, Seller Wallet, Seller
- Flow: Checkout → STK Push → Payment confirmed → Funds held in escrow → Delivery → Confirm → Release to seller → Commission deduct → Seller withdraws

**Diagram 2: Unified Payment Layer Architecture**
- Type: Component diagram
- Components: API → GatewayRouter → Provider Adapters (M-Pesa, MTN MoMo, Airtel, ClickPesa, IremboPay) → MNO APIs
- Side: Escrow Manager, Commission Calculator, Wallet Service, Transaction Logger
- Show: Adapter pattern normalization

### Key Data Points (with Citations)
- "M-Pesa Daraja 3.0: **12,000 TPS** capacity, **105K+ registered developers**" (Research Synthesis §4.1)
- "MTN MoMo Open API: go-live **~10 days** after KYC" (Research Synthesis §4.1)
- "Escrow required by **BoT, BoU, CBK** regulations" (Research Synthesis §4.2)
- "Escrow service fee: **1-1.5%** of transaction value" (Research Synthesis §4.4)
- "Onafriq for cross-border: **1B wallets**, **400K agents**, **30+ BINs**" (Research Synthesis §4.2)
- "Mobile money processes **$1+ trillion** annually across Africa" (GSMA)
- "Commission-transparent pricing builds trust with providers" (Cross-Insight #1)

### Connection Points
- **Forwards to:** Chapter 16 (Security — financial data protection), Chapter 17 (DevOps — payment service deployment)
- **Backwards from:** Chapter 9 (Marketplace — escrow for purchases), Chapter 12 (Services — booking payments)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "The 5-Provider Challenge" — why PaymentGatewayRouter pattern is essential
- **Callout:** "Escrow = Regulatory Requirement" — not optional, required by all 4 central banks
- **Callout:** "M-Pesa: 12,000 TPS" — why Kenya's payment infrastructure leads the region
- **Chart:** Mobile money market share by country (pie charts for TZ, KE, UG, RW)

---

## Chapter 15: Real-Time, Logistics & Maps (~800 words)

### Purpose
Cover real-time features: GPS tracking, WebSockets, push notifications, logistics routing, and map integration.

### Opening Hook
"When a farmer books a truck to transport 2 tons of maize from Mbeya to Dar es Salaam, she needs to know where the truck is, when it will arrive, and whether her produce is safe. MkulimaForum's real-time architecture delivers this through WebSockets, GPS tracking, and intelligent notifications — all designed to work even when the driver's phone switches between 4G and 2G networks."

### Section Structure
1. **Real-Time Architecture Overview** (100 words) — Reverb, FCM, triple-redundant delivery
2. **GPS Tracking & Maps** (200 words) — Mapbox, offline tiles, route optimization
3. **Push Notifications** (150 words) — FCM, topic-based, behavioral targeting
4. **Logistics Tracking** (150 words) — driver location, ETA, proof of delivery
5. **WebSocket Broadcasting** (100 words) — Reverb, presence channels, events
6. **Offline Map Strategy** (100 words) — tile caching, rural coverage

### Key Tables

**Table 1: Real-Time Component Matrix**
| Column Headers | `Feature` | `Technology` | `Latency` | `Offline Fallback` | `Cost` |
|---|---|---|---|---|---|
| Rows | In-app chat (Laravel Reverb / <200ms / None / ~$5-50/mo); Push notifications (FCM / <5s / SMS / Free tier); GPS tracking (Mapbox + Background location / 10s updates / Cached route / ~$425/mo); Live order status (Reverb + FCM / <2s / Pull-to-refresh / Included); Pest alert geofencing (PostGIS + FCM / <1min / SMS / Included); Driver location sharing (WebSocket + Mapbox / 5s / Last known / ~$200/mo); Forum real-time (Reverb / <500ms / N/A / Included) |
| Source | Research Synthesis §6.5 |

**Table 2: Mapbox vs Google Maps Cost Comparison**
| Column Headers | `Metric` | `Mapbox` | `Google Maps` | `Savings` |
|---|---|---|---|---|
| Rows | Monthly cost at 10K users (~$2,325 / ~$4,300 / **46%**); Map loads (Free tier / $7/1K / N/A); Directions (~$400/mo / ~$5/1K / **60%**); Geocoding (Free tier / $5/1K / **100%**); GPS tracking (~$425/mo / ~$10/1K loads / **70%**); Offline tiles (Native support / Limited / N/A); Custom styling (Full / Limited / N/A); East Africa coverage (Excellent / Excellent / Equal) |
| Source | Research Synthesis §5.3 |

**Table 3: Notification Types**
| Column Headers | `Type` | `Trigger` | `Channel` | `Priority` | `Target` |
|---|---|---|---|---|---|
| Rows | Order update (Status change / FCM + in-app / High / Buyer/seller); Delivery alert (Driver approaching / FCM + SMS / Critical / Buyer); Pest outbreak (Geofenced alert / FCM + SMS / Critical / Farmers in zone); Weather warning (Severe forecast / FCM + SMS / High / Affected farmers); Forum reply (New comment / FCM + in-app / Normal / Post author); Appointment reminder (15 min before / FCM + SMS / High / Both parties); Payment confirmed (Wallet update / FCM / High / User); Emergency vet (Urgent request / FCM + SMS + push / Critical / Nearest vets) |
| Source | Requirements IF-006 |

### Code Examples

**Code 1: Laravel Reverb Broadcasting Event**
```php
// app/Events/DriverLocationUpdated.php
// ~25 lines
// Shows: implements ShouldBroadcast, driver_id, lat, lng, timestamp,
// broadcastOn private channel, broadcastWith payload
```

**Code 2: Background GPS Tracking in Flutter**
```dart
// lib/services/location/background_tracker.dart
// ~35 lines
// Shows: Background location plugin setup, 10s interval,
// battery optimization, Mapbox Map Matching, offline buffer
```

### Architecture Diagrams

**Diagram 1: Real-Time Notification Architecture**
- Type: Event flow diagram
- Flow: Event Trigger → Laravel Event → Reverb (in-app) + FCM (push) + DB (persistent) → Triple-redundant delivery
- Show: Each channel as parallel path with fallback

**Diagram 2: Logistics Tracking Flow**
- Type: Sequence diagram
- Driver → GPS → Background Location → Mapbox → WebSocket → Server → Broadcast → Farmer Map View → ETA Update

### Key Data Points (with Citations)
- "Laravel Reverb: **90% cost reduction** vs Pusher ($1,200/yr → ~$60/yr)" (Research Synthesis §6.5)
- "FCM: **free tier**, native Flutter integration, topic-based messaging" (Requirements §4)
- "Mapbox: **46% cheaper** than Google Maps at 10K users" (Research Synthesis §5.3)
- "Background GPS: **<5% battery/hour**" (Requirements NF-007)
- "Battery efficiency target: app overall **<10% daily battery**" (Requirements NF-007)

### Connection Points
- **Forwards to:** Chapter 17 (DevOps — Reverb deployment), Chapter 12 (Services — logistics)
- **Backwards from:** Chapter 4 (System Architecture), Chapter 8 (Flutter — GPS UI)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "Triple-Redundant Delivery" — WebSocket + FCM + DB ensures no notification is lost
- **Callout:** "Mapbox: 46% Cheaper Than Google" — cost optimization for logistics at scale
- **Callout:** "<5% Battery/Hour" — background tracking optimized for rural all-day usage

---

## Chapter 16: Security, Compliance & Data Sovereignty (~800 words)

### Purpose
Cover threat model, authentication, data protection, regulatory compliance, and the trust architecture that underpins the entire platform.

### Opening Hook
"MkulimaForum handles KYC documents, mobile money transactions, farm GPS boundaries, and health records. A breach would not just expose data — it could reveal a farmer's precise location, financial status, and crop diseases to adversaries. Security is not a feature to be added later; it is the foundation everything else is built upon."

### Section Structure
1. **Threat Model** (150 words) — 6 key threats, attack data, countermeasures
2. **Authentication Architecture** (150 words) — Passkey, PIN, biometric, anti-SIM-swap
3. **Data Protection** (150 words) — AES-256 at rest, TLS 1.3 in transit, PII hashing
4. **Data Sovereignty** (150 words) — African cloud, per-country isolation, PDPA compliance
5. **Regulatory Compliance** (100 words) — 4 countries, 4 frameworks
6. **Audit & Monitoring** (100 words) — immutable logs, anomaly detection

### Key Tables

**Table 1: Threat Matrix & Countermeasures**
| Column Headers | `Threat` | `Prevalence` | `Impact` | `Countermeasure` | `Effectiveness` |
|---|---|---|---|---|---|
| Rows | Social engineering (58-72% of fraud / Critical / Passkey/WebAuthn / Eliminates phishing); SIM swap (43% of attacks / Critical / Device fingerprinting + carrier number-lock / Near-elimination); Agent-assisted fraud (38% of attacks / High / Biometric auth + PIN + device binding / **72% fraud reduction**); Fake payment notifications (Common / High / API callback verification (not SMS) / 100% detection); Mobile malware (Growing / Medium / Certificate pinning + root detection / High); Credential stuffing (Common / Medium / Rate limiting + Passkey / Eliminates passwords) |
| Source | Research Synthesis §6.4 |

**Table 2: Authentication Methods by Sensitivity**
| Column Headers | `Action` | `Method` | `Fallback` | `Frequency` |
|---|---|---|---|---|
| Rows | App login (Passkey + PIN / OTP to trusted device / Every session); High-value transaction ($50+) (Biometric + PIN / OTP / Per transaction); KYC document upload (Biometric + device binding / Video call / Per upload); Wallet withdrawal (PIN + device fingerprint / OTP + security question / Per withdrawal); Account settings change (Biometric + PIN / OTP / Per change); Escrow dispute (Human review + ID verification / Video call / Per dispute) |
| Source | Requirements IF-001, NF-009, Research Synthesis §6.4 |

**Table 3: Data Sovereignty by Country**
| Column Headers | `Country` | `Law` | `Local Storage` | `Cross-Border` | `Key Requirement` |
|---|---|---|---|---|---|
| Rows | Tanzania (PDPA 2022 / Required for sensitive / Prohibited without compliance / Consent + purpose limitation); Kenya (Data Protection Act 2019 / Recommended / Adequacy determination / Data processor registration); Uganda (Data Protection Act 2019 / Recommended / Standard clauses / Consent + retention limits); Rwanda (Law 058/2021 / Encouraged / Permitted with safeguards / Sensitive category protection) |
| Source | Research Synthesis §4.3 |

### Code Examples

**Code 1: SIM Swap Detection + Device Fingerprinting**
```php
// app/Services/Security/DeviceFingerprintService.php
// ~30 lines
// Shows: generateFingerprint(request), detectSIMSwap($phone, $fingerprint),
// risk score calculation, step-up auth trigger
```

**Code 2: Row-Level Security for Data Isolation**
```sql
-- ~20 lines
-- Purpose: Per-country data sovereignty via RLS
-- Shows: country_code RLS policy, audit logging trigger,
-- data encryption at rest with AES-256
```

### Architecture Diagrams

**Diagram 1: Threat Model + Defense Layers**
- Type: Layered defense diagram
- Layers: User (Passkey, Biometric) → Device (PIN, Fingerprint) → Network (TLS 1.3, Cert Pinning) → Application (Rate Limiting, Input Validation) → Data (AES-256, RLS, Audit Logs) → Infrastructure (WAF, DDoS, VPC)
- Show: Attack vectors at each layer and corresponding defenses

**Diagram 2: Data Sovereignty Architecture**
- Type: Data flow / deployment diagram
- Show: User data → Cloudflare (Africa edge) → AWS af-south-1 → PostgreSQL with country_code RLS → Per-country data isolation
- Annotations: Data never leaves African cloud region

### Key Data Points (with Citations)
- "Social engineering: **58-72% of all fraud** in East African mobile money" (Research Synthesis §6.4)
- "SIM swap attacks: **43%** of account takeovers" (Research Synthesis §6.4)
- "Biometric + device binding: **72% fraud reduction** in Kenya" (Research Synthesis §6.4)
- "Tanzania PDPA 2022: data localization **required** for sensitive categories" (Research Synthesis §4.3)
- "AWS af-south-1 (Cape Town): **45-65ms latency** from East Africa" (Research Synthesis §2.1)
- "AES-256 encryption at rest, TLS 1.3 in transit" (Requirements NF-010)

### Connection Points
- **Forwards to:** Chapter 17 (DevOps — security in CI/CD)
- **Backwards from:** Chapter 14 (Payment — financial security), Chapters 5, 6 (backend and DB security)
- **Parallel with:** None

### Visual Elements
- **Info Box:** "Never Trust SMS OTP Alone" — SIM swap prevalence mandates multi-factor
- **Callout:** "72% Fraud Reduction" — biometric auth impact in Kenyan mobile money
- **Callout:** "African Cloud Only" — data sovereignty as trust signal and compliance requirement
- **Chart:** Threat prevalence pie chart (social engineering 58-72%, SIM swap 43%, agent fraud 38%)

---

## Chapter 17: DevOps, Deployment & Scaling (~600 words)

### Purpose
Cover CI/CD, container orchestration, monitoring, and scaling strategies for the East African cloud environment.

### Opening Hook
"MkulimaForum's deployment architecture must handle two realities: agricultural seasonality means 10x traffic spikes during planting and harvest, and East African cloud infrastructure is still maturing. The solution: serverless containers that scale to zero when idle and to hundreds during peak, with monitoring that alerts before a farmer notices a problem."

### Section Structure
1. **Deployment Architecture** (150 words) — Cloud Run/ECS, FrankenPHP, auto-scaling
2. **CI/CD Pipeline** (100 words) — GitHub Actions, testing, Firebase distribution
3. **Database Operations** (100 words) — backups, PITR, read replicas
4. **Monitoring & Alerting** (100 words) — Sentry, Pulse, Firebase Crashlytics
5. **Scaling Strategy** (100 words) — seasonality, auto-scaling, cost optimization
6. **Data Sovereignty in Deployment** (50 words) — African regions only

### Key Tables

**Table 1: Infrastructure Stack**
| Column Headers | `Component` | `Technology` | `Region` | `Scaling` | `Cost Model` |
|---|---|---|---|---|---|
| Rows | API server (Cloud Run or AWS ECS / af-south-1 / 0-1000 instances / Pay-per-use); Database (PostgreSQL 16 RDS / af-south-1 / Read replicas / Instance-based); Cache (Redis ElastiCache / af-south-1 / Cluster mode / Instance-based); Search (Meilisearch / af-south-1 / Single node / Instance-based); Storage (GCS or S3 / Multi-region / Unlimited / Storage + egress); CDN (Cloudflare / Africa edge / Auto / Flat); WebSockets (Laravel Reverb / af-south-1 / 1-10 instances / Fixed); Monitoring (Sentry + Pulse + Crashlytics / Global / N/A / Tiered) |
| Source | Requirements §4, Research Synthesis §2.1 |

**Table 2: Scaling Targets**
| Column Headers | `Metric` | `Normal` | `Peak (Planting/Harvest)` | `Scaling Trigger` |
|---|---|---|---|---|
| Rows | Concurrent users (10,000 / 100,000 / CPU >70%); API requests/min (50K / 500K / Latency >500ms); DB connections (100 / 1,000 / Connection pool >80%); Active WebSockets (5,000 / 50,000 / Memory >80%); Storage bandwidth (1Gbps / 10Gbps / Egress limit); AI requests/hour (1,000 / 10,000 / Queue depth >100) |
| Source | Requirements NF-004 |

**Table 3: CI/CD Pipeline Stages**
| Column Headers | `Stage` | `Action` | `Success Criteria` | `Duration` |
|---|---|---|---|---|
| Rows | Lint (PHP CS + Dart analyze / Zero warnings / 1 min); Unit tests (PHPUnit + Flutter tests / >80% coverage, 0 failures / 5 min); Integration tests (Postman/Newman / All endpoints pass / 10 min); Security scan (Snyk + dependency check / Zero critical/high / 3 min); Build (Docker image + Flutter APK / APK <30MB / 10 min); Deploy staging (Auto to staging env / Smoke tests pass / 5 min); E2E tests (Maestro / Critical flows pass / 15 min); Deploy production (Manual approval / Health checks pass / 5 min) |
| Source | Requirements NF-018, §4 |

### Code Examples

**Code 1: GitHub Actions CI/CD Workflow**
```yaml
// .github/workflows/deploy.yml
// ~40 lines
// Shows: on push main, lint job, test job, build job,
// deploy to staging (auto), e2e tests, deploy to production (manual)
```

### Architecture Diagrams

**Diagram 1: AWS Africa Deployment Architecture**
- Type: Infrastructure diagram
- Show: CloudFront (edge) → Route 53 → ALB → Cloud Run/ECS (FrankenPHP) → RDS PostgreSQL (Multi-AZ) + ElastiCache Redis + S3
- Annotations: af-south-1 region, read replica, backup to S3

### Key Data Points (with Citations)
- "Cloud Run: scales **0-1000** instances automatically" (Google Cloud)
- "FrankenPHP: **5-10x throughput** over PHP-FPM" (Research Synthesis §2.1)
- "Target: **50K concurrent users**, auto-scaling to **100K** during peak" (Requirements NF-004)
- "99.9% uptime for core services" (Requirements NF-011)
- "PITR recovery: **24-hour RPO**" (Requirements NF-012)

### Connection Points
- **Forwards to:** None (near end)
- **Backwards from:** All previous technical chapters
- **Parallel with:** Chapter 18 (Roadmap — when features deploy)

### Visual Elements
- **Info Box:** "Serverless = Seasonal Scaling" — pay nothing during low season, scale automatically during planting/harvest
- **Callout:** "African Cloud First" — all primary infrastructure in af-south-1
- **Chart:** Scaling curve showing normal vs peak agricultural seasons

---

## Chapter 18: Development Roadmap & Milestones (~600 words)

### Purpose
Provide a phased development plan with milestones, deliverables, and success criteria.

### Opening Hook
"MkulimaForum is not built in a single release. It unfolds across four phases — from a lean MVP that proves farmer adoption in Tanzania to a full regional platform serving 75M+ farmers across East Africa. Each phase has clear deliverables, measurable success criteria, and explicit go/no-go decisions."

### Section Structure
1. **Roadmap Philosophy** (50 words) — lean MVP, data-driven decisions
2. **Phase 1: Tanzania MVP** (150 words) — core modules, 3-month timeline
3. **Phase 2: Kenya + Uganda Expansion** (100 words) — multi-country, payment expansion
4. **Phase 3: Rwanda + Feature Completeness** (100 words) — 4th country, advanced AI
5. **Phase 4: EAC Cross-Border & B2G** (100 words) — cross-border, government partnerships
6. **Success Metrics** (100 words) — KPIs per phase

### Key Tables

**Table 1: Development Phases**
| Column Headers | `Phase` | `Duration` | `Countries` | `Modules` | `Team Size` | `Budget Estimate` |
|---|---|---|---|---|---|---|
| Rows | Phase 1: MVP (Months 1-3 / Tanzania only / Disease Scanner + Forum + Basic Marketplace + AI Chat / 4 devs / $45K); Phase 2: Expansion (Months 4-6 / TZ + KE + UG / Full marketplace + Payments + Services + Voice / 6 devs / $75K); Phase 3: Completion (Months 7-9 / All 4 countries / Advanced AI + Cross-border + USSD + PWA / 8 devs / $90K); Phase 4: Scale (Months 10-12 / EAC / B2G + Analytics + Satellite + IoT / 10 devs / $100K) |
| Source | Derived from requirements traceability |

**Table 2: Phase 1 MVP Deliverables (Month 3)**
| Column Headers | `Feature` | `Deliverable` | `Success Criteria` | `Effort` |
|---|---|---|---|---|
| Rows | Disease Scanner (TFLite model + 10 diseases / 70%+ accuracy offline / 3 weeks); Farmers Forum (Threaded posts + 3 categories / 100+ posts/day target / 2 weeks); AI Agronomist (RAG + Gemini Flash / 80%+ satisfactory responses / 2 weeks); Basic Marketplace (Product catalog + search / 50+ verified listings / 2 weeks); Auth + Wallet (Phone OTP + M-Pesa TZ / <2% payment failure / 2 weeks); Offline Core (72h offline for critical features / Functional without data / 2 weeks) |
| Source | Requirements traceability matrix |

**Table 3: Success Metrics by Phase**
| Column Headers | `Metric` | `Phase 1 (M3)` | `Phase 2 (M6)` | `Phase 3 (M9)` | `Phase 4 (M12)` |
|---|---|---|---|---|---|
| Rows | Registered users (5,000 / 50,000 / 200,000 / 1M); MAU (2,000 / 20,000 / 80,000 / 400K); Disease scans/week (500 / 5,000 / 20,000 / 100K); Marketplace GMV/month ($5K / $100K / $500K / $2M); Verified agrodealers (20 / 200 / 1,000 / 5,000); Forum posts/day (50 / 500 / 2,000 / 10K); NPS score (40 / 50 / 60 / 65); Retention (30d) (25% / 35% / 40% / 45%); Countries live (1 / 3 / 4 / 4+); Service bookings/month (0 / 500 / 5,000 / 25K) |
| Source | Stakeholder requirements, industry benchmarks |

**Table 4: Go/No-Go Decision Criteria**
| Column Headers | `Gate` | `Criteria` | `Decision` | `Fallback` |
|---|---|---|---|---|
| Rows | Phase 1→2 (NPS >40 AND retention >25% AND 5K users / Proceed to KE+UG / Iterate on TZ); Phase 2→3 (GMV >$100K/mo AND 3 countries stable / Add Rwanda + advanced AI / Fix payment issues); Phase 3→4 (MAU >80K AND services >5K bookings / EAC cross-border / Focus on core); Post-Phase 4 (B2G revenue >20% total / Scale + partnerships / Focus on B2C) |
| Source | Derived from stakeholder criteria |

### Code Examples
None.

### Architecture Diagrams

**Diagram 1: Development Timeline Gantt**
- Type: Gantt/timeline chart
- Phases: Phase 1 (M1-3), Phase 2 (M4-6), Phase 3 (M7-9), Phase 4 (M10-12)
- Bars: Parallel workstreams (Backend, Flutter, AI/ML, DevOps, QA)
- Milestones: MVP launch, KE+UG live, Rwanda live, Cross-border

**Diagram 2: Module Delivery Timeline**
- Type: Timeline chart
- Show: When each module ships per phase
- Phase 1: Scanner, Forum, AI Chat, Basic Marketplace
- Phase 2: Full Marketplace, Payments, Agronomist, Veterinary
- Phase 3: Logistics, Warehouse, Soil Testing, USSD, PWA
- Phase 4: Cross-border, B2G, Advanced AI, Satellite

### Key Data Points (with Citations)
- "Phase 1 MVP: **3 months**, **4 developers**, **$45K** estimated"
- "Phase 1 target: **5,000 registered users**, **500 disease scans/week**"
- "Phase 4 target: **1M registered users**, **$2M GMV/month**"
- "DigiFarm reached **1.6M registered** in ~3 years" (Research Synthesis §1.3)
- "Apollo Agriculture targets **1M farmers by 2027**" (Research Synthesis §1.3)
- "Rapid MVP launch mitigates **competitor preemption risk**" (Requirements §10)

### Connection Points
- **Forwards to:** None (final chapter)
- **Backwards from:** All previous chapters
- **Parallel with:** Chapter 17 (DevOps — deployment timeline)

### Visual Elements
- **Info Box:** "Lean MVP Philosophy" — launch with disease scanner + forum + AI, validate, expand
- **Callout:** "Data-Driven Go/No-Go" — every phase transition requires meeting success metrics
- **Chart:** Gantt chart showing 12-month development timeline
- **Chart:** User growth projection curve (5K → 50K → 200K → 1M)

---

# Appendix: Cross-Chapter Reference Matrix

## Table-to-Table References

| Table In Chapter | References Tables In | Relationship |
|---|---|---|
| Ch1: Competitive Positioning | Ch2: Platform Competitive Analysis | Summary vs detail |
| Ch4: Technology Stack | Ch5: Laravel Packages, Ch6: pgvector, Ch7: LLM/Model comparisons | Stack components |
| Ch5: RBAC Seeder | Ch3: User Roles Matrix | Implementation of roles |
| Ch6: Core Schema | Ch9-12: Module data models | Database foundations |
| Ch7: LLM Cost | Ch14: Commission structure | Cost optimization theme |
| Ch9: Seller Verification | Ch14: Escrow states | Trust architecture chain |
| Ch12: Provider Vetting | Ch9: Seller Verification | Shared vetting pattern |
| Ch14: Mobile Money APIs | Ch2: Country metrics | Payment rails per country |

## Diagram-to-Diagram References

| Diagram In Chapter | References Diagrams In | Relationship |
|---|---|---|
| Ch1: Ecosystem Map | Ch4: C4 Context | Conceptual → Technical |
| Ch4: C4 Context | Ch4: C4 Container | Context → Detail (C4 model) |
| Ch4: C4 Container | Ch5: Laravel Layers, Ch6: ER Diagram | Container → Component |
| Ch7: AI Stack (3-layer) | Ch8: Flutter Clean Architecture | Edge layer connects to Flutter |
| Ch7: RAG Pipeline | Ch7: Disease Scanner Decision Tree | Shared AI infrastructure |
| Ch9: Marketplace Sequence | Ch14: Payment Flow | Purchase → Payment |
| Ch12: Booking State Machine | Ch14: Escrow States | Booking → Payment hold |
| Ch15: Real-Time Events | Ch4: Offline-First Sync | Online + offline together |

## Code Example Cross-References

| Code Example | Related Code In | Shared Pattern |
|---|---|---|
| Ch4: Tenant Resolution Middleware | Ch5: API Route Middleware Stack | Tenant scoping |
| Ch5: RBAC Seeder | Ch9: Seller Verification | Permission system |
| Ch6: pgvector Similarity Search | Ch7: RAG Pipeline | Vector search usage |
| Ch7: TFLite Flutter Integration | Ch8: BLoC Offline Pattern | Flutter service layer |
| Ch9: Escrow Payment Flow | Ch14: Escrow Sub-Wallet | Same service, different view |
| Ch13: Delta Sync | Ch8: Drift Sync | API ↔ Local DB sync |
| Ch14: Payment Gateway Router | Ch14: M-Pesa Adapter | Adapter pattern |
| Ch16: SIM Swap Detection | Ch5: Auth Middleware | Security in auth flow |

---

# Appendix: Data Point Master Index

## Statistics Used Across Multiple Chapters

| Statistic | Value | Primary Chapter | Also Used In | Source |
|---|---|---|---|---|
| Agriculture GDP share | 25-40% | Ch1, Ch2 | Ch18 | EAC statistics |
| Post-harvest losses | 40%, $4.5B | Ch1, Ch2 | Ch12 | Research Synthesis §1.2 |
| Extension ratio Kenya | 1:1,380 | Ch1, Ch2, Ch7 | Ch12 | Research Synthesis §1.2 |
| Extension ratio Tanzania | 1:1,172 | Ch1, Ch2, Ch7 | Ch12 | Research Synthesis §1.2 |
| Smartphone penetration TZ | 41.8% | Ch1, Ch2 | Ch8 | TCRA 2025 |
| Feature phone ownership | 77.5% | Ch2 | Ch10 | Dim 01 |
| Women's mobile internet gap | 24% vs 35% | Ch2 | Ch8 | GSMA 2025 |
| Mobile money annual volume | $1T+ | Ch1, Ch14 | — | GSMA |
| Fall Armyworm losses | $13B/year | Ch1, Ch11 | Ch7 | Research Synthesis §1.5 |
| Gemini 2.0 Flash cost | $0.075/1M tokens | Ch7 | Ch1 | Research Synthesis §3.1 |
| pgvector performance | 471 QPS, 28ms p95 | Ch6, Ch7 | Ch4 | Research Synthesis §2.1 |
| M-Pesa Daraja TPS | 12,000 | Ch14 | — | Research Synthesis §4.1 |
| Escrow fee | 1-1.5% | Ch14 | Ch9 | Research Synthesis §4.4 |
| FrankenPHP throughput | 5-10x | Ch5, Ch17 | Ch4 | Research Synthesis §2.1 |
| Reverb cost reduction | 90% vs Pusher | Ch15, Ch17 | Ch4 | Research Synthesis §6.5 |
| Mapbox cost savings | 46% vs Google | Ch15 | Ch12 | Research Synthesis §5.3 |
| iProcure fill rate | 94% | Ch2, Ch9 | — | Research Synthesis §1.3 |
| Social engineering fraud | 58-72% | Ch16 | — | Research Synthesis §6.4 |
| SIM swap attacks | 43% | Ch16 | — | Research Synthesis §6.4 |
| Biometric fraud reduction | 72% | Ch16 | — | Research Synthesis §6.4 |
| Cold chain market | $12.87B → $18.29B | Ch1, Ch12 | — | Research Synthesis §1.2 |

---

*Document prepared for MkulimaForum Architecture Writing Team*
*Based on: Requirements Analysis v1.0 | Research Synthesis (7 dimensions, 200+ sources) | 10 Strategic Insights | Cross-Verification Report*
