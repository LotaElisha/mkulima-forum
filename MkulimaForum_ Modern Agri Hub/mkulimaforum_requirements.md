# MkulimaForum Requirements Analysis

**Version:** 1.0
**Date:** June 2026
**Analyst:** Requirements Analysis Team
**Project:** MkulimaForum -- Modern East African Agricultural Super App
**Traceability Base:** Original Mkulima Super App Architecture (v1.0) + User Enhancement Request

---

## Executive Summary

This document captures all requirements for the MkulimaForum platform -- a comprehensive reimagining of the original Mkulima Super App architecture. The user's request to "make it much better and modern for the East African environment" drives fundamental changes across all system layers. The platform must now explicitly support four countries (Tanzania, Kenya, Uganda, Rwanda), five core modules (Agrodealer Marketplace, Plant Disease Scanner, Farmers Forum, Services Directory, AI Agronomist), and incorporate cutting-edge 2025-2026 technologies including RAG-based AI chat, vector databases, on-device ML inference, and modern Flutter/Laravel patterns.

---

## 1. Explicit Functional Requirements

Features explicitly requested by the user in the enhancement request.

| ID | Requirement | Priority | Source | Traceability |
|----|-------------|----------|--------|-------------|
| **EF-001** | **Agrodealer Marketplace** -- Multi-vendor e-commerce platform where registered agrodealers can list and sell agricultural inputs (seeds, fertilizers, pesticides, tools) to farmers with search, filtering, cart, checkout, and order management | Must-Have | User request: "add agrodealer marketplace" | Original had basic marketplace; this elevates it to first-class feature with TFRA/Kenya PCPB/Uganda UNADA compliance |
| **EF-002** | **Plant Disease Scanner** -- AI-powered image recognition system that identifies crop diseases from smartphone camera photos, providing diagnosis, treatment recommendations, and severity assessment for East African crops (maize, cassava, banana, coffee, tea, beans, potatoes) | Must-Have | User request: "plant disease scanner" | Original had scanner via Gemini Vision; enhancement requires on-device ML + cloud hybrid, East African crop-specific training |
| **EF-003** | **Farmers Forum** -- Community discussion platform with threaded conversations, topic categories (pests, markets, weather, techniques), image sharing, upvoting, expert verification badges, and localized sub-forums per region | Must-Have | User request: "faremers forum" | Original had basic forum; enhancement requires full community features, expert verification, RAG-powered FAQ suggestions |
| **EF-004** | **Agronomist Services** -- Directory and booking system for certified agronomists/extension officers with profiles, specializations, availability calendar, appointment booking, in-app consultation (chat/video), and rating system | Must-Have | User request: "services (agronomist...)" | New requirement not fully addressed in original |
| **EF-005** | **Logistics & Transport Services** -- Freight and delivery booking system connecting farmers with transporters (bodaboda pickups to trucks) for moving produce, inputs, and equipment with GPS tracking, fare estimation, and route optimization | Must-Have | User request: "services (...Logistics & transport...)" | Original had logistics; enhancement requires multi-country adaptation, cross-border EAC logistics |
| **EF-006** | **Warehouse Services** -- Digital warehouse marketplace for farmers to find, book, and pay for agricultural storage facilities (grain stores, cold storage) with capacity monitoring, temperature alerts, and seasonal pricing | Must-Have | User request: "services (...warehouse...)" | Original mentioned warehouse booking; enhancement requires full marketplace model for storage |
| **EF-007** | **Veterinary Help Services** -- Directory and tele-veterinary consultation system for livestock farmers to connect with registered veterinary officers, book farm visits, get remote diagnosis via chat/image sharing, and access vaccination schedules | Must-Have | User request: "services (...verterinary help...)" | New requirement not in original |
| **EF-008** | **Soil Testing Services** -- Soil testing request and results management system allowing farmers to request soil sample collection, view lab results with nutrient breakdown, receive AI-powered fertilizer recommendations based on soil analysis | Must-Have | User request: "services (...soil testing)" | New requirement not in original |
| **EF-009** | **Rebrand to MkulimaForum** -- Complete application rebranding from "Mkulima Super App" to "MkulimaForum" with updated logo, color scheme, app store listings, and domain configuration | Must-Have | User request: "lets call the app MkulimaForum" | Full rebrand affects all UI/UX, marketing materials, API endpoints |

---

## 2. Implicit Functional Requirements

Features that naturally accompany the explicit requirements based on domain knowledge, user needs, and technical necessity. Each is justified by the East African agricultural technology context.

| ID | Requirement | Priority | Rationale | Traceability to Original |
|----|-------------|----------|-----------|-------------------------|
| **IF-001** | **Multi-Factor Authentication** -- Phone-based OTP registration (SMS via Twilio/Africa's Talking) with phone as primary identifier; password optional; biometric login on supported devices | Must-Have | Phone is primary digital identity in East Africa; most farmers lack email | Original: Section 3.3 auth flow |
| **IF-002** | **Role-Based Access Control (RBAC)** -- Granular permission system for: `farmer`, `agrodealer`, `agronomist`, `veterinary_officer`, `logistics_provider`, `warehouse_operator`, `admin`, `extension_officer` | Must-Have | Multiple user types with different capabilities, data visibility, and monetization paths | Original: Spatie RBAC with roles |
| **IF-003** | **Offline-First Architecture** -- Critical data cached locally (product catalog, weather, forum threads, diagnosis history) via Hive/SQLite; queued uploads for posts, images, orders when connectivity restored | Must-Have | Rural connectivity is intermittent; 57.75% of African mobile users still rely on USSD/feature phones; offline capability directly impacts adoption | Original: Section 7.4 offline-first strategy |
| **IF-004** | **Voice AI Interface** -- Speech-to-text input (Swahili + English) for AI chatbot queries and forum search; Text-to-Speech responses for low-literacy farmers; voice notes in forum posts | Must-Have | Literacy rates vary (TZ 77.9%, KE 81.5%, UG 76.5%, RW 73.2%); voice interface democratizes access | Original: Section 6.3 voice AI; enhancement adds multilingual STT/TTS |
| **IF-005** | **USSD Fallback Service** -- Basic USSD menu (*384*99# style) for weather forecasts, market prices, and emergency veterinary alerts accessible on feature phones without internet | Should-Have | ~43% of African mobile users still use feature phones; USSD reaches farmers without smartphones; risk mitigation from original | Original: Risk mitigation table -- "Low smartphone penetration" |
| **IF-006** | **Push Notifications** -- Firebase Cloud Messaging for order updates, delivery tracking, pest outbreak alerts, weather warnings, forum replies, and appointment reminders with topic-based subscription | Must-Have | Real-time engagement critical for time-sensitive agricultural decisions | Original: Section 9.1 FCM integration |
| **IF-007** | **Wallet & Escrow System** -- In-app digital wallet supporting deposits/withdrawals via mobile money; escrow for marketplace transactions (hold until delivery confirmed); transaction history | Must-Have | Trust-building for online marketplace; mobile money is dominant payment method in East Africa ($1+ trillion annually) | Original: Section 8.2 wallet/escrow |
| **IF-008** | **KYC & Verification System** -- Identity verification flow: farmers (farm GPS location, ID), agrodealers (TFRA/PCPB/UNADA license), agronomists (professional cert), vets (TVB registration), transporters (driver license, vehicle docs) | Must-Have | Regulatory compliance (TFRA TZ, PCPB KE, UNADA UG); trust and safety for marketplace | Original: Section 3.3 KYC + Section 10.2 TFRA compliance |
| **IF-009** | **Rating & Review System** -- Star ratings (1-5) with written reviews for products, services, and service providers; verified-purchase badge; moderation queue for flagged content | Must-Have | Trust economy for marketplace and services; helps farmers make informed decisions | Original: reviews table existed |
| **IF-010** | **Search & Discovery** -- Full-text search with Swahili + English support; filters by location, price, category, verification status; Meilisearch/Scout integration; autocomplete suggestions | Must-Have | Large product/service catalogs require efficient discovery; original had search | Original: Section 3.2 Meilisearch integration |
| **IF-011** | **Multi-Language Support (i18n)** -- Interface in English and Swahili with extensible framework for French (Rwanda/Burundi) and Luganda; all user-facing content translatable | Must-Have | Swahili is lingua franca of East Africa; French needed for Rwanda; local language support increases adoption by 40%+ | Original: locale middleware, ARB files |
| **IF-012** | **Real-Time GPS Tracking** -- Live location sharing for deliveries, service provider dispatch, and pest alert geofencing using Google Maps Flutter with offline map tiles for rural areas | Must-Have | Logistics requires real-time tracking; pest/disease alerts need geofencing | Original: Section 9.1 tracking system |
| **IF-013** | **Weather Integration** -- Hyper-local 7-day weather forecasts via Open-Meteo API with AI-powered farming advice based on weather + crop calendar; severe weather push alerts | Must-Have | Weather is #1 concern for rain-fed agriculture (80% of East African farms); original had this | Original: Section 6.2 weather engine |
| **IF-014** | **AI-Powered Crop Recommendations** -- RAG-based (Retrieval-Augmented Generation) system that combines farmer profile, soil data, weather, market prices, and regional knowledge base to recommend crops, planting dates, and expected yields | Must-Have | Modernization requirement; RAG provides more accurate, locally-grounded advice than pure LLM; "much better" implies this | Original: Gemini recommendations; enhancement requires RAG pipeline |
| **IF-015** | **Image Compression & Upload** -- Automatic image compression to <=2MB for disease scans, forum posts, and KYC documents; multipart upload with progress indicator; offline queue for uploads | Must-Have | Bandwidth is expensive and limited in rural areas; compressed images essential | Original: image compression utility |
| **IF-016** | **Commission & Monetization Engine** -- Automated commission calculation and disbursement: marketplace (3-5%), logistics (10%), tool rental (8%), agronomist bookings (15%), warehouse (5%), veterinary (12%) | Must-Have | Platform sustainability requires revenue model; service marketplace needs commission tracking | Original: Section 8.2 commission structure |
| **IF-017** | **Admin Dashboard** -- Web-based admin panel for user management, content moderation, KYC verification queue, analytics (MAU, transactions, disease reports), service provider approval, and platform configuration | Must-Have | Platform operations require centralized management; original mentioned React/Vue dashboard | Original: Phase 2 roadmap item |
| **IF-018** | **Analytics & Reporting** -- Aggregated dashboards showing: crop disease trends by region, marketplace transaction volumes, active users by country, service provider performance, weather impact analysis | Should-Have | Data-driven decision making for platform and government partners (TARI, KALRO, NARO); modernization | Original: Phase 4 analytics mentioned |
| **IF-019** | **SMS Notifications Fallback** -- Critical alerts (pest outbreaks, weather warnings, appointment reminders) delivered via SMS for users without smartphones or data connectivity | Should-Have | Reaches all users regardless of device; Africa's Talking SMS gateway integration | Original: Risk mitigation -- "SMS fallback for alerts" |
| **IF-020** | **Dark Mode & Modern UI** -- Material 3 design with dynamic theming, glassmorphism cards, dark mode default, shimmer loaders, and adaptive layouts for 4-7 inch screens | Should-Have | Modernization requirement; "make it much better" implies visual upgrade; Flutter 3.24 Material 3 support | Original: Section 7.3 glassmorphism, dark mode |
| **IF-021** | **Cross-Border EAC Trade** -- Support for agricultural product listings and logistics across EAC borders with customs documentation, cross-border payment support, and trade compliance | Could-Have | EAC Common Market Protocol enables free movement of goods; growing cross-border agricultural trade ($680M KE-TZ corridor in 2025) | Original: Phase 4 expansion |
| **IF-022** | **Progressive Web App (PWA)** -- Lightweight web version of the app accessible via browser for users who cannot install the native app, with core features (forum browsing, weather, price checks) | Should-Have | Reduces barrier to entry; reaches users with storage-constrained devices; original mentioned PWA as risk mitigation | Original: Risk mitigation -- "Progressive Web App fallback" |
| **IF-023** | **Data Export & Reporting** -- Farmers can export farm records (diagnoses, soil tests, transactions) as PDF/Excel; service providers can export earnings reports; compliance reports for regulators | Should-Have | Farm record-keeping is critical for credit access and compliance; modernization adds professional features | New -- modernization requirement |
| **IF-024** | **Content Moderation** -- AI-assisted content moderation for forum posts, reviews, and marketplace listings with automated flagging of inappropriate content, spam detection, and human review queue | Should-Have | Community platform needs moderation; AI reduces manual moderation workload | New -- implicit for forum feature |
| **IF-025** | **Livestock Management Module** -- Track livestock inventory, vaccination schedules, breeding records, and health history integrated with veterinary services | Should-Have | Veterinary services (EF-007) implies livestock management; 76.5% of East African farmers own livestock | New -- derived from veterinary feature |

---

## 3. Non-Functional Requirements

System qualities and operational characteristics required for successful operation in East African conditions.

| ID | Requirement | Target | Measurement | Traceability |
|----|-------------|--------|-------------|-------------|
| **NF-001** | **App Launch Time** | < 2 seconds on mid-range Android devices (e.g., Tecno Spark 10, Samsung A14) | Cold start time measured via Firebase Performance Monitoring | Original: performance optimization in Phase 3 |
| **NF-002** | **API Response Time** | < 500ms for 95th percentile of API endpoints; < 200ms for cached reads | New Relic/Datadog APM p95 latency metrics | Original: API-first architecture |
| **NF-003** | **Offline Data Availability** | Critical features functional for 72 hours without connectivity (weather, catalog, forum, wallet balance) | Manual testing + automated offline test suite | Original: Section 7.4 offline-first |
| **NF-004** | **Concurrent Users** | Support 50,000 concurrent users with auto-scaling to 100,000 during peak seasons (planting/harvest) | Load testing via k6/Locust simulating realistic usage patterns | Original: Phase 3 -- "simulate 10k concurrent users" |
| **NF-005** | **Data Sovereignty** | Tanzanian user data stored in African cloud regions (GCP europe-west2 or AWS af-south-1); option for in-country replication to Vodacom/Airtel cloud for regulatory compliance | Cloud region verification; data residency audit | Original: Section 10.1 data protection |
| **NF-006** | **Image Upload Size** | Compressed to <= 2MB per image with acceptable quality loss; progressive upload with resume capability | File size measurement + user perception testing | Original: Section 6.1 pipeline |
| **NF-007** | **Battery Efficiency** | Background location tracking consumes < 5% battery per hour; app overall < 10% of daily battery on average | Android battery stats + Firebase Performance | New -- logistics requires GPS tracking |
| **NF-008** | **Network Resilience** | Graceful degradation on 2G/Edge networks; core features functional at 50kbps; automatic quality reduction for images/videos on slow connections | Network throttling tests via Charles Proxy/Chrome DevTools | Original: Risk mitigation -- "Poor rural connectivity" |
| **NF-009** | **Security -- Authentication** | OWASP Mobile Top 10 compliance; certificate pinning; secure token storage via Keystore/Keychain; automatic token refresh | Quarterly penetration testing + SAST/DAST scans | Original: Section 3.3 + 10.1 security layers |
| **NF-010** | **Security -- Data Protection** | AES-256 encryption for KYC documents at rest; TLS 1.3 in transit; PII hashing in logs; immutable audit trail for financial transactions | Security audit + compliance certification | Original: Section 10.1 encryption |
| **NF-011** | **Availability/Uptime** | 99.9% uptime for core services; 99.5% for AI diagnosis (accounting for third-party API dependencies) | Uptime monitoring via Pingdom/UptimeRobot + status page | New -- SLA requirement |
| **NF-012** | **Disaster Recovery** | Point-in-time recovery (PITR) for PostgreSQL with 24-hour RPO; automated daily backups with 30-day retention; cross-region replication | Quarterly DR drills + backup verification | Original: Section 11 infrastructure |
| **NF-013** | **Accessibility** | WCAG 2.1 AA compliance; minimum 16dp touch targets; high contrast mode; screen reader support for critical flows | Accessibility audit via automated tools + manual testing | New -- inclusion requirement |
| **NF-014** | **Scalability -- Database** | PostgreSQL partitioning by month for orders and transactions; read replicas for reporting queries; connection pooling via PgBouncer | Database performance benchmarks; query execution time monitoring | Original: Section 4.2 partitioning |
| **NF-015** | **AI Diagnosis Accuracy** | >= 85% top-1 accuracy for East African crop diseases; human agronomist review for confidence < 70%; continuous model improvement | Confusion matrix evaluation on held-out test set; user feedback loop | Original: Section 6.1 -- 70% confidence threshold |
| **NF-016** | **Localization Coverage** | English (100% coverage), Swahili (100% coverage), French (Rwanda/Burundi -- 80% coverage), Luganda (Uganda -- 60% coverage) | Translation coverage audit via l10n tooling | New -- regional expansion |
| **NF-017** | **App Size** | APK < 30MB; total installed size < 80MB with cached data; separate APK per ABI to reduce download size | Build output measurement; Play Console size metrics | New -- low-end device optimization |
| **NF-018** | **Code Quality** | > 80% test coverage (unit + integration); zero critical/high security vulnerabilities in dependency scan; code review for all PRs | PHPUnit/Flutter test coverage reports + Snyk/Dependabot scans | New -- modernization requirement |

---

## 4. Technical Stack Requirements

| Component | Technology | Version | Justification |
|-----------|-----------|---------|---------------|
| **Backend Framework** | Laravel | 11.x (latest stable) | Streamlined structure, per-second rate limiting, built-in health checks, 15-20% faster bootstrap vs Laravel 10, enhanced Eloquent ORM; original specified Laravel 11 | Original + Laravel 11 research |
| **Mobile Frontend** | Flutter | 3.24+ (with Impeller) | Impeller rendering engine (smoother animations), Material 3 support, TreeView/CarouselView widgets, GPU preview, reduced shader jank; single codebase for Android/iOS; original specified Flutter 3.x | Original + Flutter 3.24 research |
| **Admin Dashboard** | Flutter Web or React/Vue 3 | Latest stable | Reuse of Flutter widgets if Flutter Web; React/Vue offers richer ecosystem for admin analytics dashboards | Original: React/Vue SPA or Flutter Web |
| **Primary Database** | PostgreSQL | 16+ | PostGIS for geospatial (farm boundaries, routing, pest alerts), JSONB for flexible attributes, row-level security, native partitioning, superior concurrency | Original: Section 4 |
| **Cache & Queue** | Redis | 7.x | Dual use for caching + queue; Laravel native integration via Horizon; simpler ops than RabbitMQ | Original: Section 11 |
| **Vector Database** | Pinecone or Qdrant or pgvector | Latest stable | RAG pipeline for AI agronomist -- stores agricultural knowledge base embeddings for semantic search; pgvector preferred for single-DB simplicity | New -- RAG/AI modernization |
| **Search Engine** | Meilisearch | 1.x | Full-text search with Swahili support; typo-tolerant; faceted search for marketplace; Laravel Scout integration | Original: Section 3.2 |
| **Push Notifications** | Firebase Cloud Messaging | Latest | Free tier generous; native Flutter integration; topic-based messaging; reliable delivery in Africa | Original: Section 9.1 |
| **Local Database (Flutter)** | Hive + SQLite | Latest | Hive for structured caching (products, weather); SQLite for relational offline data (forum posts, orders); drift package for type-safe SQLite | Original: Section 7.3 |
| **State Management** | BLoC (flutter_bloc) | 8.x | Predictable state management; testable; separation of concerns; offline-first sync handled elegantly | Original: Section 7.2 |
| **HTTP Client** | Dio | 5.x | Interceptors for auth/locale/region headers; retry logic; request/response logging; offline queue support | Original: Section 7.3 |
| **Maps** | Google Maps Flutter | Latest | Mature plugin; street view for logistics; custom markers for farms/warehouses; offline tile caching | Original: Section 7.3 |
| **Image Processing** | Custom TensorFlow Lite + Cloud Gemini | TFLite 2.x + Gemini API | On-device TFLite for offline diagnosis (lightweight model); Gemini Vision for complex cases and second opinion | New -- hybrid AI approach |
| **AI/LLM Orchestration** | Laravel + OpenAI-PHP client + Vector DB | Latest | Abstract LLM behind interface; Gemini primary, OpenAI fallback; RAG pipeline with vector similarity search | Original: Section 3.2 + enhancement |
| **Authentication** | Laravel Sanctum | 4.x | Lightweight API tokens; simpler than Passport; sufficient for mobile app auth; supports token refresh | Original: Section 3.3 |
| **RBAC** | Spatie Laravel Permission | 6.x | Mature package; role-permission middleware; cache-friendly | Original: Section 3.2 |
| **Multi-Tenancy** | Spatie Laravel Multitenancy | 4.x | Region-based tenancy; shared DB with scoped queries; supports TZ/KE/UG/RW isolation | Original: Section 3.2 |
| **Media Handling** | Spatie Laravel Media Library | 11.x | Image uploads with automatic conversions (thumbnail, medium, large); responsive images; collection organization | Original: Section 3.2 |
| **SMS Gateway** | Africa's Talking | Latest | Best SMS coverage in East Africa (TZ, KE, UG, RW); USSD support; airtime disbursement; competitive pricing | New -- regional SMS/USSD |
| **Voice AI** | Whisper API (OpenAI) + Google Cloud TTS | Latest | Whisper for STT (supports Swahili); Google Cloud TTS for Swahili voice synthesis; both have African language support | Original: Section 6.3 |
| **Weather API** | Open-Meteo | Free tier | Free, no API key required, high-resolution Africa coverage, reduces operational costs | Original: Section 6.2 |
| **CI/CD** | GitHub Actions | Latest | Automated testing, linting, Docker builds, Firebase App Distribution; codemagic for iOS builds | Original: Section 11.2 |
| **Container Orchestration** | Google Cloud Run or AWS ECS | Latest | Serverless containers; auto-scaling 0-100 instances; pay-per-use; ideal for variable agricultural seasonality | Original: Section 11.1 |
| **Monitoring** | Sentry + Firebase Crashlytics + Laravel Horizon | Latest | Error tracking (both platforms); queue monitoring; performance tracing; free tiers available | Original: Section 11.3 |
| **Storage** | Google Cloud Storage / AWS S3 | Standard | Image/document storage with CDN; lifecycle policies; signed URLs for secure access | Original: Section 2.1 |
| **CDN & Security** | Cloudflare | Pro plan | DDoS protection; WAF; image optimization; Africa edge POPs in Lagos, Nairobi, Johannesburg; caching | Original: Section 2.1 |

---

## 5. Stakeholder Requirements

| Stakeholder | Needs | Success Criteria |
|-------------|-------|-----------------|
| **Smallholder Farmers** (Primary Users) | - Access quality agricultural inputs at fair prices from verified dealers<br>- Get timely, accurate crop disease diagnosis and treatment<br>- Connect with agronomists and vets for expert advice<br>- Find affordable logistics to transport produce<br>- Access storage facilities during bumper harvests<br>- Weather forecasts and AI crop recommendations<br>- Community support and knowledge sharing<br>- Soil testing services with actionable recommendations | - 80%+ report finding needed inputs within 3 searches<br>- Disease diagnosis accuracy >85%<br>- Average agronomist response time < 4 hours<br>- Logistics booking completed in < 5 minutes<br>- Forum active with >100 posts/day per region<br>- 30-day retention rate > 40% |
| **Agrodealers** | - Digital storefront to reach more farmers<br>- Verified seller badge building trust<br>- Inventory management tools<br>- Order processing and fulfillment workflow<br>- Payment collection via mobile money<br>- Sales analytics and reporting<br>- Commission-transparent pricing | - >50% increase in customer reach within 6 months<br>- Average order value growth 20% month-over-month<br>- < 2% payment failure rate<br>- 4.0+ average seller rating |
| **Agronomists / Extension Officers** | - Professional profile showcasing expertise<br>- Appointment booking and calendar management<br>- In-app consultation tools (chat, video, image sharing)<br>- Access to farmer farm profiles for contextual advice<br>- Verified expert badge and ratings<br>- Earnings dashboard with transparent commissions | - >20 consultations/month per active agronomist<br>- 4.5+ average consultation rating<br>- 90%+ appointment attendance rate |
| **Veterinary Officers** | - Directory listing for livestock farmers to discover them<br>- Tele-veterinary consultation capability<br>- Farm visit booking with GPS directions<br>- Access to livestock health records<br>- Vaccination schedule management for farmers<br>- Emergency call feature for urgent cases | - >15 consultations/month per active vet<br>- Emergency response time < 30 minutes<br>- 4.0+ average service rating |
| **Logistics Providers (Bodaboda to Truck)** | - Job dispatch with GPS pickup/dropoff<br>- Real-time earnings tracking<br>- Route optimization<br>- Proof of delivery photo capture<br>- Vehicle/document verification for trust | - >50% driver utilization during peak season<br>- Average delivery completion time within ETA<br>- < 5% disputed deliveries |
| **Warehouse Operators** | - Digital listing of storage facilities<br>- Capacity management dashboard<br>- Booking calendar with seasonal pricing<br>- Temperature/humidity monitoring integration<br>- Insurance integration for stored goods | - >60% warehouse utilization year-round<br>- < 1% booking conflicts<br>- 4.0+ average facility rating |
| **Soil Testing Labs** | - Sample collection request management<br>- Digital results delivery to farmers<br>- Historical soil data tracking per farm<br>- Integration with AI fertilizer recommendations | - < 72 hours sample-to-result turnaround<br>- 90%+ farmer comprehension of results |
| **Platform Admin** | - User management and moderation<br>- KYC verification queue and approval<br>- Content moderation for forum/marketplace<br>- Analytics dashboard (MAU, GMV, transactions)<br>- Commission and payout management<br>- System configuration and feature flags | - KYC review turnaround < 24 hours<br>- Content moderation response < 2 hours<br>- Platform uptime 99.9% |
| **Government / Research Partners** (TARI, KALRO, NARO, Rwanda RAB) | - Aggregated anonymized disease outbreak data<br>- Agricultural trend analytics<br>- Extension service impact metrics<br>- Data sovereignty and compliance | - Monthly data reports delivered<br>- 100% data residency compliance<br>- API availability for government dashboards |

---

## 6. Regional/Localization Requirements

### 6.1 Country-Specific Requirements

| Country | Language | Currency | Mobile Money | Regulatory | Key Crops | Research Partner |
|---------|----------|----------|-------------|------------|-----------|-----------------|
| **Tanzania (TZ)** | Swahili, English | TZS (TZ Shilling) | **M-Pesa** (Vodacom), **Tigo Pesa** (MIC), **Airtel Money**, **Mixx by YAS**, **HaloPesa** | TCRA (telecom), TFRA (agrochemical licensing), NBS (statistics) | Maize, cassava, rice, bananas, coffee, cotton, cashew, tobacco, tea, sorghum, millet, sweet potatoes | TARI (Tanzania Agricultural Research Institute) |
| **Kenya (KE)** | Swahili, English | KES (Kenya Shilling) | **M-Pesa** (Safaricom, dominant -- 30M+ users), **Airtel Money** | CBK (Central Bank of Kenya), KEPHIS (seed certification), PCPB (pest control) | Tea, coffee, maize, beans, potatoes, horticulture (flowers, vegetables), wheat | KALRO (Kenya Agricultural & Livestock Research Organization) |
| **Uganda (UG)** | English, Swahili, Luganda | UGX (Uganda Shilling) | **MTN MoMo** (dominant), **Airtel Money** | Bank of Uganda, UNADA (agrodealers), MAAIF (agriculture) | Bananas (matooke), coffee (robusta), maize, beans, cassava, groundnuts, sorghum | NARO (National Agricultural Research Organization) |
| **Rwanda (RW)** | Kinyarwanda, French, English | RWF (Rwanda Franc) | **MTN MoMo**, **Airtel Money** | NBR (National Bank of Rwanda), RSB (standards), RAB (agriculture) | Coffee (Arabica), tea, potatoes, beans, bananas, maize, cassava, wheat | RAB (Rwanda Agriculture Board) |

### 6.2 Multi-Country Technical Requirements

| ID | Requirement | Details |
|----|-------------|---------|
| **RC-001** | **Currency Conversion** | All prices stored in local currency; API responses include currency code; admin configurable exchange rates for cross-border transactions |
| **RC-002** | **Phone Number Validation** | E.164 format per country: TZ (+255), KE (+254), UG (+256), RW (+250); auto-detection from SIM if permission granted |
| **RC-003** | **Mobile Money Gateway Router** | PaymentGatewayRouter pattern resolving provider per country: TZ (M-Pesa/Tigo Pesa/Airtel), KE (M-Pesa STK push/Airtel), UG (MTN MoMo/Airtel), RW (MTN MoMo/Airtel); graceful fallback if one provider fails |
| **RC-004** | **Multi-Tenancy by Region** | Shared PostgreSQL database with `region_id` and `country_code` scoping; row-level security policies; region-specific product catalogs, service providers, and content |
| **RC-005** | **Language Detection** | Automatic language detection from device locale with manual override; content stored in primary language with translation fallback; RTL support consideration for future Arabic expansion |
| **RC-006** | **Regulatory Compliance per Country** | TFRA verification for TZ agrodealers; PCPB for KE; UNADA for UG; KEPHIS seed certification; veterinary registration via respective national boards |
| **RC-007** | **EAC Cross-Border** | Support for cross-border agricultural trade within EAC; customs documentation generation; harmonized standards awareness; cross-border logistics booking |
| **RC-008** | **Local Cloud Hosting** | Primary: GCP europe-west2 or AWS af-south-1 (South Africa); roadmap for in-country hosting (Vodacom Cloud TZ, Safaricom Cloud KE) for data sovereignty |
| **RC-009** | **USSD Short Codes** | Country-specific USSD integration via Africa's Talking; *384*99# pattern adapted per country telco requirements |
| **RC-010** | **Seasonal Crop Calendar** | Country-specific planting and harvesting calendars integrated into AI recommendations; TZ (March-May, October-December rains); KE (March-May, October-December); UG (bimodal); RW (September-December main season) |

---

## 7. Modernization Gaps (Original Mkulima -> MkulimaForum)

This section maps the transformation from the original Mkulima Super App architecture to the modernized MkulimaForum vision.

| Area | Original State | Target State | Impact |
|------|---------------|-------------|--------|
| **Branding & Identity** | "Mkulima Super App" (ShambaSmart) generic branding | "MkulimaForum" -- purpose-built brand emphasizing community and agricultural discourse | Full UI/UX overhaul; new color palette; updated logo; app store assets |
| **AI Architecture** | Basic Gemini Vision API integration for disease scanning; simple prompt-based crop recommendations | **Hybrid On-Device + Cloud AI**: TensorFlow Lite model running on-device for offline diagnosis; Gemini Vision for complex cases; **RAG pipeline** with vector database for context-aware agronomist responses; fine-tuned models on East African crop data | 3-5x improvement in diagnosis availability (offline capable); significantly more accurate advice grounded in local knowledge |
| **Services Ecosystem** | Basic logistics (bodaboda dispatch), warehouse booking, tool rental | **Full Services Marketplace**: Agronomist booking, veterinary telemedicine, soil testing coordination, expanded logistics (cross-border), warehouse marketplace with IoT monitoring | Transforms from product marketplace to services ecosystem; 5x increase in addressable revenue streams |
| **Forum/Community** | Basic forum threads, pest alerts, knowledge base articles | **Full Community Platform**: Threaded discussions with rich media, expert verification badges, AI-powered FAQ suggestions, voice-note posts, upvoting, localized sub-forums, content moderation | Community becomes a core retention driver; positions app as the "Forum" in its name |
| **Plant Disease Scanner** | Cloud-only via Gemini API; requires internet; generic crop support | **Hybrid ML Pipeline**: On-device TFLite for maize LLS, cassava brown streak, banana bacterial wilt, coffee leaf rust; cloud for rare diseases; severity estimation; treatment product linking | Works offline; East African crop-specific; faster response time; builds farmer trust |
| **Payment Coverage** | M-Pesa (TZ) + Tigo Pesa + bank transfer | **Full East African Mobile Money**: M-Pesa (TZ + KE), Tigo Pesa, Airtel Money (all 4 countries), MTN MoMo (UG + RW), HaloPesa (TZ), Mixx by YAS (TZ); cross-border payment routing | 4-country payment coverage; provider fallback for reliability |
| **Voice Interface** | Azure STT/TTS; basic voice input for chatbot | **Multilingual Voice AI**: Whisper API for Swahili/English STT; Google Cloud TTS Swahili voice; voice notes in forum; voice search for products and services | Better Swahili recognition; voice as primary input method for low-literacy users |
| **Offline Capability** | Basic Hive caching for weather, catalog, auth | **Full Offline-First Architecture**: On-device ML diagnosis; offline forum browsing and drafting; queued marketplace actions; offline maps for logistics; background sync prioritization | Critical for rural farmers; competitive advantage over cloud-only apps |
| **USSD/SMS Access** | Mentioned as risk mitigation only | **First-Class USSD Channel**: *384*99# menu for weather, prices, alerts; SMS notifications for critical updates; Africa's Talking integration | Reaches feature phone users (~43% of market); disaster/pest alert distribution |
| **Database Architecture** | PostgreSQL with basic full-text search; Redis for cache | **PostgreSQL + Vector DB**: pgvector extension for RAG embeddings; Meilisearch for faceted product/service search; read replicas per region; materialized views for analytics | Enables semantic AI search; faster discovery; regional performance |
| **Push Notifications** | FCM with basic topic-based messaging | **Intelligent Notification System**: Behavioral targeting; quiet hours respecting; rich notifications with images; action buttons (e.g., "View Diagnosis", "Book Vet"); A/B testing framework | Higher engagement; better user experience; data-driven optimization |
| **Admin & Analytics** | Phase 4 roadmap item; basic analytics | **Real-Time Admin Dashboard**: Live transaction monitoring; disease outbreak heatmaps; service provider performance; automated KYC flagging; content moderation queue; cohort retention analysis | Operations team efficiency; data-driven product decisions; government reporting |
| **UI/UX Framework** | Material 2 with glassmorphism cards, dark mode | **Material 3 (You)**: Dynamic theming, CarouselView for marketplace, TreeView for forum categories, improved animations with Impeller, adaptive layouts, predictive back gestures | Modern, fresh feel; "much better" visual experience; smoother performance |
| **Livestock Management** | Not addressed | **Integrated Livestock Module**: Animal inventory, vaccination schedules, breeding records, health history, linked to veterinary services | Serves 76.5% of farmers who own livestock; major feature gap in original |
| **Code Quality & Testing** | Basic PHPUnit tests; manual QA | **Comprehensive Testing**: >80% coverage (PHPUnit + Flutter tests); integration tests (Postman/Newman); E2E tests (Maestro); SAST/DAST in CI/CD; dependency scanning | Production reliability; faster releases; reduced regression bugs |
| **Developer Experience** | Basic API docs | **Developer Portal**: OpenAPI 3.1 spec; interactive API explorer; SDK generation; webhook management; rate limit visibility; partner onboarding flow | Enables third-party integrations; cooperative white-labeling; ecosystem growth |
| **Multi-Language** | English + Swahili (basic) | **Full i18n**: English, Swahili (100%); French (Kinyarwanda expansion); Luganda (Uganda); extensible framework for additional languages | Rwanda French support critical; local language adoption driver |
| **EAC Cross-Border** | Phase 4 expansion item | **Built-in Cross-Border**: Multi-currency support from day 1; customs documentation; cross-border logistics; EAC trade compliance awareness; harmonized product standards | Positions as regional platform; taps into $680M+ KE-TZ trade corridor |

---

## 8. Requirement Traceability Matrix

| Requirement ID | Original Doc Reference | User Request Trigger | Priority | Est. Effort |
|---------------|----------------------|---------------------|----------|-------------|
| EF-001 | Section 3 (Marketplace) | "agrodealer marketplace" | Must-Have | 6 weeks |
| EF-002 | Section 6.1 (Scanner) | "plant disease scanner" | Must-Have | 8 weeks |
| EF-003 | Section 3 (Community) | "faremers forum" | Must-Have | 5 weeks |
| EF-004 | -- | "services (agronomist)" | Must-Have | 4 weeks |
| EF-005 | Section 3 (Logistics) | "services (Logistics & transport)" | Must-Have | 5 weeks |
| EF-006 | Section 3 (Warehouse) | "services (warehouse)" | Must-Have | 3 weeks |
| EF-007 | -- | "services (verterinary help)" | Must-Have | 4 weeks |
| EF-008 | -- | "services (soil testing)" | Must-Have | 4 weeks |
| EF-009 | -- | "lets call the app MkulimaForum" | Must-Have | 1 week |
| IF-001 | Section 3.3 | Implicit (auth necessity) | Must-Have | 2 weeks |
| IF-002 | Section 2.2 (RBAC) | Implicit (multi-role) | Must-Have | 2 weeks |
| IF-003 | Section 7.4 | Implicit (rural connectivity) | Must-Have | 4 weeks |
| IF-004 | Section 6.3 | Implicit (literacy) | Must-Have | 3 weeks |
| IF-005 | Risk table (USSD) | Implicit (feature phones) | Should-Have | 3 weeks |
| IF-006 | Section 9.1 | Implicit (real-time) | Must-Have | 2 weeks |
| IF-007 | Section 8.2 | Implicit (marketplace trust) | Must-Have | 3 weeks |
| IF-008 | Section 3.3 + 10.2 | Implicit (compliance) | Must-Have | 3 weeks |
| IF-009 | Database schema | Implicit (trust economy) | Must-Have | 1 week |
| IF-010 | Section 3.2 | Implicit (discovery) | Must-Have | 2 weeks |
| IF-011 | Section 2.2 + 7.3 | Implicit (regional) | Must-Have | 2 weeks |
| IF-012 | Section 9.1 | Implicit (logistics) | Must-Have | 2 weeks |
| IF-013 | Section 6.2 | Implicit (weather dependency) | Must-Have | 2 weeks |
| IF-014 | -- | Implicit ("much better") | Must-Have | 4 weeks |
| IF-015 | Section 6.1 | Implicit (bandwidth) | Must-Have | 1 week |
| IF-016 | Section 8.2 | Implicit (monetization) | Must-Have | 2 weeks |
| IF-017 | Phase 2 roadmap | Implicit (operations) | Must-Have | 4 weeks |
| IF-018 | Phase 4 roadmap | Implicit (data-driven) | Should-Have | 2 weeks |
| IF-019 | Risk table (SMS) | Implicit (reach) | Should-Have | 1 week |
| IF-020 | Section 7.3 (UI) | Implicit ("modern") | Should-Have | 2 weeks |
| IF-021 | Phase 4 (expansion) | Implicit (EAC market) | Could-Have | 4 weeks |
| IF-022 | Risk table (PWA) | Implicit (accessibility) | Should-Have | 3 weeks |
| IF-023 | -- | Implicit (professional) | Should-Have | 1 week |
| IF-024 | -- | Implicit (community safety) | Should-Have | 2 weeks |
| IF-025 | -- | Implicit (veterinary services) | Should-Have | 2 weeks |

---

## 9. Key Assumptions & Constraints

| ID | Assumption/Constraint | Type | Impact |
|----|----------------------|------|--------|
| **AC-001** | Smartphone penetration in target countries will reach 55%+ by end of 2026 | Assumption | PWA and USSD become secondary channels; native app is primary |
| **AC-002** | Mobile money APIs (M-Pesa, Airtel Money, MTN MoMo) maintain current integration documentation and sandbox availability | Assumption | Payment integration effort estimates valid |
| **AC-003** | Google Gemini API remains available and competitively priced in Africa | Assumption | AI features cost projections valid; OpenAI fallback required |
| **AC-004** | TARI/KALRO/NARO/RAB willing to provide verified agricultural content for RAG knowledge base | Assumption | AI advice quality depends on institutional partnerships |
| **AC-005** | Flutter 3.24+ Impeller engine is production-stable on Android (primary target) | Assumption | UI performance targets achievable |
| **AC-006** | Average rural network speed >= 50kbps (2G/Edge minimum) | Constraint | Offline-first architecture is non-negotiable |
| **AC-007** | Average target device: 2-4GB RAM, 32-64GB storage, Android 10+ | Constraint | App size and memory optimization critical |
| **AC-008** | TFRA/PCPB/UNADA provide API or manual verification process for agrodealer licenses | Constraint | KYC verification depends on regulatory body cooperation |
| **AC-009** | Development team has Laravel 11 + Flutter 3.x expertise | Constraint | Training time needed if skills gap exists |
| **AC-010** | Budget for third-party APIs: AI (Gemini), SMS (Africa's Talking), Maps (Google Cloud), Storage (GCS) | Constraint | Operational costs scale with user growth |

---

## 10. Risk Summary (Requirements Perspective)

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| **AI Diagnosis Hallucination** | Medium | High | RAG grounding; confidence thresholds; human agronomist review queue; disclaimer system |
| **Feature Phone User Exclusion** | High | High | USSD channel; SMS alerts; PWA for low-end smartphones; IVR voice menu |
| **Mobile Money API Instability** | Medium | Critical | Multi-provider support per country; provider fallback; graceful degradation to cash-on-delivery |
| **Rural Network Unreliability** | High | High | Aggressive offline caching; on-device ML; queued operations; SMS fallback |
| **Regulatory Compliance Changes** | Medium | Medium | Modular regulatory plugin architecture; per-country compliance configuration; legal advisory |
| **Data Privacy Regulations** | Medium | High | Data sovereignty design; encryption at rest/transit; consent management; right to deletion |
| **Low Digital Literacy** | High | Medium | Voice-first interface; simple iconography; video tutorials; USSD for basic features; community champion program |
| **Competitor Pre-emption** | Medium | Medium | Rapid MVP launch (3 months); community-first growth; extension officer partnership network |

---

*Document prepared for MkulimaForum Architecture Team*
*Analysis based on: Original Mkulima Super App Architecture v1.0 + User Enhancement Request*
*Regional data sourced from: Africa Mobile Money Market Reports 2025, CGIAR Agricultural Research, EAC Trade Statistics*
*Technical research: Flutter 3.24 Release Notes, Laravel 11 Documentation, Plant Disease Detection Academic Literature 2024-2025*
