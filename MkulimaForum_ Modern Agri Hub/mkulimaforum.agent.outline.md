# MkulimaForum — Software Architecture Document
## Laravel 13 Backend × Flutter 3.24 Frontend | East African Agricultural Super-App Platform

## 1. Executive Summary & Vision (~900 words, 2 tables, 2 diagrams)
### 1.1 The $18.4 Billion Opportunity
#### 1.1.1 East African agriculture contributes 25-40% of EAC Partner States GDP, yet 40% of fresh produce is lost post-harvest — Tanzania alone represents an $18.42B market growing to $24.23B by 2030
#### 1.1.2 Smartphone penetration at 41.8% (TZ), extension officer ratios of 1:1,172 vs FAO standard of 1:400, and $13B annual FAW losses create urgent need for unified digital platform
#### 1.1.3 MkulimaForum vision: comprehensive agricultural super-app as digital backbone for East African smallholder farmers — marketplace, AI diagnostics, expert services, community in one offline-first platform
### 1.2 Platform at a Glance
#### 1.2.1 Five core pillars: Agrodealer Marketplace, Plant Disease Scanner, Farmers Forum, Services Marketplace (6 categories), and AI Agronomist — unified under MkulimaForum brand
#### 1.2.2 Key differentiators: hybrid on-device + cloud AI, voice-first Swahili interface, offline-first architecture, multi-country mobile money escrow, RAG-powered agricultural knowledge system
#### 1.2.3 Target countries and rollout: Tanzania (launch), Kenya, Uganda, Rwanda — country-scoped multi-tenant architecture from day one
### 1.3 Success Metrics & Impact Projections
#### 1.3.1 Platform targets: 50,000 concurrent users at launch scale, 85%+ disease diagnosis accuracy, $1-3/farmer/year AI extension cost (10x reduction vs traditional), 72-hour offline operation
#### 1.3.2 Ecosystem impact: replacing 50,000+ missing extension officers through AI, reducing post-harvest losses via cold chain digitization, cooperative/SACCO integration reaching $2B+ annual transactions

## 2. East African Context — Problem, Stats & Opportunity (~1,100 words, 3 tables, 1 diagram)
### 2.1 The Smallholder Reality
#### 2.1.1 80% of East African farms are rain-fed smallholdings under 2 hectares; 76.5% of farmers own livestock; agriculture employs 43.7% of Rwanda's workforce and 26.2% of Tanzania's GDP
#### 2.1.2 Connectivity divide: 99.3% mobile penetration in Tanzania but only 41.8% smartphone ownership; 77.5% own only feature phones; rural internet use 7.7%; gender gap of 24% women vs 35% men on mobile internet
#### 2.1.3 40% of fresh produce lost post-harvest ($4.5B annual loss in East Africa); only 5% passes through cold chain; cold chain market growing from $12.87B to $18.29B by 2032
### 2.2 Competitive Landscape & Gap Analysis
#### 2.2.1 Existing platforms: DigiFarm (1.6M registered, Kenya-only), FarmerChat (830K+ users, no marketplace), Apollo Agriculture (100K+ farmers, credit-focused), Twiga Foods (130+ tons/day, scaled back), Wefarm (1.8M farmers, declining) — all single-feature or single-country
#### 2.2.2 MkulimaForum positioning: unlike single-feature competitors, unifies marketplace + AI diagnostics + expert services + community + offline-first + voice interface + multi-country — ecosystem lock-in through service marketplace data flywheel
### 2.3 Country-Specific Context
#### 2.3.1 Tanzania launch market: Swahili-dominant, M-Pesa/Tigo Pesa/Airtel Money/HaloPesa/Mixx, TFRA regulation, TARI partnership, crops: maize, cassava, rice, bananas, coffee, cotton
#### 2.3.2 Kenya mature market: M-Pesa dominance (30M+ users, Daraja 3.0 at 12K TPS), PCPB/KEPHIS regulation, KALRO partnership, crops: tea, coffee, maize, horticulture
#### 2.3.3 Uganda and Rwanda expansion: Uganda MTN MoMo dominant, UNADA/MAAIF regulation, NARO partnership, matooke/coffee; Rwanda MTN MoMo/Airtel, BNR/RSB/RAB, coffee/tea/potatoes, PSTA5 digital agriculture vision

## 3. Platform Overview — All MkulimaForum Modules (~1,200 words, 2 tables, 2 diagrams)
### 3.1 System Modules Map
#### 3.1.1 Module interaction overview: marketplace purchases trigger logistics bookings, disease scans recommend marketplace products, soil tests inform agronomist consultations, forum discussions feed RAG knowledge base, all data improves AI recommendations
#### 3.1.2 Eight user roles with granular RBAC: farmer, agrodealer, agronomist, veterinary_officer, logistics_provider, warehouse_operator, admin, extension_officer — permissions per module and country scope
#### 3.1.3 Cross-module data flywheel: soil test data improves crop recommendations, veterinary records improve livestock advice, purchase history personalizes marketplace suggestions, disease reports inform regional pest alerts
### 3.2 Module Specifications
#### 3.2.1 Agrodealer Marketplace: multi-vendor e-commerce for inputs (seeds, fertilizers, pesticides, tools) with TFRA/PCPB/UNADA compliance, search/filtering, escrow checkout, inventory tracking, sales analytics
#### 3.2.2 Plant Disease Scanner: hybrid AI diagnosis with on-device TF Lite MobileNetV3-Small (2.5MB, 20 diseases) + Gemini Vision cloud fallback, severity estimation, treatment product linking, offline capability
#### 3.2.3 Farmers Forum: threaded discussions with rich media, expert verification badges, AI-powered FAQ via RAG, voice-note posts, upvoting/downvoting, localized sub-forums per region, content moderation
#### 3.2.4 AI Agronomist: RAG-powered conversational assistant with TARI/KALRO/NARO/RAB knowledge base, voice I/O in Swahili, crop recommendations combining soil + weather + market prices, fertilizer advice from soil analysis
#### 3.2.5 Services Marketplace with six categories: Agronomist consultation (booking, chat, video), Logistics & Transport (boda-to-truck, GPS tracking), Warehouse (storage search, IoT monitoring), Veterinary (tele-vet, farm visits, vaccination), Soil Testing (3-tier: AI → sample → lab)
### 3.3 Shared Platform Services
#### 3.3.1 Cross-cutting service layer: authentication (phone OTP + biometric), KYC verification, wallet & escrow, push notifications (FCM + SMS fallback), search (Meilisearch), analytics, content moderation
#### 3.3.2 Infrastructure services: weather (Open-Meteo), GPS (Mapbox), image compression, multi-language i18n (English/Swahili/French/Luganda), USSD fallback, PWA support

## 4. System Architecture — High-Level Design (~1,500 words, 2 tables, 3 diagrams)
### 4.1 Architectural Philosophy
#### 4.1.1 Design principles: domain-driven design with bounded contexts, API-first with JSON:API standard, offline-first as default, trust-by-design (KYC + escrow + verification), voice-first for inclusion, multi-country from day one
#### 4.1.2 Technology stack: Laravel 13.x (PHP 8.3+) with first-party JSON:API, FrankenPHP application server, Flutter 3.24+ with Impeller, PostgreSQL 16+ with pgvector, Redis 7.x, Meilisearch, Firebase Cloud Messaging, Cloudflare
#### 4.1.3 C4 Context and Container diagrams: system context showing farmers, agrodealers, service providers, government partners, platform admin; container diagram showing mobile app, web admin, API gateway, microservices, databases, external services
### 4.2 Microservices Architecture
#### 4.2.1 Service decomposition: Auth Service, User Service, Marketplace Service, Disease Scanner Service, Forum Service, Services Marketplace Service, AI Orchestration Service, Payment Service, Notification Service, Analytics Service
#### 4.2.2 Service communication: synchronous REST/JSON:API for user-facing, asynchronous Redis queues + Laravel Horizon for background, WebSockets via Laravel Reverb for real-time, event-driven for cross-service notifications
#### 4.2.3 API Gateway with country-code routing, per-second rate limiting (Laravel 13), auth middleware, request transformation, response caching, BFF pattern for mobile-optimized payloads
### 4.3 Multi-Tenancy & Regional Architecture
#### 4.3.1 Country-scoped tenant isolation: shared PostgreSQL with country_code tenant key, PostgreSQL RLS policies, TenantAwareModel base class with global scope, subdomain routing (tz.mkulimaforum.com)
#### 4.3.2 Data sovereignty: per-country database isolation via RLS, AWS af-south-1 (Cape Town) primary with 45-65ms latency, CloudFront edge caching across East Africa, roadmap for in-country hosting
#### 4.3.3 Regional customization: per-country mobile money configs, regulatory compliance modules, crop disease knowledge bases, seasonal crop calendars, language packs, product catalog scoping

## 5. Laravel Backend Architecture — Domain-Driven Design (~1,300 words, 2 tables, 2 diagrams)
### 5.1 Domain-Driven Design Structure
#### 5.1.1 Seven bounded contexts: Auth (Sanctum 4.x, RBAC), Marketplace (products, orders, inventory), Forum (threads, posts, moderation), Scanner (diagnosis, model management), Services (booking, scheduling, providers), AI (RAG, LLM orchestration), Payment (wallets, escrow, commissions)
#### 5.1.2 Domain layer: entities, value objects, domain events, repository interfaces + Eloquent implementations, domain services, policy classes — clean separation between domain and infrastructure
#### 5.1.3 Application layer: commands, queries (CQRS pattern), handlers, DTOs, service layer coordinating domain objects, application services for cross-domain operations
### 5.2 Laravel 13.x Modern Patterns
#### 5.2.1 First-party JSON:API resources: native sparse fieldsets, compound documents, cursor pagination, relationship inclusion — eliminates third-party package dependency
#### 5.2.2 Laravel Reverb: 90% cost reduction vs Pusher ($1,200/yr to ~$60/yr), 40% lower latency, first-party WebSocket server for live tracking and notifications
#### 5.2.3 FrankenPHP application server: 5-10x throughput over PHP-FPM, HTTP/2 and HTTP/3, zero-downtime deployments with Laravel Octane
#### 5.2.4 Laravel AI SDK: first-party LLM orchestration with provider abstraction (Gemini primary, OpenAI fallback, self-hosted Llama 3 option for data sovereignty)
### 5.3 Code Organization & Quality
#### 5.3.1 Directory structure: app/Domains/{Domain}/{Entity,Repository,Service,Event}/, app/Http/Resources/JsonApi/, database/factories/Domain/, modular service provider registration per domain
#### 5.3.2 Testing strategy: PHPUnit >80% coverage, Pest PHP for expressive syntax, Testcontainers for PostgreSQL integration tests, contract tests for API specifications
#### 5.3.3 Package ecosystem: Spatie Laravel Permission 6.x, Spatie Media Library 11.x, Spatie Multitenancy 4.x, Laravel Scout + Meilisearch, Laravel Horizon

## 6. Database Architecture — PostgreSQL + pgvector + Advanced Schema (~1,000 words, 3 tables, 2 diagrams)
### 6.1 PostgreSQL Schema Design
#### 6.1.1 Core schema: 40+ tables across 7 domains — users, profiles, kyc_verifications, products, orders, order_items, forum_threads, forum_posts, diagnoses, service_bookings, wallets, transactions, notifications, crop_diseases, knowledge_chunks, soil_tests
#### 6.1.2 Multi-tenancy with RLS: country_code column on every tenant-scoped table, PostgreSQL RLS policies enforcing current_setting('app.current_country'), global query scopes in Eloquent, tenant resolution via subdomain or header
#### 6.1.3 Partitioning strategy: monthly range partitioning for orders and transactions, native PostgreSQL 16 partitioning, automated partition creation, read replicas for reporting, PgBouncer connection pooling
### 6.2 Vector Database with pgvector
#### 6.2.1 pgvector extension: vector data type for embeddings, HNSW indexing, pgvectorscale for production, same PostgreSQL instance eliminating separate infrastructure — 471 QPS at 28ms p95 with 50M vectors
#### 6.2.2 RAG knowledge base schema: knowledge_chunks table with content, embedding (vector(1536)), source (TARI/FAO/KEPHIS), metadata (JSONB), country_code — cosine similarity queries with hybrid full-text search
#### 6.2.3 Semantic search: vector similarity + tsvector full-text hybrid, cross-encoder reranking, metadata filtering by country and crop type
### 6.3 Specialized Data Types
#### 6.3.1 PostGIS for geospatial: geometry columns for farm boundaries, warehouse locations, delivery routes, service provider coverage areas; geo-indexing for nearby searches
#### 6.3.2 JSONB for flexible attributes: product specifications, provider availability, forum metadata, diagnosis results, soil test nutrient breakdown — schema-flexible without migrations
#### 6.3.3 Full-text search: combined Meilisearch + tsvector, Swahili language support, typo-tolerant autocomplete, synonym dictionaries for agricultural terms

## 7. AI/ML Integration — The Brain of MkulimaForum (~2,000 words, 5 tables, 5 diagrams)
### 7.1 AI Architecture Overview
#### 7.1.1 Three-layer AI stack: Edge (on-device TF Lite for offline diagnosis), Application (RAG pipeline, recommendation engine, voice processing), Cloud (Gemini 2.0 Flash, OpenAI fallback, self-hosted Llama 3)
#### 7.1.2 AI service topology: Flutter TF Lite → API Gateway → AI Orchestration Service → Vector DB (pgvector) + LLM providers + Model registry; data ingestion from TARI/KALRO/NARO/RAB
#### 7.1.3 Cost optimization: Gemini 2.0 Flash at $0.075/1M tokens ($21/month at 50K queries), pgvector zero additional cost, TF Lite on-device free — total AI cost ~$100-200/month at launch scale
### 7.2 Plant Disease Detection System
#### 7.2.1 Model selection: MobileNetV3-Small (2.54MB, NNAPI) primary, MobileNetV3-Large quantized (2.96MB, 73% Top-1) for higher-end devices, DenseNet201 (30MB, 96%) premium, Gemini Vision cloud fallback (80-90%) for rare diseases
#### 7.2.2 Hybrid inference pipeline: TF Lite offline → top-k predictions → severity estimation → treatment lookup; confidence <70% → Gemini Vision second opinion; <50% → human agronomist queue; active learning from farmer feedback
#### 7.2.3 East African disease coverage: 20 priority diseases — MLN, FAW damage, CBSD, CMD, Banana XW, BBW, Coffee Leaf Rust, Coffee Berry Disease, Rice Blast, Bean Anthracnose, SPVD, Tomato Early Blight, Potato Late Blight, Groundnut Rosette, MSV, Wheat Rust, Cotton Bollworm, Tea Blister Blight, TMV, Sorghum Downy Mildew
#### 7.2.4 Model improvement pipeline: continuous training from farmer-submitted images with expert labels, confidence calibration for field conditions (mitigating 10-40% accuracy drop), A/B testing, quarterly releases
### 7.3 RAG Knowledge System (AI Agronomist)
#### 7.3.1 Knowledge ingestion: TARI PDFs/FAO guidelines/KEPHIS alerts/KALRO research/iSDAsoil data → text extraction → semantic chunking → multilingual embedding → pgvector with metadata tagging
#### 7.3.2 Retrieval and generation: query embedding → pgvector similarity search (top-10) → cross-encoder reranking → context assembly (top-5 chunks) → Gemini 2.0 Flash generation → response validation → citation attribution
#### 7.3.3 Conversational memory: per-user history, farm profile context integration, follow-up handling, multi-turn management, conversation summarization
#### 7.3.4 Fine-tuned agricultural LLM option: Mistral-7B or Llama-3-8B base, QLoRA 4-bit fine-tuning (6GB VRAM on T4), 5K-20K Swahili-English examples, 92% quality of full fine-tuning, self-hosted for data sovereignty
### 7.4 Soil Analysis AI
#### 7.4.1 Fertilizer recommendation engine: XGBoost model (99.09% accuracy) trained on iSDAsoil + lab results, 14 input variables (pH, N, P, K, S, Ca, Mg, B, Cu, Fe, Mn, Zn, clay, organic carbon), crop-specific blends and application rates
#### 7.4.2 iSDAsoil integration: REST API at 30m resolution (free), 14 chemical/physical variables, all sub-Saharan Africa — Tier 1 instant AI, Tier 2 physical sample collection, Tier 3 lab precision analysis
#### 7.4.3 Soil-weather-market correlation: integration of soil analysis, weather forecasts (Open-Meteo), market prices, regional crop calendars for personalized planting recommendations with yield projections
### 7.5 Voice Service Layer (VSL)
#### 7.5.1 Speech-to-text: Whisper Small fine-tuned for Swahili (~17% WER) primary; Whisper Tiny (39MB) for offline (~25-30% WER); Google Cloud Speech API (sw-KE, sw-TZ) fallback; audio preprocessing with noise reduction
#### 7.5.2 Text-to-speech: Google Cloud TTS Swahili voices (Daudi male, Rehema female); Azure Speech Service alternative (sw-KE, sw-TZ); SSML for natural prosody in agricultural terminology
#### 7.5.3 Universal voice interface: every feature has voice access — marketplace search, disease description, forum voice-notes, AI agronomist chat; USSD voice callbacks for feature phones; voice as primary input for low-literacy farmers

## 8. Flutter Frontend Architecture — Clean Architecture, Modern UI (~1,000 words, 2 tables, 2 diagrams)
### 8.1 Architecture Patterns
#### 8.1.1 Clean Architecture layers: Presentation (BLoC with flutter_bloc 8.x), Domain (entities, use cases, repository interfaces), Data (repository implementations, API clients, local database); DI with GetIt, unidirectional data flow
#### 8.1.2 State management with BLoC: feature-based organization, event-driven transitions, Stream-based reactive UI, offline state handling, error normalization, loading shimmer states with Material 3
#### 8.1.3 Offline-first data layer: Drift (type-safe SQLite) for relational data, Hive for structured caching, custom SyncEngine with outbox pattern, delta sync API (/sync?since=timestamp), CRDT conflict resolution, background sync via WorkManager
### 8.2 Modern UI Implementation
#### 8.2.1 Material 3 (You) design system: dynamic theming, glassmorphism cards, dark mode default, predictive back gestures, CarouselView for marketplace, TreeView for forum, shimmer skeleton screens
#### 8.2.2 Adaptive layouts: responsive 4-7 inch screens (primary), tablet for agrodealer dashboards, minimum 16dp touch targets, high contrast mode, screen reader support, large text scaling
#### 8.2.3 Performance: Impeller rendering (reduced shader jank), image progressive loading, lazy loading for lists, deferred heavy widgets, APK <30MB target, per-ABI APK splitting
### 8.3 Module-Specific Frontend Patterns
#### 8.3.1 Marketplace UI: product grid with faceted filters, cart with swipe actions, order tracking timeline, vendor pages with ratings, Swahili autocomplete and voice search
#### 8.3.2 Disease scanner UX: camera capture with real-time overlay → scanning animation → results with confidence visualization → treatment recommendations → product linking → save to history
#### 8.3.3 Services booking flow: category browser → provider listing with map → profile with reviews → booking calendar → escrow payment → booking dashboard
#### 8.3.4 Forum UI: thread list with upvotes, rich text editor with voice note, image gallery, expert badges, regional sub-forum tabs, AI-suggested similar questions

## 9. Module Deep-Dives (~2,800 words, 6 tables, 6 diagrams)
### 9.1 Agrodealer Marketplace
#### 9.1.1 Data model: products (variants, JSONB specs), vendors (KYC/TFRA verified), categories (country taxonomy), inventory (stock, reorder points), orders (state machine), cart (guest + auth), reviews (verified purchase badge)
#### 9.1.2 Order state machine: Cart → Checkout (escrow hold) → Payment Confirmed → Processing → Shipped (GPS) → Delivered → Escrow Released → Completed; cancellation flows with automatic refund rules at each state
#### 9.1.3 Inventory management: product CRUD for dealers, bulk CSV/Excel import, inventory alerts, seasonal pricing (pre-planting discounts), WebP image optimization via Spatie Media Library
#### 9.1.4 Commission: 3-5% per marketplace transaction, disbursement after escrow release, monthly payouts, commission reports, transparent fee display
### 9.2 Plant Disease Scanner — Hero Feature
#### 9.2.1 Scanner architecture: Flutter camera → image capture → compression (<=2MB progressive JPEG) → TF Lite inference (NNAPI) → post-processing (NMS, confidence) → results → offline storage → background cloud sync
#### 9.2.2 On-device model management: download on first use (2.5MB base + 5MB per crop extension), versioning with A/B testing, hot-swap, cloud fallback if outdated, <15MB total on-device budget
#### 9.2.3 Cloud fallback and active learning: >70% = local result; 50-70% = Gemini Vision second opinion; <50% = Gemini Vision + human agronomist queue; farmer feedback (correct/incorrect) feeds active learning
#### 9.2.4 Disease-to-marketplace integration: each diagnosis links to treatment products (verified dealer listings), severity-based urgency, regional outbreak aggregation for epidemic early warning
#### 9.2.5 Scanner as acquisition channel: free diagnosis → account creation → saved history → personalized recommendations → marketplace engagement → community; analytics funnel tracking scan-to-active conversion
### 9.3 Farmers Forum & Community
#### 9.3.1 Data model: categories (country/crop-specific), threads (tags, views), posts (rich text, images, voice notes), votes (CRDT-based), badges (expert verified, top contributor), moderation flags
#### 9.3.2 Expert verification: tiered badges — verified_agronomist (professional cert), verified_veterinary (TVB registration), verified_agrodealer (TFRA license), top_contributor (reputation); green checkmark with detail
#### 9.3.3 AI community assistance: RAG-based "Similar Questions" before posting, AI-generated FAQ from high-engagement threads, automated content moderation, voice-note transcription and search indexing
#### 9.3.4 Regional sub-forums: country forums (TZ, KE, UG, RW) with local languages; crop forums (Coffee Corner, Maize Masters, Banana Board); seasonal auto-generation from crop calendar
### 9.4 Agronomist Services
#### 9.4.1 Data model: provider profiles (specializations, certifications, coverage, availability), services (consultation types, pricing tiers), bookings (calendar), consultations (chat, video, images), ratings
#### 9.4.2 Booking engine: real-time availability calendar, deposit (20% via escrow), in-app consultation room (WebRTC video, chat, image sharing), AI-generated summary, prescription generation, follow-up suggestions
#### 9.4.3 Consultation delivery: pre-consultation farm profile sharing, real-time image sharing, AI summary, prescription document, post-consultation rating
#### 9.4.4 Commission: 15% platform commission, tiered reduction for high volume (>50/month = 12%, >100 = 10%), transparent fee breakdown
### 9.5 Logistics & Transport Services
#### 9.5.1 Service model: vehicle types (boda boda, tuk-tuk, pickup, lorry, refrigerated truck), route management, fare estimation (distance + vehicle + weight + fuel), driver verification
#### 9.5.2 Real-time tracking: GPS streaming → Mapbox Map Matching → route visualization → ETA → delivery confirmation with photo proof; geofenced alerts for pickup/delivery
#### 9.5.3 Cross-border logistics: EAC Common Market compliance, customs documentation, cross-border fare with border wait estimates, bilingual instructions, transporter vetting
#### 9.5.4 Commission and management: 10% platform commission, driver onboarding (ID, license, vehicle inspection, community reference), performance scoring, incentive bonuses, dispute resolution
### 9.6 Warehouse Services
#### 9.6.1 Data model: facilities (location, type: grain store/cold storage/silo, capacity, amenities), availability calendar (seasonal pricing), bookings (duration, quantity, crop type), IoT integration, quality grading
#### 9.6.2 Booking and monitoring: search by location/capacity/crop/price, mobile money deposit, IoT dashboard (temperature/humidity alerts), warehouse receipt generation (WRS Act 2005 compliance)
#### 9.6.3 IoT integration: temperature/humidity via MQTT, per-crop thresholds (maize <13% moisture, potatoes 4-7°C), SMS/app alerts, historical data for insurance, blockchain receipts
#### 9.6.4 Commission: 5% per booking, subscription for operators (premium listing, analytics), insurance integration (3-7% of input value)
### 9.7 Veterinary Services
#### 9.7.1 Service model: provider profiles (TVB registration, specializations: large animal/poultry/dairy, coverage, emergency availability), services (consultation, farm visit, vaccination, emergency), livestock health records
#### 9.7.2 Tele-veterinary: image/video remote diagnosis, AI-assisted symptom triage, OTC prescription, referral to physical visit, vaccination schedule management
#### 9.7.3 Livestock management: animal inventory (species, breed, age, health), vaccination schedules, breeding records, health timeline, veterinary consultation context
#### 9.7.4 Emergency response: emergency call with GPS, nearest vet dispatch, ETA, pre-arrival first aid, follow-up scheduling; target <30 minute response
### 9.8 Soil Testing Services
#### 9.8.1 Three-tier architecture: Tier 1 instant AI from iSDAsoil API (30m, 14 variables, free); Tier 2 physical sample collection (lab pickup); Tier 3 lab comprehensive analysis with precision recommendations
#### 9.8.2 Sample collection workflow: request → nearest certified lab → pickup scheduled → GPS-tagged collection → 72-hour SLA processing → digital results → AI fertilizer recommendation
#### 9.8.3 Results delivery: 14-variable nutrient radar chart, pH interpretation with crop suitability, AI fertilizer blend (NPK ratios, rates, timing), historical tracking per plot, AI Agronomist integration
#### 9.8.4 Lab partner integration: lab onboarding (certification), sample tracking dashboard, digital results portal, QA scoring, turnaround monitoring, automated farmer notification

## 10. API Design — RESTful, Standards, Versioning (~700 words, 2 tables, 1 diagram)
### 10.1 API Standards & Conventions
#### 10.1.1 JSON:API first-party compliance: sparse fieldsets (?fields[post]=title,body), compound documents (?include=author,comments), cursor pagination, standard errors, content negotiation
#### 10.1.2 Versioning: URL path (/v1/, /v2/), deprecation headers with sunset dates, 6-month transition window, backward compatibility within major versions, Accept header negotiation
#### 10.1.3 Request/response patterns: standard envelope, HTTP status codes, idempotency keys, validation with detailed errors, Brotli/gzip compression
### 10.2 Endpoint Organization
#### 10.2.1 Domain-based: /v1/auth/*, /v1/marketplace/*, /v1/forum/*, /v1/scanner/*, /v1/services/*, /v1/ai/*, /v1/payments/*, /v1/notifications/*, /v1/admin/*
#### 10.2.2 Mobile-optimized BFF: reduced payloads, delta sync (/sync?since=timestamp), batch for offline sync, field selection for bandwidth
#### 10.2.3 Auth: Sanctum tokens, refresh mechanism, biometric login, device fingerprinting for SIM swap detection, rate limiting (100 req/min standard, 500 premium)
### 10.3 Developer Experience
#### 10.3.1 OpenAPI 3.1: auto-generated from Laravel resources, Swagger UI, SDK generation, webhook docs, rate limit visibility
#### 10.3.2 Webhooks: event subscriptions, HMAC verification, exponential backoff retry, event idempotency, delivery logs

## 11. Payment & Financial Architecture — Mobile Money, Escrow, Insurance (~1,300 words, 4 tables, 3 diagrams)
### 11.1 Mobile Money Integration
#### 11.1.1 Unified payment gateway router: provider-agnostic abstraction, Tanzania (M-Pesa, Tigo Pesa, Airtel Money, HaloPesa, Mixx, AzamPesa via ClickPesa), Kenya (M-Pesa Daraja 3.0 12K TPS, Airtel Money), Uganda (MTN MoMo, Airtel), Rwanda (MTN MoMo, Airtel, IremboPay)
#### 11.1.2 API implementation: M-Pesa Daraja 3.0 STK Push (OAuth 2.0, callbacks, idempotency), MTN MoMo Open API (sandbox → KYC → production ~10 days), automatic provider fallback
#### 11.1.3 Cross-border: Phase 1 single-country, Phase 2 Onafriq integration (1B wallets, 400K agents), currency conversion, EAC harmonization readiness
### 11.2 Wallet & Escrow System
#### 11.2.1 Sub-wallet architecture: Main Wallet (deposits, withdrawals), Escrow Wallet (transaction holds), Savings Wallet, Insurance Wallet; per-country isolation for compliance
#### 11.2.2 Escrow flow: buyer pays → Escrow hold → seller fulfills → delivery confirmed (GPS + photo) → funds released → dispute window closes → finalized; auto-release after 48 hours
#### 11.2.3 Regulatory compliance: segregated trust accounts per country (BoT TZ, CBK KE, BoU UG, BNR RW), escrow fee 1-1.5%, pass-through MNO fees, quarterly regulatory reporting
### 11.3 Commission & Monetization Engine
#### 11.3.1 Commission structure: Marketplace 3-5%, Logistics 10%, Warehouse 5%, Agronomist 15% (tiered to 10%), Veterinary 12%, Soil Testing 8%; deducted at transaction, monthly settlements, tax invoices
#### 11.3.2 Disbursement workflow: commission to platform revenue wallet, net to provider wallet, monthly settlement reports, automated tax invoice generation per country
### 11.4 Insurance Integration
#### 11.4.1 Agricultural insurance: index-based crop (weather triggers), input (seed/fertilizer protection), livestock (mortality), warehouse (stored goods); premiums 3-7% of input value
#### 11.4.2 Insurance workflow: select at checkout → premium calculated → deducted from wallet → digital policy → claim via app (photo + GPS) → automated processing for index-based

## 12. Real-Time, Logistics & Maps (~900 words, 2 tables, 2 diagrams)
### 12.1 Real-Time Communication Architecture
#### 12.1.1 Laravel Reverb: 90% cost reduction vs Pusher, first-party WebSocket, event broadcasting for order updates, delivery tracking, chat, appointments, pest alerts; triple-redundant: WebSocket → FCM push → SMS fallback
#### 12.1.2 Push notifications: Firebase Cloud Messaging, topic subscriptions (weather, pests, orders), rich notifications with action buttons, quiet hours by timezone, A/B testing
#### 12.1.3 Background location and geofencing: GPS for logistics (<5% battery/hour), geofenced pickup/delivery alerts, pest alert geofencing (50km radius), farm boundary mapping with PostGIS
### 12.2 Maps & Routing
#### 12.2.1 Mapbox primary: cost-optimized ($2,325/mo at 10K users vs $4,300 Google), custom markers, route visualization, offline tile caching, 300ms debounced search; OSM fallback via MapLibre
#### 12.2.2 Route optimization: OSRM self-hosted fallback, multi-stop planning, fare calculation (distance + vehicle + weight + fuel), real-time traffic
#### 12.2.3 GPS tracking dashboard: live driver location, ETA with traffic, delivery proof (photo + GPS + timestamp), route history, performance analytics (on-time rate, average speed)

## 13. Security, Compliance & Data Sovereignty (~900 words, 2 tables, 2 diagrams)
### 13.1 Security Architecture
#### 13.1.1 Authentication hardening: Passkey/WebAuthn (Laravel 13 native) eliminating password phishing, device fingerprinting for SIM swap detection (43% of attacks), biometric + PIN for 72% fraud reduction, certificate pinning, root/jailbreak detection
#### 13.1.2 Data protection: AES-256 for KYC at rest, TLS 1.3 in transit, PII hashing in logs, immutable audit trail, secure token storage (Keystore/Keychain), automatic refresh
#### 13.1.3 Threat mitigation: social engineering (58-72% of fraud) → passkey; SIM swap (43%) → device fingerprinting; agent-assisted fraud (38%) → biometric + PIN; fake payments → API callback verification; malware → cert pinning + root detection
### 13.2 Regulatory Compliance
#### 13.2.1 Data protection: Tanzania PDPA 2022 (data localization required), Kenya DPA 2019 + VASPA 2025, Uganda DPPA 2019, Rwanda Law 058/2021 — per-country consent management
#### 13.2.2 Agricultural regulation: TFRA (Tanzania), KEPHIS/PCPB (Kenya), UNADA (Uganda), veterinary registration (TVB, KVB) — verification integration
#### 13.2.3 Financial compliance: non-bank PSP licensing per country (BoT TZ: 131 licensed; CBK KE: sandbox; BoU UG: partner with FI; BNR RW: eKash), escrow trust accounts
### 13.3 Data Sovereignty Architecture
#### 13.3.1 Regional data residency: AWS af-south-1 (Cape Town) primary with <20ms Nairobi Local Zone future, per-country RLS isolation, Cloudflare Africa PoPs (Lagos, Nairobi, JHB), in-region encryption keys
#### 13.3.2 Government data sharing: aggregated anonymized disease data for TARI/KALRO/NARO/RAB, monthly agricultural reports, extension impact metrics, API for government dashboards — data sovereignty as competitive moat

## 14. DevOps, Deployment & Scaling (~800 words, 2 tables, 2 diagrams)
### 14.1 CI/CD Pipeline
#### 14.1.1 GitHub Actions: automated testing (PHPUnit + Flutter), quality (PHPStan, Dart analyze), security scanning (Snyk, SonarQube), Docker builds, Firebase App Distribution, Codemagic for iOS
#### 14.1.2 Deployment: blue-green with FrankenPHP zero-downtime, migrations before switch, feature flags, canary for high-risk, auto-rollback on health failure
### 14.2 Infrastructure & Scaling
#### 14.2.1 Cloud: AWS ECS Fargate or Google Cloud Run, auto-scaling 0-100 (critical for seasonality), serverless pay-per-use, PostgreSQL RDS with read replicas
#### 14.2.2 Scaling: HPA based on CPU/memory/request rate, DB read replicas for reporting, Redis cluster, CDN for assets, PgBouncer pooling
### 14.3 Monitoring & Observability
#### 14.3.1 Monitoring: Laravel Pulse (slow queries, queues, cache), Sentry errors, Firebase Crashlytics mobile, Laravel Horizon queues, Prometheus + Grafana infrastructure
#### 14.3.2 Health and alerting: FrankenPHP health checks, 99.9% SLA, P95 latency alerts (>500ms), error rate, disk/memory, PagerDuty on-call
#### 14.3.3 Disaster recovery: PostgreSQL PITR (24-hour RPO), daily backups 30-day retention, cross-region replication, quarterly DR drills

## 15. Development Roadmap & Milestones (~700 words, 1 table, 1 diagram)
### 15.1 Phase 1 — Tanzania MVP (Months 1-4)
#### 15.1.1 Core platform: auth (OTP + biometric), KYC framework, RBAC, multi-tenancy (TZ), PostgreSQL + pgvector, Meilisearch, admin dashboard
#### 15.1.2 MVP features: Marketplace (listing, search, cart, M-Pesa/Tigo Pesa, escrow), Disease Scanner (TF Lite + Gemini, 10 diseases), Forum (threads, posts, moderation), AI Agronomist (RAG + TARI, text chat)
#### 15.1.3 MVP targets: 10,000 registered users, 100 verified agrodealers, 5,000 disease scans, 500 daily forum posts, 99.5% uptime, <2s launch on Tecno Spark 10
### 15.2 Phase 2 — Services & Kenya (Months 5-8)
#### 15.2.1 Services launch: Agronomist booking, Logistics (boda-to-truck), Warehouse, Veterinary, Soil Testing (3-tier); unified booking/payment/review
#### 15.2.2 Kenya entry: M-Pesa Daraja 3.0, KEPHIS/PCPB compliance, KALRO knowledge base, localized crops, Swahili/English bilingual
#### 15.2.3 Voice AI and offline: Voice Service Layer (Whisper STT + Google TTS), voice-first UI, offline-first full (72-hour), PWA launch
### 15.3 Phase 3 — Uganda, Rwanda & Advanced AI (Months 9-12)
#### 15.3.1 UG and RW: MTN MoMo both markets, UNADA/NARO/RAB partnerships, French (RW), Luganda (UG), country-specific disease models
#### 15.3.2 Advanced AI: fine-tuned agricultural LLM (Mistral-7B QLoRA), expanded scanner (20 diseases), Sentinel-2 NDVI monitoring, cooperative/SACCO module, government analytics dashboard
### 15.4 Phase 4 — Scale & EAC Integration (Months 13-18)
#### 15.4.1 Cross-border EAC: Onafriq payments, customs docs, cross-border logistics, EAC Common Market compliance, regional disease surveillance
#### 15.4.2 Scale targets: 100,000 MAU, 500 verified agrodealers, 500 active service providers, 50,000 monthly scans, $500K monthly GMV, 15,000+ SACCO members, 99.9% uptime

# References
## mkulimaforum_requirements.md
- **Type**: Requirements analysis
- **Description**: Explicit and implicit requirements extracted from user query and original document
- **Path**: /mnt/agents/output/mkulimaforum_requirements.md

## mkulimaforum_artifact_synthesis.md
- **Type**: Research synthesis
- **Description**: Consolidated data points and technology recommendations from 5 research dimensions
- **Path**: /mnt/agents/output/mkulimaforum_artifact_synthesis.md

## mkulimaforum_structure_design.md
- **Type**: Structure design
- **Description**: Chapter hierarchy, word counts, table/diagram allocations, research mapping
- **Path**: /mnt/agents/output/mkulimaforum_structure_design.md

## mkulimaforum_content_plan.md
- **Type**: Content plan
- **Description**: Detailed content specifications per chapter with tables, code, and data points
- **Path**: /mnt/agents/output/mkulimaforum_content_plan.md

## mkulimaforum_dim01_agritech_landscape.md
- **Type**: Research dimension
- **Description**: East African agritech ecosystem research
- **Path**: /mnt/agents/output/research/mkulimaforum_dim01_agritech_landscape.md

## mkulimaforum_dim02_mobilemoney_fintech.md
- **Type**: Research dimension
- **Description**: Mobile money and fintech integration research
- **Path**: /mnt/agents/output/research/mkulimaforum_dim02_mobilemoney_fintech.md

## mkulimaforum_dim03_ai_ml_agriculture.md
- **Type**: Research dimension
- **Description**: AI/ML technologies for agriculture research
- **Path**: /mnt/agents/output/research/mkulimaforum_dim03_ai_ml_agriculture.md

## mkulimaforum_dim04_logistics_services.md
- **Type**: Research dimension
- **Description**: Agricultural logistics and services infrastructure research
- **Path**: /mnt/agents/output/research/mkulimaforum_dim04_logistics_services.md

## mkulimaforum_dim05_architecture_patterns.md
- **Type**: Research dimension
- **Description**: Modern architecture patterns and East African tech context research
- **Path**: /mnt/agents/output/research/mkulimaforum_dim05_architecture_patterns.md

## mkulimaforum_insight.md
- **Type**: Cross-dimension insights
- **Description**: 10 strategic insights from cross-dimension analysis
- **Path**: /mnt/agents/output/research/mkulimaforum_insight.md

## mkulimaforum_cross_verification.md
- **Type**: Cross-verification
- **Description**: Confidence tiers and conflict zone analysis
- **Path**: /mnt/agents/output/research/mkulimaforum_cross_verification.md

## Original Architecture Document
- **Type**: Source file
- **Description**: Original Mkulima Super App architecture document (input)
- **Path**: /mnt/agents/upload/mkulima_software_architecture_laravel_flutter.md
