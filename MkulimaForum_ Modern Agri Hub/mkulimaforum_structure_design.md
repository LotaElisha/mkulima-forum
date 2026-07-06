# MkulimaForum Architecture — Structure Design

> **Document**: MkulimaForum Software Architecture Document (SAD) — Chapter Hierarchy & Structural Blueprint
> **Target Word Count**: 16,800 words (within 15,000–18,000 range)
> **Audience**: Technical architects, CTOs, senior developers, investors
> **Format**: 4-level heading hierarchy (H1 unnumbered; H2/H3/H4 numbered). H5 is forbidden.
> **Design Philosophy**: Deeply East African, distinctly MkulimaForum, 2026-modern

---

## Design Principles

1. **Standout Sections**: Plant Disease Scanner (Chapter 9.2), AI/ML Platform (Chapter 7), and Services Marketplace (Chapter 9.3–9.8) receive the deepest treatment — more subsections, more tables, more diagrams, more code examples.
2. **East African DNA**: Every chapter embeds regional specifics — crop types, mobile money rails, regulatory bodies, research institutes (TARI, KALRO, NARO, RAB), Swahili language considerations.
3. **2026 Architecture**: Laravel 13.x with first-party JSON:API, FrankenPHP, pgvector, RAG pipelines, on-device ML, voice-first design — not 2020-era patterns.
4. **Trust as Foundation**: The "trust gap" insight (Insight 1) shapes the architecture narrative — KYC, escrow, and verification are core, not peripheral.
5. **Voice-First Inclusion**: The Voice Service Layer (VSL) is a first-class architectural component, reflecting Insight 5 about literacy and device constraints.
6. **No H5**: All content flattens into H4 numbered sections. Where depth exceeds H4, content points use bold lead sentences, not subheadings.

---

## Chapter Hierarchy

### Chapter 1: Executive Summary & Vision (~900 words, 2 tables, 2 diagrams)
#### 1.1 The $18.4 Billion Opportunity
**1.1.1 The East African agricultural landscape.** Agriculture accounts for 25–40% of EAC Partner States GDP, yet 40% of fresh produce is lost post-harvest. Tanzania alone represents an $18.42B agricultural market growing to $24.23B by 2030.
**1.1.2 The digital agriculture imperative.** Smartphone penetration at 41.8% (TZ), extension officer ratios of 1:1,172 (vs. FAO standard of 1:400), and $13B annual losses to Fall Armyworm create an urgent need for a unified digital platform.
**1.1.3 MkulimaForum vision statement.** A comprehensive agricultural super-app serving as the digital backbone for East African smallholder farmers — combining marketplace, AI-powered diagnostics, expert services, and community in one offline-first platform.

#### 1.2 Platform at a Glance
**1.2.1 Five core pillars.** Agrodealer Marketplace, Plant Disease Scanner, Farmers Forum, Services Marketplace (6 service categories), and AI Agronomist — unified under the MkulimaForum brand.
**1.2.2 Key differentiators.** Hybrid on-device + cloud AI, voice-first Swahili interface, offline-first architecture, multi-country mobile money escrow, and RAG-powered agricultural knowledge system.
**1.2.3 Target countries and rollout strategy.** Tanzania (launch), Kenya, Uganda, Rwanda — with country-scoped multi-tenant architecture from day one.

#### 1.3 Success Metrics & Impact Projections
**1.3.1 Quantified platform targets.** 50,000 concurrent users at launch scale, 85%+ disease diagnosis accuracy, $1–3/farmer/year AI extension cost (10x reduction vs. traditional), 72-hour offline operation capability.
**1.3.2 Ecosystem impact goals.** Replacement of 50,000+ missing extension officers through AI, reduction of post-harvest losses via cold chain digitization, and cooperative/SACCO integration reaching $2B+ in annual transactions.

### Chapter 2: East African Context — Problem, Stats & Opportunity (~1,100 words, 3 tables, 1 diagram)
#### 2.1 The Smallholder Reality
**2.1.1 Scale of smallholder agriculture.** 80% of East African farms are rain-fed smallholdings under 2 hectares. 76.5% of farmers own livestock. Agriculture employs 43.7% of Rwanda's workforce and 26.2% of Tanzania's GDP.
**2.1.2 The connectivity divide.** 99.3% mobile phone penetration in Tanzania but only 41.8% smartphone ownership. 77.5% own only non-smartphones. Rural internet use: 7.7%. The gender gap: 24% women vs. 35% men use mobile internet.
**2.1.3 Post-harvest losses and cold chain gaps.** 40% of fresh produce lost post-harvest ($4.5B annual loss in East Africa). Only 5% passes through cold chain. Cold chain market growing from $12.87B to $18.29B by 2032.

#### 2.2 Competitive Landscape & Gap Analysis
**2.2.1 Existing platforms analysis.** DigiFarm (1.6M registered, Kenya-only), FarmerChat (830K+ users, no marketplace), Apollo Agriculture (100K+ farmers, credit-focused), Twiga Foods (130+ tons/day, scaled back), Wefarm (1.8M farmers, declining), Maathai (offline-first, early stage).
**2.2.2 The MkulimaForum positioning.** Unlike single-feature competitors, MkulimaForum unifies marketplace + AI diagnostics + expert services + community + offline-first + voice interface + multi-country — creating ecosystem lock-in through the service marketplace data flywheel.

#### 2.3 Country-Specific Context
**2.3.1 Tanzania — launch market profile.** Swahili-dominant, M-Pesa/Tigo Pesa/Airtel Money/HaloPesa/Mixx by YAS, TFRA regulation, TARI partnership, key crops: maize, cassava, rice, bananas, coffee, cotton.
**2.3.2 Kenya — mature market profile.** M-Pesa dominance (30M+ users, Daraja 3.0 at 12K TPS), PCPB/KEPHIS regulation, KALRO partnership, key crops: tea, coffee, maize, horticulture.
**2.3.3 Uganda and Rwanda expansion profiles.** Uganda: MTN MoMo dominant, UNADA/MAAIF regulation, NARO partnership, matooke/coffee specialty. Rwanda: MTN MoMo/Airtel Money, BNR/RSB/RAB regulation, coffee/tea/potatoes focus, PSTA5 digital agriculture vision.

### Chapter 3: Platform Overview — All MkulimaForum Modules (~1,200 words, 2 tables, 2 diagrams)
#### 3.1 System Modules Map
**3.1.1 Module interaction overview.** How the five pillars connect: marketplace purchases trigger logistics bookings, disease scans recommend marketplace products, soil tests inform agronomist consultations, forum discussions feed the RAG knowledge base, and all data improves AI recommendations.
**3.1.2 User types and RBAC matrix.** Eight roles: farmer, agrodealer, agronomist, veterinary_officer, logistics_provider, warehouse_operator, admin, extension_officer — with granular permissions per module and country scope.
**3.1.3 Cross-module data flows.** The data flywheel: soil test data improves crop recommendations, veterinary records improve livestock advice, purchase history personalizes marketplace suggestions, disease reports inform regional pest alerts.

#### 3.2 Module Specifications
**3.2.1 Agrodealer Marketplace module.** Multi-vendor e-commerce for agricultural inputs (seeds, fertilizers, pesticides, tools). Features: search/filtering, cart, escrow checkout, order management, inventory tracking, sales analytics, TFRA/PCPB/UNADA compliance integration.
**3.2.2 Plant Disease Scanner module.** Hybrid AI diagnosis: on-device TensorFlow Lite (MobileNetV3-Small, 2.5MB) for 20 common East African diseases + Gemini Vision cloud fallback for rare/uncertain cases. Severity estimation, treatment product linking, offline capability.
**3.2.3 Farmers Forum & Community module.** Threaded discussions with rich media, expert verification badges, AI-powered FAQ suggestions via RAG, voice-note posts, upvoting/downvoting, localized sub-forums per region, content moderation.
**3.2.4 AI Agronomist module.** RAG-powered conversational assistant with TARI/KALRO/NARO/RAB knowledge base, voice input/output in Swahili, crop recommendations combining soil data + weather + market prices, fertilizer advice from soil analysis.
**3.2.5 Services Marketplace — six service categories.** Agronomist consultation (booking, chat, video), Logistics & Transport (boda-to-truck, GPS tracking, route optimization), Warehouse (storage search, IoT monitoring, seasonal pricing), Veterinary (tele-vet, farm visits, vaccination schedules), Soil Testing (sample collection, lab results, AI fertilizer recommendations).

#### 3.3 Shared Platform Services
**3.3.1 Cross-cutting service layer.** Authentication (phone OTP + biometric), KYC verification, wallet & escrow, push notifications (FCM + SMS fallback), search & discovery (Meilisearch), analytics & reporting, content moderation.
**3.3.2 Infrastructure services.** Weather integration (Open-Meteo), GPS tracking (Mapbox), image compression & upload, multi-language i18n (English/Swahili/French/Luganda), USSD fallback, PWA support.

### Chapter 4: System Architecture — High-Level Design (~1,500 words, 2 tables, 3 diagrams)
#### 4.1 Architectural Philosophy
**4.1.1 Design principles.** Domain-driven design with bounded contexts, API-first with JSON:API standard, offline-first as default, trust-by-design (KYC + escrow + verification), voice-first for inclusion, multi-country from day one.
**4.1.2 Technology stack overview.** Laravel 13.x (PHP 8.3+) with first-party JSON:API, FrankenPHP application server, Flutter 3.24+ with Impeller, PostgreSQL 16+ with pgvector, Redis 7.x, Meilisearch, Firebase Cloud Messaging, Cloudflare.
**4.1.3 C4 Context and Container diagrams.** System context: farmers, agrodealers, service providers, government partners, platform admin. Container diagram: mobile app, web admin, API gateway, microservices, databases, external services.

#### 4.2 Microservices Architecture
**4.2.1 Service decomposition strategy.** Core services: Auth Service, User Service, Marketplace Service, Disease Scanner Service, Forum Service, Services Marketplace Service, AI Orchestration Service, Payment Service, Notification Service, Analytics Service.
**4.2.2 Service communication patterns.** Synchronous (REST/JSON:API) for user-facing operations, asynchronous (Redis queues + Laravel Horizon) for background jobs, WebSockets (Laravel Reverb) for real-time features, event-driven for cross-service notifications.
**4.2.3 API Gateway and routing.** Single entry point with country-code routing, rate limiting (per-second in Laravel 13), authentication middleware, request transformation, response caching, BFF pattern for mobile-optimized payloads.

#### 4.3 Multi-Tenancy & Regional Architecture
**4.3.1 Country-scoped tenant isolation.** Shared PostgreSQL database with `country_code` tenant key, PostgreSQL Row-Level Security (RLS) policies, `TenantAwareModel` base class with global scope, subdomain routing (`tz.mkulimaforum.com`).
**4.3.2 Data sovereignty architecture.** Per-country database isolation via RLS, AWS af-south-1 (Cape Town) primary with ~45-65ms latency, CloudFront edge caching across East Africa, roadmap for in-country hosting (Vodacom Cloud TZ, Safaricom Cloud KE).
**4.3.3 Regional customization.** Per-country mobile money gateway configs, regulatory compliance modules, crop disease knowledge bases, seasonal crop calendars, language packs, and product catalog scoping.

### Chapter 5: Laravel Backend Architecture — Domain-Driven Design (~1,300 words, 2 tables, 2 diagrams)
#### 5.1 Domain-Driven Design Structure
**5.1.1 Bounded contexts.** Auth Domain (Sanctum 4.x, RBAC), Marketplace Domain (products, orders, inventory), Forum Domain (threads, posts, moderation), Scanner Domain (diagnosis, model management), Services Domain (booking, scheduling, provider management), AI Domain (RAG, LLM orchestration), Payment Domain (wallets, escrow, commissions).
**5.1.2 Domain layer implementation.** Entities, value objects, domain events, repositories (interface + Eloquent implementation), domain services, policy classes. Clean separation between domain and infrastructure.
**5.1.3 Application layer.** Commands, queries (CQRS pattern), handlers, DTOs, service layer coordinating domain objects. Application services for cross-domain operations.

#### 5.2 Laravel 13.x Modern Patterns
**5.2.1 First-party JSON:API resources.** Native sparse fieldsets, compound documents, cursor pagination, relationship inclusion. Eliminates third-party package dependency.
**5.2.2 Laravel Reverb for real-time.** 90% cost reduction vs. Pusher ($1,200/yr to ~$60/yr), 40% lower latency, first-party WebSocket server, event broadcasting for live tracking and notifications.
**5.2.3 FrankenPHP application server.** 5-10x throughput improvement over PHP-FPM, HTTP/2 and HTTP/3 support, Go-based, zero-downtime deployments with Laravel Octane.
**5.2.4 Laravel AI SDK integration.** First-party AI SDK for LLM orchestration with provider abstraction (Gemini primary, OpenAI fallback, self-hosted Llama 3 option for data sovereignty).

#### 5.3 Code Organization & Quality
**5.3.1 Directory structure.** `app/Domains/{Domain}/{Entity,Repository,Service,Event}/`, `app/Http/Resources/JsonApi/`, `app/Notifications/`, `database/factories/Domain/`, modular service provider registration per domain.
**5.3.2 Testing strategy.** PHPUnit with >80% coverage target, Pest PHP for expressive syntax, factories and seeders per domain, integration tests with Testcontainers for PostgreSQL, contract tests for API specifications.
**5.3.3 Package ecosystem.** Spatie Laravel Permission 6.x (RBAC), Spatie Laravel Media Library 11.x (image handling), Spatie Laravel Multitenancy 4.x (regional scoping), Laravel Scout + Meilisearch (search), Laravel Horizon (queue monitoring).

### Chapter 6: Database Architecture — PostgreSQL + pgvector + Advanced Schema (~1,000 words, 3 tables, 2 diagrams)
#### 6.1 PostgreSQL Schema Design
**6.1.1 Core schema overview.** 40+ tables across 7 domains: `users`, `profiles`, `kyc_verifications`, `products`, `orders`, `order_items`, `forum_threads`, `forum_posts`, `diagnoses`, `service_bookings`, `wallets`, `transactions`, `notifications`, `crop_diseases`, `knowledge_chunks`, `soil_tests`.
**6.1.2 Multi-tenancy with RLS.** `country_code` column on every tenant-scoped table, PostgreSQL RLS policies enforcing `current_setting('app.current_country')`, global query scopes in Eloquent, tenant resolution via subdomain or header.
**6.1.3 Partitioning strategy.** Monthly range partitioning for `orders` and `transactions` tables, native PostgreSQL 16 partitioning, automated partition creation via cron, read replicas for reporting queries, PgBouncer connection pooling.

#### 6.2 Vector Database with pgvector
**6.2.1 pgvector extension setup.** `vector` data type for embeddings, HNSW indexing for fast similarity search, pgvectorscale extension for production workloads, same PostgreSQL instance eliminating separate vector DB infrastructure.
**6.2.2 RAG knowledge base schema.** `knowledge_chunks` table: id, content (text), embedding (vector(1536)), source (TARI/FAO/KEPHIS), metadata (JSONB), country_code, created_at. 471 QPS at 28ms p95 with pgvectorscale at 50M vectors.
**6.2.3 Semantic search implementation.** Cosine similarity queries for RAG retrieval, hybrid search combining vector similarity with full-text (tsvector), reranking with cross-encoder, metadata filtering by country and crop type.

#### 6.3 Specialized Data Types
**6.3.1 PostGIS for geospatial.** `geometry` columns for farm boundaries, warehouse locations, delivery routes, service provider coverage areas. Geo-indexing for nearby searches, GPS tracking point storage.
**6.3.2 JSONB for flexible attributes.** Product specifications, service provider availability, forum post metadata, diagnosis results, soil test nutrient breakdown — schema-flexible without migrations.
**6.3.3 Full-text search with tsvector.** Combined with Meilisearch for faceted product/service search, Swahili language support, typo-tolerant autocomplete, synonym dictionaries for agricultural terms.

### Chapter 7: AI/ML Integration — The Brain of MkulimaForum (~2,000 words, 5 tables, 5 diagrams)
> *Standout section — the AI architecture is MkulimaForum's primary differentiator and the deepest technical chapter.*

#### 7.1 AI Architecture Overview
**7.1.1 The three-layer AI stack.** Edge layer (on-device TF Lite for offline diagnosis), Application layer (RAG pipeline, recommendation engine, voice processing), Cloud layer (Gemini 2.0 Flash, OpenAI fallback, self-hosted Llama 3 option).
**7.1.2 AI service topology diagram.** Client (Flutter + TF Lite) → API Gateway → AI Orchestration Service → Vector DB (pgvector) + LLM providers + Model registry. Data ingestion pipeline from TARI/KALRO/NARO/RAB sources.
**7.1.3 Cost optimization strategy.** Gemini 2.0 Flash at $0.075/1M tokens ($21/month at 50K queries), pgvector zero additional cost, TF Lite on-device free, strategic fallback to GPT-4o only for complex reasoning. Total AI operational cost: ~$100-200/month at launch scale.

#### 7.2 Plant Disease Detection System
**7.2.1 Model selection and comparison.** MobileNetV3-Small (2.54MB, 67.7% Top-1, NNAPI) as primary on-device model. MobileNetV3-Large quantized (2.96MB, 73% Top-1) for higher-end devices. DenseNet201 (30MB, 96%) for premium tier. Gemini Vision cloud fallback (80-90%) for rare diseases and second opinions.
**7.2.2 Hybrid inference pipeline.** Offline path: TF Lite inference on captured image → top-k predictions → severity estimation → treatment recommendation lookup. Online path: confidence < 70% → Gemini Vision analysis → human agronomist review queue. Active learning loop from farmer feedback.
**7.2.3 East African crop disease coverage.** 20 priority diseases: Maize Lethal Necrosis, Fall Armyworm damage, Cassava Brown Streak Disease, Cassava Mosaic Disease, Banana Xanthomonas Wilt, Banana Bacterial Wilt, Coffee Leaf Rust, Coffee Berry Disease, Rice Blast, Bean Anthracnose, Sweet Potato Virus Disease, Tomato Early Blight, Irish Potato Late Blight, Groundnut Rosette, Maize Streak Virus, Wheat Rust, Cotton Bollworm, Tea Blister Blight, Tobacco Mosaic Virus, Sorghum Downy Mildew.
**7.2.4 Model training and improvement pipeline.** Continuous training from farmer-submitted images with expert labels, confidence calibration for field conditions (10-40% accuracy drop mitigation), A/B testing between model versions, quarterly model releases.

#### 7.3 RAG Knowledge System (AI Agronomist)
**7.3.1 Knowledge ingestion pipeline.** Source documents (TARI PDFs, FAO guidelines, KEPHIS alerts, KALRO research, iSDAsoil data) → text extraction → chunking with semantic boundaries → multilingual embedding (Swahili-English) → vector storage in pgvector with metadata tagging.
**7.3.2 Retrieval and generation pipeline.** Query embedding → pgvector similarity search (cosine, top-10) → reranking with cross-encoder → context assembly (top-5 chunks) → prompt engineering with system instructions → Gemini 2.0 Flash generation → response validation → citation attribution.
**7.3.3 Conversational memory and context.** Per-user conversation history, farm profile context integration (crop types, soil data, location, season), follow-up query handling, multi-turn conversation management, conversation summarization for long sessions.
**7.3.4 Fine-tuned agricultural LLM option.** Mistral-7B or Llama-3-8B base, QLoRA 4-bit fine-tuning (6GB VRAM on T4 GPU), 5K-20K Swahili-English agricultural examples, 92% quality of full fine-tuning, self-hosted for data sovereignty and offline capability.

#### 7.4 Soil Analysis AI
**7.4.1 AI-powered fertilizer recommendations.** XGBoost model (99.09% accuracy for agricultural crops, 99.3% horticultural) trained on iSDAsoil data + lab results. Input: 14 soil variables (pH, N, P, K, S, Ca, Mg, B, Cu, Fe, Mn, Zn, clay, organic carbon). Output: crop-specific fertilizer blends and application rates.
**7.4.2 iSDAsoil integration.** REST API queries at 30m resolution (free), 14 chemical/physical variables, coverage of all sub-Saharan Africa. Tier 1: instant AI recommendations from iSDAsoil data. Tier 2: physical soil sample collection request. Tier 3: lab-processed results with precision recommendations.
**7.4.3 Soil data correlation with crop recommendations.** Integration of soil analysis, weather forecasts (Open-Meteo), market prices, and regional crop calendars to generate personalized planting recommendations with expected yield projections.

#### 7.5 Voice Service Layer (VSL)
**7.5.1 Speech-to-text pipeline.** Whisper Small fine-tuned for Swahili (~17% WER) as primary STT. Whisper Tiny (39MB) for offline capability (~25-30% WER). Google Cloud Speech API (`sw-KE`, `sw-TZ`) as fallback. Audio preprocessing: noise reduction, segmentation, format normalization.
**7.5.2 Text-to-speech pipeline.** Google Cloud TTS with Swahili voices (Daudi male, Rehema female). Azure Speech Service alternative (`sw-KE`, `sw-TZ`). Voice selection based on user preference. SSML for natural prosody in agricultural terminology.
**7.5.3 Universal voice interface.** Every feature has voice access: marketplace search by voice, disease description by voice, forum voice-note posts, AI agronomist voice chat. USSD voice callbacks for feature phone users. Voice as primary input method for low-literacy farmers.

### Chapter 8: Flutter Frontend Architecture — Clean Architecture, Modern UI (~1,000 words, 2 tables, 2 diagrams)
#### 8.1 Architecture Patterns
**8.1.1 Clean Architecture layers.** Presentation layer (BLoC pattern with flutter_bloc 8.x), Domain layer (entities, use cases, repository interfaces), Data layer (repository implementations, API clients, local database). Dependency injection with GetIt, unidirectional data flow.
**8.1.2 State management with BLoC.** Feature-based BLoC organization, event-driven state transitions, Stream-based reactive UI, offline state handling, error state normalization, loading shimmer states with Material 3.
**8.1.3 Offline-first data layer.** Drift (type-safe SQLite) for relational offline data, Hive for structured caching (products, weather, user profile), custom SyncEngine with outbox pattern, delta sync API (`/sync?since=timestamp`), CRDT conflict resolution for collaborative features, background sync via WorkManager.

#### 8.2 Modern UI Implementation
**8.2.1 Material 3 (You) design system.** Dynamic theming with seed color generation, glassmorphism cards, dark mode as default, predictive back gestures, CarouselView for marketplace product browsing, TreeView for forum category navigation, shimmer loaders for skeleton screens.
**8.2.2 Adaptive layouts.** Responsive design for 4-7 inch screens (primary target), tablet-optimized layouts for agrodealer dashboards, accessibility: minimum 16dp touch targets, high contrast mode, screen reader support, large text scaling.
**8.2.3 Performance optimization.** Impeller rendering engine (reduced shader jank, smoother animations), image caching with progressive loading, lazy loading for forum threads and product lists, deferred loading for heavy widgets, APK < 30MB target, per-ABI APK splitting.

#### 8.3 Module-Specific Frontend Patterns
**8.3.1 Marketplace UI patterns.** Product grid with faceted filters, cart with swipe actions, order tracking timeline, vendor store pages with ratings, search with Swahili autocomplete and voice input.
**8.3.2 Disease scanner UX flow.** Camera capture with real-time overlay → scanning animation → results screen with confidence visualization → treatment recommendations → product linking → save to history.
**8.3.3 Services booking flow.** Service category browser → provider listing with map view → profile with reviews and availability → booking calendar → payment with escrow confirmation → booking management dashboard.
**8.3.4 Forum and community UI.** Thread list with upvotes, rich text editor with voice note attachment, image gallery in posts, expert badge indicators, regional sub-forum tabs, AI-suggested similar questions.

### Chapter 9: Module Deep-Dives (~2,800 words, 6 tables, 6 diagrams)
> *Standout section — each module receives architectural-level treatment with data models, state machines, and integration points.*

#### 9.1 Agrodealer Marketplace
**9.1.1 Marketplace data model.** Products (with variants, JSONB specifications), vendors (agrodealers with KYC/TFRA verification), categories (country-specific taxonomy), inventory (stock levels, reorder points), orders (with state machine), cart (guest + authenticated), reviews (verified purchase badge).
**9.1.2 Order state machine.** Cart → Checkout (escrow hold) → Payment Confirmed → Processing → Shipped (GPS tracking) → Delivered → Released from Escrow → Completed. Cancellation flows at each state with automatic refund rules.
**9.1.3 Inventory and catalog management.** Product CRUD for agrodealers, bulk import via CSV/Excel, inventory alerts, seasonal pricing (e.g., pre-planting discounts), product image optimization (WebP, responsive sizes via Spatie Media Library).
**9.1.4 Commission and monetization.** Automated commission calculation: 3-5% per marketplace transaction. Disbursement to agrodealer wallet after escrow release. Monthly payout scheduling, commission report generation, transparent fee display.

#### 9.2 Plant Disease Scanner — Hero Feature
**9.2.1 Scanner architecture deep-dive.** Flutter camera plugin → image capture → automatic compression (<=2MB, progressive JPEG) → TF Lite inference engine (NNAPI delegate) → post-processing (NMS, confidence thresholding) → result presentation → offline storage → background cloud sync.
**9.2.2 On-device model management.** Model download on first use (2.5MB base + 5MB per crop-specific extension), model versioning with A/B testing, model hot-swap capability, fallback to cloud if local model version outdated, model size budget: < 15MB total on-device.
**9.2.3 Cloud fallback and active learning.** Confidence threshold gate: >70% = local result displayed, 50-70% = Gemini Vision second opinion requested, <50% = direct Gemini Vision + human agronomist queue. Farmer feedback (correct/incorrect) feeds active learning pipeline for model improvement.
**9.2.4 Disease-to-marketplace integration.** Each diagnosis automatically links to recommended treatment products in the marketplace (verified agrodealer listings). Severity-based urgency indicators. Regional outbreak aggregation for epidemic early warning.
**9.2.5 Scanner as farmer acquisition channel.** Free diagnosis as entry point → account creation prompt → saved diagnosis history → personalized crop recommendations → marketplace engagement → community participation. Analytics funnel tracking conversion from scan to active user.

#### 9.3 Farmers Forum & Community
**9.3.1 Forum data model.** Categories (country-specific, crop-specific), threads (with tags, view counts), posts (rich text, image attachments, voice notes), votes (upvote/downvote, CRDT-based), user badges (expert verified, top contributor, early adopter), moderation flags.
**9.3.2 Expert verification system.** Tiered badges: `verified_agronomist` (professional cert), `verified_veterinary` (TVB registration), `verified_agrodealer` (TFRA license), `top_contributor` (reputation threshold). Green checkmark with hover detail.
**9.3.3 AI-powered community assistance.** RAG-based "Similar Questions" suggestion before posting new thread. AI-generated FAQ from high-engagement threads. Automated content moderation with confidence scoring. Voice-note posts transcribed and indexed for search.
**9.3.4 Regional sub-forums and localization.** Country-specific forums (Tanzania, Kenya, Uganda, Rwanda) with local language support. Crop-specific sub-forums (Coffee Corner, Maize Masters, Banana Board). Seasonal topic auto-generation from crop calendar.

#### 9.4 Agronomist Services
**9.4.1 Service data model.** Provider profiles (specializations, certifications, coverage areas, availability), services offered (consultation types, pricing tiers), bookings (with calendar integration), consultations (chat, video, image sharing), ratings and reviews.
**9.4.2 Booking and scheduling engine.** Real-time availability calendar, appointment booking with deposit (20% via escrow), in-app consultation room (WebRTC for video, typed chat, image sharing), consultation notes and recommendations, follow-up appointment suggestions.
**9.4.3 Consultation delivery.** Pre-consultation farm profile sharing (crop types, soil data, past diagnoses), real-time image sharing during video call, AI-generated consultation summary, prescription/recommendation document generation, post-consultation rating and review.
**9.4.4 Commission structure.** 15% platform commission on agronomist bookings. Tiered commission reduction for high-volume providers (>50 consultations/month = 12%, >100 = 10%). Transparent fee breakdown visible to both parties.

#### 9.5 Logistics & Transport Services
**9.5.1 Logistics service model.** Vehicle types (boda boda pickup, tuk-tuk, pickup truck, lorry, refrigerated truck), route management (pickup → waypoints → delivery), fare estimation engine (distance + vehicle type + cargo weight + fuel surcharge), driver verification and ratings.
**9.5.2 Real-time tracking architecture.** GPS location streaming from driver device → Mapbox Map Matching API → route visualization on farmer app → ETA calculation → delivery confirmation with photo proof. Geofenced alerts for pickup/delivery proximity.
**9.5.3 Cross-border logistics.** EAC Common Market Protocol compliance, customs documentation generation, cross-border fare calculation with border wait-time estimates, bilingual driver instructions (Swahili/English), transporter vetting with cross-border permits.
**9.5.4 Commission and provider management.** 10% platform commission on logistics bookings. Driver onboarding (ID, license, vehicle inspection, community reference), performance scoring (on-time rate, completion rate, rating), incentive bonuses for high performers, dispute resolution workflow.

#### 9.6 Warehouse Services
**9.6.1 Warehouse data model.** Facilities (location, type: grain store/cold storage/silo, capacity, amenities), availability calendar (seasonal pricing: harvest season premium), bookings (duration, quantity, crop type), IoT integration (temperature, humidity monitoring), quality grading records.
**9.6.2 Booking and monitoring.** Search by location, capacity, crop type compatibility, price comparison, booking with mobile money deposit, IoT dashboard for booked space (real-time temperature/humidity alerts), warehouse receipt generation (WRS Act 2005 compliance in Tanzania).
**9.6.3 IoT integration.** Temperature/humidity sensor data ingestion via MQTT, alert thresholds per crop type (maize: <13% moisture, potatoes: 4-7°C), automated SMS/app alerts for threshold breaches, historical data for insurance claims, SiloSense-style blockchain receipts.
**9.6.4 Commission structure.** 5% platform commission on warehouse bookings. Subscription model for warehouse operators (premium listing, analytics dashboard). Insurance integration for stored goods (3-7% of input value).

#### 9.7 Veterinary Services
**9.7.1 Veterinary service model.** Provider profiles (TVB registration, specializations: large animal/poultry/dairy, coverage area, emergency availability), services (consultation, farm visit, vaccination, emergency), livestock health records integration, emergency call feature.
**9.7.2 Tele-veterinary consultation.** Image/video-based remote diagnosis, symptom checklist with AI-assisted triage, prescription generation for over-the-counter treatments, referral to physical visit when needed, vaccination schedule management and reminders.
**9.7.3 Livestock management integration.** Animal inventory tracking (species, breed, age, health history), vaccination schedule per animal, breeding records and lineage tracking, health event timeline, integration with veterinary consultations for contextual advice.
**9.7.4 Emergency response system.** Emergency call button with GPS location sharing, nearest available veterinary officer dispatch, estimated arrival time, pre-arrival first aid instructions, post-emergency follow-up scheduling. Target: <30 minute emergency response time.

#### 9.8 Soil Testing Services
**9.8.1 Three-tier soil testing architecture.** Tier 1: Instant AI analysis from iSDAsoil API (30m resolution, 14 variables, free). Tier 2: Physical sample collection request (lab pickup from farm). Tier 3: Lab-processed comprehensive analysis with precision recommendations.
**9.8.2 Sample collection workflow.** Farmer requests soil test → nearest certified lab assigned → pickup scheduled → sample collection with GPS-tagged location → lab processing (72-hour SLA) → digital results delivery → AI fertilizer recommendation generated.
**9.8.3 Results and recommendations delivery.** 14-variable nutrient breakdown visualization (radar chart), pH interpretation with crop suitability, AI-generated fertilizer blend recommendation (NPK ratios, application rates, timing), historical soil tracking per farm plot, integration with AI Agronomist for holistic crop advice.
**9.8.4 Lab partner integration.** Lab onboarding (certification verification), sample tracking dashboard, digital results upload portal, quality assurance scoring, turnaround time monitoring, automated farmer notification on results ready.

### Chapter 10: API Design — RESTful, Standards, Versioning (~700 words, 2 tables, 1 diagram)
#### 10.1 API Standards & Conventions
**10.1.1 JSON:API specification compliance.** First-party JSON:API resources in Laravel 13: sparse fieldsets (`?fields[post]=title,body`), compound documents (`?include=author,comments`), cursor-based pagination, standard error objects, content negotiation.
**10.1.2 API versioning strategy.** URL path versioning (`/v1/`, `/v2/`), deprecation headers with sunset dates, 6-month transition window, backward compatibility guarantees within major versions, version negotiation via Accept header.
**10.1.3 Request/response patterns.** Standard envelope structure, HTTP status code usage, idempotency keys for mutation endpoints, request validation with detailed error messages, Brotli/gzip compression for JSON payloads.

#### 10.2 Endpoint Organization
**10.2.1 Domain-based endpoint structure.** `/v1/auth/*`, `/v1/marketplace/*`, `/v1/forum/*`, `/v1/scanner/*`, `/v1/services/*`, `/v1/ai/*`, `/v1/payments/*`, `/v1/notifications/*`, `/v1/admin/*`.
**10.2.2 Mobile-optimized endpoints.** BFF pattern: reduced payload sizes, mobile-specific field inclusion, delta sync endpoint (`/v1/sync?since=timestamp`), batch request endpoint for offline sync, field selection for bandwidth optimization.
**10.2.3 Authentication & authorization.** Laravel Sanctum token-based auth, token refresh mechanism, biometric login flow, device fingerprinting for SIM swap detection, rate limiting per user tier (100 req/min standard, 500 req/min premium).

#### 10.3 Developer Experience
**10.3.1 OpenAPI 3.1 specification.** Auto-generated from Laravel resources, interactive Swagger UI explorer, SDK generation for Flutter and other clients, webhook documentation, rate limit visibility in response headers.
**10.3.2 Webhook system.** Event subscriptions (order updates, payment confirmations, delivery status), HMAC signature verification, retry with exponential backoff, event idempotency, webhook delivery logs.

### Chapter 11: Payment & Financial Architecture — Mobile Money, Escrow, Insurance (~1,300 words, 4 tables, 3 diagrams)
#### 11.1 Mobile Money Integration
**11.1.1 Unified payment gateway router.** Provider-agnostic abstraction layer: PaymentGatewayRouter resolves provider per country-code. Tanzania: M-Pesa, Tigo Pesa, Airtel Money, HaloPesa, Mixx by YAS, AzamPesa (via ClickPesa aggregator). Kenya: M-Pesa Daraja 3.0 (12K TPS), Airtel Money. Uganda: MTN MoMo Open API, Airtel Money. Rwanda: MTN MoMo, Airtel Money, IremboPay.
**11.1.2 Mobile money API implementation.** M-Pesa Daraja 3.0 STK Push integration (OAuth 2.0, callback handling, idempotency). MTN MoMo Open API (sandbox → KYC → production ~10 days). Provider fallback: automatic retry with alternate provider if primary fails.
**11.1.3 Cross-border payment architecture.** Phase 1: single-country transactions only. Phase 2: Onafriq integration (1B wallets, 400K agents) for cross-border agricultural trade. Currency conversion with admin-configurable exchange rates. EAC harmonization readiness.

#### 11.2 Wallet & Escrow System
**11.2.1 Sub-wallet architecture.** Main Wallet (deposits, withdrawals, transfers), Escrow Wallet (holds funds during transactions), Savings Wallet (earmarked funds), Insurance Wallet (premium reserves). Per-country wallet isolation for regulatory compliance.
**11.2.2 Escrow transaction flow.** Buyer pays → funds held in Escrow Wallet → seller fulfills order → delivery confirmed (GPS + photo proof) → funds released to seller wallet → dispute window closes → transaction finalized. Automatic release after 48 hours if no dispute.
**11.2.3 Regulatory compliance.** Segregated trust account per country regulations (BoT Tanzania, CBK Kenya, BoU Uganda, BNR Rwanda). Escrow fee: 1-1.5% per transaction. Withdrawal to mobile money with pass-through MNO fees. Quarterly regulatory reporting.

#### 11.3 Commission & Monetization Engine
**11.3.1 Automated commission calculation.** Marketplace: 3-5% per transaction. Logistics: 10% per booking. Warehouse: 5% per booking. Agronomist: 15% per consultation (tiered down to 10% for high volume). Veterinary: 12% per consultation. Soil Testing: 8% per test.
**11.3.2 Disbursement workflow.** Commission deducted at transaction time, credited to platform revenue wallet, agrodealer/service provider receives net amount, monthly settlement reports, automated tax invoice generation per country requirements.

#### 11.4 Insurance Integration
**11.4.1 Agricultural insurance partnerships.** Index-based crop insurance (weather triggers), input insurance (seed/fertilizer protection), livestock insurance (mortality coverage), warehouse insurance (stored goods protection). Premium: 3-7% of input value (benchmarked to Pula: $126M premiums, $92M claims).
**11.4.2 Insurance workflow.** Farmer selects insurance at checkout → premium calculated → premium deducted from wallet → policy issued digitally → claim filing via app (photo evidence, GPS location) → automated claim processing for index-based policies.

### Chapter 12: Real-Time, Logistics & Maps (~900 words, 2 tables, 2 diagrams)
#### 12.1 Real-Time Communication Architecture
**12.1.1 Laravel Reverb WebSocket server.** 90% cost reduction vs. Pusher, first-party integration, event broadcasting for: order status updates, delivery tracking, chat messages, appointment notifications, pest outbreak alerts. Triple-redundant delivery: WebSocket → FCM push → SMS fallback.
**12.1.2 Push notification system.** Firebase Cloud Messaging for in-app notifications, topic-based subscription (weather alerts, pest warnings, order updates), rich notifications with action buttons, quiet hours respecting local time zones, A/B testing framework.
**12.1.3 Background location and geofencing.** GPS tracking for logistics drivers (<5% battery/hour target), geofenced alerts for pickup/delivery proximity, pest alert geofencing (disease outbreak within 50km radius), farm boundary mapping with PostGIS.

#### 12.2 Maps & Routing
**12.2.1 Mapbox integration.** Primary maps provider: Mapbox (cost-optimized, ~$2,325/mo at 10K users vs. ~$4,300 for Google Maps). Features: custom markers for farms/warehouses/service providers, route visualization, offline map tile caching for rural areas, 300ms debounced place search.
**12.2.2 Route optimization.** OSRM self-hosted fallback for routing, multi-stop route planning for logistics providers, estimated fare calculation (distance + vehicle type + cargo weight + fuel surcharge), real-time traffic adjustment where available.
**12.2.3 GPS tracking dashboard.** Live driver location on interactive map, ETA calculation with traffic consideration, delivery proof (photo + GPS coordinates + timestamp), route history and playback, performance analytics (on-time delivery rate, average speed).

### Chapter 13: Security, Compliance & Data Sovereignty (~900 words, 2 tables, 2 diagrams)
#### 13.1 Security Architecture
**13.1.1 Authentication hardening.** Passkey/WebAuthn (Laravel 13 native) eliminating password phishing, device fingerprinting for SIM swap detection (43% of attacks), biometric + PIN binding for 72% fraud reduction, certificate pinning, root/jailbreak detection.
**13.1.2 Data protection.** AES-256 encryption for KYC documents at rest, TLS 1.3 in transit, PII hashing in logs, immutable audit trail for financial transactions, secure token storage via Keystore/Keychain, automatic token refresh.
**13.1.3 Threat mitigation matrix.** Social engineering (58-72% of fraud) → passkey auth. SIM swap (43%) → device fingerprinting + carrier number-lock. Agent-assisted fraud (38%) → biometric + PIN + device binding. Fake payment notifications → API callback verification (not SMS). Mobile malware → certificate pinning + root detection.

#### 13.2 Regulatory Compliance
**13.2.1 Data protection regulations.** Tanzania PDPA 2022 (effective May 2023, data localization required), Kenya Data Protection Act 2019 + VASPA 2025, Uganda Data Protection and Privacy Act 2019, Rwanda Law 058/2021. Per-country consent management flows.
**13.2.2 Agricultural regulatory compliance.** TFRA verification for Tanzania agrodealers, KEPHIS seed certification for Kenya, PCPB pest control product registration, UNADA agrodealer licensing for Uganda. Veterinary registration via respective national boards (TVB, KVB).
**13.2.3 Financial regulatory compliance.** Non-bank PSP licensing path per country (BoT Tanzania: 131 licensed, encouraging new entrants; CBK Kenya: sandbox available; BoU Uganda: partner with licensed financial institution; BNR Rwanda: leverage eKash standards). Escrow trust account requirements.

#### 13.3 Data Sovereignty Architecture
**13.3.1 Regional data residency.** AWS af-south-1 (Cape Town) primary with <20ms Nairobi Local Zone future, per-country database isolation via RLS, edge caching in Cloudflare Africa PoPs (Lagos, Nairobi, Johannesburg), encryption keys managed in-region.
**13.3.2 Government data sharing.** Aggregated anonymized disease outbreak data for TARI/KALRO/NARO/RAB, monthly agricultural trend reports, extension service impact metrics, API access for government dashboards. Data sovereignty as competitive moat against foreign platforms.

### Chapter 14: DevOps, Deployment & Scaling (~800 words, 2 tables, 2 diagrams)
#### 14.1 CI/CD Pipeline
**14.1.1 GitHub Actions workflow.** Automated testing (PHPUnit + Flutter tests), code quality checks (PHPStan, Dart analyze), SAST/DAST scanning (Snyk, SonarQube), Docker image builds, Firebase App Distribution for beta releases, Codemagic for iOS builds.
**14.1.2 Deployment strategy.** Blue-green deployment with FrankenPHP zero-downtime restarts, database migration running before app switch, feature flags for gradual rollout, canary deployment for high-risk changes, automatic rollback on health check failure.

#### 14.2 Infrastructure & Scaling
**14.2.1 Cloud architecture.** AWS ECS Fargate or Google Cloud Run for containerized microservices, auto-scaling 0-100 instances based on demand (critical for agricultural seasonality), serverless containers for pay-per-use efficiency, PostgreSQL RDS with read replicas.
**14.2.2 Scaling strategy.** Horizontal pod autoscaling based on CPU/memory/request rate, database read replicas for reporting queries, Redis cluster for cache and queues, CDN for static assets and images, database connection pooling via PgBouncer.

#### 14.3 Monitoring & Observability
**14.3.1 Application monitoring.** Laravel Pulse for slow queries/queue throughput/cache hit rates, Sentry for error tracking, Firebase Crashlytics for mobile crashes, Laravel Horizon for queue monitoring, Prometheus + Grafana for infrastructure metrics.
**14.3.2 Health checks and alerting.** FrankenPHP built-in health checks, uptime monitoring (99.9% SLA), P95 API latency alerts (>500ms threshold), error rate alerts, disk space and memory alerts, on-call rotation via PagerDuty.
**14.3.3 Disaster recovery.** Point-in-time recovery (PITR) for PostgreSQL with 24-hour RPO, automated daily backups with 30-day retention, cross-region replication for critical data, quarterly DR drills, backup verification testing.

### Chapter 15: Development Roadmap & Milestones (~700 words, 1 table, 1 diagram)
#### 15.1 Phase 1 — Tanzania MVP (Months 1–4)
**15.1.1 Core platform foundation.** User authentication (phone OTP + biometric), KYC verification framework, RBAC system, multi-tenancy (Tanzania only), PostgreSQL + pgvector setup, Meilisearch integration, basic admin dashboard.
**15.1.2 MVP feature set.** Agrodealer Marketplace (product listing, search, cart, M-Pesa/Tigo Pesa checkout, escrow), Plant Disease Scanner (TF Lite offline + Gemini fallback, 10 priority diseases), Farmers Forum (threads, posts, basic moderation), AI Agronomist (RAG with TARI knowledge base, text chat).
**15.1.3 MVP launch targets.** 10,000 registered users, 100 verified agrodealers, 5,000 disease scans, 500 daily forum posts, 99.5% uptime, <2 second app launch time on Tecno Spark 10.

#### 15.2 Phase 2 — Services & Kenya Expansion (Months 5–8)
**15.2.1 Services marketplace launch.** Agronomist booking, Logistics & Transport (boda-to-truck), Warehouse booking, Veterinary services, Soil Testing (3-tier). Unified booking/payment/review infrastructure.
**15.2.2 Kenya market entry.** M-Pesa Daraja 3.0 integration, KEPHIS/PCPB compliance, KALRO knowledge base integration, localized crop profiles (tea, coffee, horticulture), Swahili/English bilingual support.
**15.2.3 Voice AI and offline enhancement.** Voice Service Layer (STT Whisper + TTS Google Cloud), voice-first UI for key flows, offline-first full implementation (72-hour capability), PWA launch.

#### 15.3 Phase 3 — Uganda, Rwanda & Advanced AI (Months 9–12)
**15.3.1 Uganda and Rwanda expansion.** MTN MoMo integration (both markets), UNADA/NARO/RAB partnerships, French language support (Rwanda), Luganda support (Uganda), country-specific crop disease models.
**15.3.2 Advanced AI features.** Fine-tuned agricultural LLM (Mistral-7B QLoRA), expanded disease scanner (20 diseases), satellite crop monitoring (Sentinel-2 NDVI), cooperative/SACCO management module, analytics dashboard for government partners.

#### 15.4 Phase 4 — Scale & EAC Integration (Months 13–18)
**15.4.1 Cross-border EAC trade.** Onafriq cross-border payments, customs documentation generation, cross-border logistics booking, EAC Common Market Protocol compliance, regional disease surveillance network.
**15.4.2 Scale targets.** 100,000 MAU, 500 verified agrodealers, 500 active service providers, 50,000 monthly disease scans, $500K monthly GMV, 15,000+ SACCO members on platform, 99.9% uptime.

---

## Word Count Allocation

| Chapter | Title | Words | % | Tables | Diagrams |
|---------|-------|-------|---|--------|----------|
| 1 | Executive Summary & Vision | 900 | 5.4% | 2 | 2 |
| 2 | East African Context | 1,100 | 6.5% | 3 | 1 |
| 3 | Platform Overview | 1,200 | 7.1% | 2 | 2 |
| 4 | System Architecture | 1,500 | 8.9% | 2 | 3 |
| 5 | Laravel Backend Architecture | 1,300 | 7.7% | 2 | 2 |
| 6 | Database Architecture | 1,000 | 6.0% | 3 | 2 |
| 7 | AI/ML Integration | 2,000 | 11.9% | 5 | 5 |
| 8 | Flutter Frontend Architecture | 1,000 | 6.0% | 2 | 2 |
| 9 | Module Deep-Dives | 2,800 | 16.7% | 6 | 6 |
| 10 | API Design | 700 | 4.2% | 2 | 1 |
| 11 | Payment & Financial Architecture | 1,300 | 7.7% | 4 | 3 |
| 12 | Real-Time, Logistics & Maps | 900 | 5.4% | 2 | 2 |
| 13 | Security, Compliance & Data Sovereignty | 900 | 5.4% | 2 | 2 |
| 14 | DevOps, Deployment & Scaling | 800 | 4.8% | 2 | 2 |
| 15 | Development Roadmap & Milestones | 700 | 4.2% | 1 | 1 |
| **Total** | | **16,800** | **100%** | **40** | **36** |

### Weight Distribution Rationale
- **Standout sections (Chapter 7 + 9)**: 28.6% of total word count. AI/ML gets 11.9% as the technical differentiator. Module Deep-Dives gets 16.7% as the most comprehensive feature coverage.
- **Foundation sections (Chapters 4 + 5 + 6)**: 22.6% for architecture, backend, and database — the structural backbone.
- **Context sections (Chapters 1 + 2 + 3)**: 19.0% for setting the stage with East African specificity.
- **Specialized sections (Chapters 10-14)**: 27.3% for API, payments, real-time, security, and DevOps.
- **Roadmap (Chapter 15)**: 4.2% for forward-looking closure.

---

## Key Tables by Chapter

| Chapter | Table Title | Content Description |
|---------|-------------|-------------------|
| 1 | Ecosystem Statistics Dashboard | Key adoption stats: mobile penetration, agriculture GDP share, post-harvest losses, extension ratios per country |
| 1 | Platform Comparison Matrix | 12+ competitors (DigiFarm, FarmerChat, Apollo, Twiga, etc.) vs. MkulimaForum across features, scale, countries |
| 2 | Digital Adoption & Connectivity Matrix | TZ/KE/UG/RW: smartphone penetration, internet use, gender gap, feature phone dominance |
| 2 | Agriculture Sector Statistics | EAC agriculture GDP share, market sizes, post-harvest losses, cold chain penetration, extension ratios |
| 2 | Competitive Landscape Analysis | Platform types, scales, countries, key strengths, key weaknesses, MkulimaForum positioning |
| 3 | Multi-Country Parameter Matrix | TZ/KE/UG/RW: languages, currencies, mobile money rails, regulators, key crops, research partners |
| 3 | RBAC Permission Matrix | 8 roles × module permissions × CRUD operations across all MkulimaForum modules |
| 4 | Technology Stack Summary | Backend/Mobile/Web/USSD/Infra/Security components with technology, version, justification |
| 4 | Microservices Communication Matrix | Service-to-service communication patterns: sync, async, WebSocket, event-driven |
| 5 | Package Ecosystem Reference | Key Laravel packages: Spatie Permission, Media Library, Multitenancy, Scout, Horizon — versions, purposes |
| 5 | Testing Strategy Matrix | Test types (unit, integration, contract, E2E), tools, coverage targets, execution frequency |
| 6 | Vector Database Benchmark | pgvector vs Qdrant vs Weaviate: QPS, latency, cost at 50M vectors |
| 6 | Core Schema Entity Reference | Key tables across 7 domains with primary keys, relationships, partitioning strategy |
| 6 | RAG Knowledge Base Schema | `knowledge_chunks` table structure, embedding dimensions, indexing strategy |
| 7 | LLM Cost Comparison | 5 models (Gemini 2.0 Flash, GPT-4o, Claude 3.5 Sonnet, GPT-4o-mini, Llama 3) × cost/input/output/context/Swahili quality |
| 7 | Disease Detection Model Comparison | 6 models (MobileNetV3-S/L, DenseNet201, PlantVillage Nuru, Gemini Vision) × size/accuracy/offline/platform |
| 7 | Voice AI Service Comparison | 5 services (Whisper Small/Tiny, Google Cloud Speech, Azure Speech) × STT/TTS/offline/WER/cost |
| 7 | Soil Variable Accuracy Table | 14 soil variables × iSDAsoil accuracy × XGBoost prediction accuracy × use case |
| 7 | AI Operational Cost Projection | Monthly costs at 50K queries/month: LLM, vector DB, STT, TTS, satellite data |
| 8 | Offline-First Sync Architecture | SyncEngine components: OutboxService, PushService, PullService, ConflictService |
| 8 | Device Tier Specifications | Screen sizes, RAM targets, APK size budgets, performance targets per device tier |
| 9 | Marketplace Order State Machine | All states, transitions, cancellation rules, automatic refund triggers |
| 9 | Disease Scanner Crop-Disease Coverage | 20 East African diseases × affected crops × geographic prevalence × TF Lite vs. cloud classification |
| 9 | Service Provider Vetting Tiers | 4 tiers (individual, agro-dealer, warehouse operator, professional) × requirements × verification process |
| 9 | Services Commission Structure | 6 service categories × commission rate × tiered discounts × revenue model |
| 9 | Logistics Vehicle Type Matrix | Vehicle types (boda to truck) × capacity × pricing model × verification requirements |
| 9 | Warehouse IoT Threshold Reference | Crop types × optimal temperature × humidity thresholds × alert levels |
| 10 | API Endpoint Reference (selected) | Key endpoints per domain with method, path, auth requirement, rate limit |
| 10 | JSON:API Feature Matrix | Sparse fieldsets, compound documents, cursor pagination, error format per endpoint category |
| 11 | Mobile Money API Comparison | 4 countries × provider × API status × integration complexity × fee structure |
| 11 | Regulatory Requirements by Country | 4 countries × data protection law × financial licensing × KYC requirements × cross-border rules |
| 11 | Recommended Fee Structure | 6 services × platform fee × benchmark comparison × revenue projection |
| 11 | Escrow State Machine | Transaction states: pending → held → released/refunded → finalized, with timing rules |
| 12 | Mapping API Cost Comparison | Mapbox vs. Google vs. OSM: features, 10K-user monthly cost, Africa coverage, offline capability |
| 12 | Real-Time Delivery Matrix | WebSocket → FCM → SMS triple-redundant delivery with latency and reliability targets |
| 13 | Threat Matrix + Countermeasures | 6 threat types × attack prevalence × defense mechanism × effectiveness data |
| 13 | Regulatory Compliance by Country | 4 countries × data protection × agricultural regulation × financial licensing × compliance status |
| 14 | CI/CD Pipeline Stages | Build → Test → Security Scan → Deploy → Monitor stages with tools and gates |
| 14 | Infrastructure Cost Projection | Monthly costs at 10K/50K/100K users: compute, database, CDN, monitoring, third-party APIs |
| 15 | Development Phase Timeline | 4 phases × duration × key deliverables × success criteria × dependencies |

---

## Key Diagrams by Chapter

| Chapter | Diagram | Type | Priority |
|---------|---------|------|----------|
| 1 | MkulimaForum Ecosystem Map | Conceptual (platform + actors + flows) | High |
| 1 | East African Agritech Competitive Landscape | Positioning matrix (feature × scale) | High |
| 2 | Country-Specific Context Map | Geographic infographic (4 countries × key stats) | Medium |
| 3 | MkulimaForum Module Interaction Map | System diagram (5 pillars + data flows) | High |
| 3 | User Type & RBAC Hierarchy | Role hierarchy diagram | Medium |
| 4 | C4 Context Diagram | C4 model (Level 1 — system context) | High |
| 4 | C4 Container Diagram | C4 model (Level 2 — containers) | High |
| 4 | Multi-Tenant Data Flow | Data flow diagram (country-scoped requests) | High |
| 5 | Domain-Driven Design Bounded Contexts | Domain diagram (7 bounded contexts + relationships) | High |
| 5 | Laravel 13.x Request Lifecycle | Sequence diagram (FrankenPHP → Laravel → Response) | Medium |
| 6 | Multi-Tenancy RLS Enforcement | ER diagram (tenant isolation via RLS) | Medium |
| 6 | pgvector RAG Query Flow | Data flow (query → embedding → similarity → result) | High |
| 7 | MkulimaForum AI Stack Architecture | System diagram (edge → application → cloud layers) | High |
| 7 | RAG Pipeline Flow | Data flow (intent → retrieval → rerank → generation → citation) | High |
| 7 | Disease Scanner Hybrid Architecture | Decision tree (TF Lite → Gemini Vision → Human Review) | High |
| 7 | Voice Service Layer (VSL) Architecture | System diagram (Audio → STT → LLM → TTS → Audio) | High |
| 7 | Soil Analysis Tiered Architecture | Component diagram (3-tier: iSDAsoil → sample → lab) | Medium |
| 7 | Knowledge Ingestion Pipeline | Data flow (TARI PDFs → chunks → embeddings → pgvector) | Medium |
| 8 | Offline-First Sync Architecture | Component diagram (Drift → SyncEngine → REST API) | High |
| 8 | Flutter Clean Architecture Layers | Layered architecture diagram (Presentation/Domain/Data) | High |
| 9 | Marketplace Order State Machine | State machine diagram (all states and transitions) | High |
| 9 | Disease Scanner UX Flow | User journey diagram (capture → scan → results → action) | High |
| 9 | Service Marketplace Booking Flow | State machine/flowchart (search → book → pay → complete) | High |
| 9 | Provider Vetting Process | Flowchart (4-tier verification pipeline) | Medium |
| 9 | Logistics Tracking Architecture | System diagram (GPS → Mapbox → farmer dashboard) | Medium |
| 9 | Veterinary Emergency Response Flow | Sequence diagram (emergency → dispatch → arrival → follow-up) | Medium |
| 10 | API Architecture Overview | Component diagram (Gateway → Services → Database) | Medium |
| 10 | Authentication Flow | Sequence diagram (Passkey + PIN + Biometric) | Medium |
| 11 | Payment Flow (buyer → escrow → seller) | Sequence diagram | High |
| 11 | Unified Payment Layer Architecture | Component diagram (adapters, connectors, router) | High |
| 11 | Mobile Money Integration by Country | Deployment/country-specific diagram | Medium |
| 12 | Real-Time Notification Architecture | Event flow diagram (Event → Reverb → FCM → DB) | Medium |
| 12 | GPS Tracking & Geofencing | System diagram (driver app → location stream → map) | Medium |
| 13 | Threat Model + Defense Layers | Layered defense diagram | Medium |
| 13 | Data Sovereignty Compliance Architecture | Data residency flow (per-country isolation) | Medium |
| 14 | AWS Africa Deployment Architecture | Infrastructure diagram (af-south-1 + CloudFront edges) | Medium |
| 14 | CI/CD Pipeline Flow | Pipeline diagram (GitHub Actions stages) | Medium |
| 15 | Development Roadmap Timeline | Gantt-style timeline (4 phases over 18 months) | High |

---

## Research-to-Chapter Mapping

| Chapter | Primary Dim Files | Key Data Points from Research | Standout Insights Applied |
|---------|------------------|------------------------------|--------------------------|
| 1 | All dimensions + synthesis | $18.42B TZ market, 40% post-harvest loss, 1:1,172 extension ratio, $13B FAW losses | Insight 2 (AI extension officer), Insight 7 (multi-country from day one) |
| 2 | Dim 01 (ecosystem), synthesis §1.1–1.3 | Smartphone 41.8% TZ, 77.5% non-smartphone, 24% vs 35% gender gap, 12 competitor platforms analyzed | Insight 5 (voice-first for 60%+ market), Insight 1 (trust gap) |
| 3 | Dim 01, Dim 04, synthesis §5 | 6 service categories, 4 provider vetting tiers, 8 RBAC roles, crop profiles per country | Insight 8 (service marketplace = ecosystem lock-in), Insight 7 (multi-country) |
| 4 | Dim 05, synthesis §2.1, §6 | Laravel 13, FrankenPHP 5-10x, pgvector 471 QPS, Reverb 90% cost reduction, JSON:API first-party | Insight 7 (country-scoped multi-tenant), Insight 9 (data sovereignty) |
| 5 | Dim 05, synthesis §2.1, §6.2 | Laravel 13 AI SDK, first-party JSON:API, Spatie ecosystem, FrankenPHP, Octane | Insight 9 (data sovereignty = competitive moat) |
| 6 | Dim 03, Dim 05, synthesis §2.1 | pgvector 28ms p95 at 50M vectors, pgvectorscale, PostGIS, JSONB, RLS multi-tenancy | Insight 9 (data sovereignty architecture) |
| 7 | Dim 03, synthesis §3, §6.7, §7.4 | Gemini 2.0 Flash $0.075/1M, MobileNetV3 2.5MB, Whisper ~17% WER, XGBoost 99.09%, iSDAsoil free | Insight 2 (AI replaces 50K+ extension officers), Insight 6 (scanner as acquisition channel), Insight 5 (voice-first) |
| 8 | Dim 05, synthesis §2.1, §6.1 | Flutter 3.24 Impeller, Drift SQLite, BLoC, Material 3, offline-first sync engine | Insight 5 (voice-first UI), Insight 7 (multi-country i18n) |
| 9 | Dim 01, Dim 03, Dim 04, synthesis §5 | iProcure 94% fill rate, Sokofresh 32 rooms, CowTribe 5K farmers, Lori 20K trucks, 20 disease priorities | Insight 1 (trust gap → KYC core), Insight 4 (cold chain = revenue engine), Insight 6 (scanner hero feature), Insight 8 (services lock-in) |
| 10 | Dim 05, synthesis §6.6 | JSON:API first-party, BFF pattern, delta sync, cursor pagination, OpenAPI 3.1 | Insight 7 (multi-country API routing) |
| 11 | Dim 02, synthesis §4, §6.4 | M-Pesa Daraja 3.0 12K TPS, MTN MoMo ~10 day go-live, ClickPesa TZ aggregator, escrow 1-1.5%, Onafriq 1B wallets | Insight 1 (escrow as trust builder), Insight 9 (data sovereignty in payments) |
| 12 | Dim 04, Dim 05, synthesis §5.3, §6.5 | Mapbox $2,325/mo vs Google $4,300, Reverb $5-50/mo, MapLibre OSM fallback, triple-redundant delivery | Insight 7 (multi-country logistics), Insight 10 (USSD bridge) |
| 13 | Dim 02, Dim 05, synthesis §4.3, §6.4 | TZ PDPA 2022, KE DPA 2019, passkey anti-phishing, device fingerprinting 72% fraud reduction, AES-256 TLS 1.3 | Insight 9 (data sovereignty as moat), Insight 1 (trust gap) |
| 14 | Dim 05, synthesis §2.1 | FrankenPHP zero-downtime, CloudRun auto-scaling, Laravel Pulse, AWS af-south-1 45-65ms | Insight 7 (multi-region deployment) |
| 15 | All dimensions, synthesis §7–8 | 18-month timeline, 4-phase rollout, $500K monthly GMV target at scale, 100K MAU goal | Insight 7 (TZ launch → EAC), Insight 10 (USSD as growth engine) |

---

## Structural Flow Narrative

The document follows a **"Why → What → How → Prove → Scale"** narrative arc:

1. **Why (Chapters 1–2)**: The $18.4B opportunity, the 50,000 missing extension officers, the 40% post-harvest losses — the burning problem in East African agriculture.

2. **What (Chapters 3–4)**: MkulimaForum as the answer — five pillars, multi-country from day one, trust-by-design architecture, offline-first for rural farmers.

3. **How (Chapters 5–9)**: The deepest technical dive — Laravel 13 with JSON:API and FrankenPHP, PostgreSQL + pgvector for RAG, the three-layer AI stack (edge + application + cloud), Flutter offline-first with Drift, and module-by-module architectural specifications with the disease scanner, services marketplace, and AI as standout sections.

4. **Prove (Chapters 10–14)**: API standards, mobile money escrow across 4 countries, real-time logistics tracking, security against East African fraud patterns, and DevOps for African cloud deployment.

5. **Scale (Chapter 15)**: 18-month phased roadmap from Tanzania MVP to EAC regional platform, reaching 100K MAU and $500K monthly GMV.

The **three standout sections** (Chapter 7: AI/ML at 11.9%, Chapter 9: Module Deep-Dives at 16.7%, with disease scanner as hero feature within) together represent **28.6% of the document** — ensuring these differentiators receive the architectural depth they deserve.

---

*Structure design prepared for MkulimaForum Architecture Document v1.0*
*Research base: 7 dimension reports, 200+ sources, 50+ searches*
*Confidence: High — all chapters mapped to verified research data and strategic insights*
