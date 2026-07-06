# MkulimaForum — File Analysis (Phase F)

## File Inventory

| File | Type | Size | Summary |
|------|------|------|---------|
| mkulima_software_architecture_laravel_flutter.md | Architecture Document | ~1,160 lines | Complete software architecture for Mkulima Super App using Laravel 11 + Flutter 3.x targeting East African smallholder farmers |

## Per-File Extraction

### Core Themes
1. **Multi-tenant agricultural super-app** — serving farmers, agrodealers, logistics providers
2. **Laravel 11 backend** — Domain-driven architecture with 6 domains: Auth, Farming, Marketplace, Logistics, Finance, Community
3. **Flutter 3.x frontend** — Clean architecture with BLoC pattern, offline-first design
4. **AI Integration** — Google Gemini Vision for plant disease scanning, weather-based crop recommendations, voice AI agronomist (TTS/STT)
5. **Payment** — M-Pesa and Tigo Pesa mobile money integration with wallet/escrow system
6. **Multi-region** — Tanzania (TZ), Kenya (KE), Uganda (UG), Rwanda (RW)
7. **RBAC** — Spatie Laravel Permission with 6 roles
8. **Real-time logistics** — Bodaboda dispatch via PostGIS, Firebase FCM for notifications

### Key Claims & Technical Decisions
- Laravel over Node.js for ORM, queue/scheduler, PHP 8.3 type safety
- Flutter over React Native for offline support via Hive, single codebase
- PostgreSQL over MySQL for PostGIS, JSONB, row-level security
- Sanctum over Passport for lighter mobile app auth
- Open-Meteo (free) over paid weather APIs
- Firebase FCM over OneSignal for cost

### Data Points
- Commission structure: Marketplace 3-5%, Logistics 10%, Tool Rental 8%
- AI confidence threshold: 70% for diagnosis
- Rate limits: Auth 5/min, AI 10/hr, Browse 100/min
- Token lifetime: Access 24h, Refresh 30 days
- Target: 10k concurrent users in load testing

### Limitations & Gaps Identified
1. **No dedicated Farmers Forum module** — basic forum threads exist but no rich community features
2. **No Agrodealer Marketplace** — generic product catalog, not specialized for agro-inputs
3. **No Plant Disease Scanner as standalone** — exists as AI pipeline but no dedicated module architecture
4. **Missing Services module** — no architecture for agronomist, veterinary, soil testing services
5. **No warehouse management system** — only basic warehouse booking
6. **No logistics & transport marketplace** — basic bodaboda dispatch only
7. **Limited East African context** — generic architecture, not deeply localized
8. **No USSD fallback** — critical for feature phone users in rural areas
9. **No cooperative/SACCO integration** — important for farmer financing
10. **No crop insurance integration** — mentioned as model but not architected
11. **No e-extension/officer module** — government extension workers not included
12. **Modern AI/ML stack not specified** — no vector DB, no RAG, no fine-tuning pipeline
13. **No PWA architecture** — web fallback not detailed
14. **Cold chain logistics missing** — critical for perishables

## Consolidated Theme List

1. Platform Identity & East African Context
2. Agrodealer Marketplace Architecture
3. Farmers Forum & Community Platform
4. Plant Disease Scanner (AI/ML Module)
5. Services Marketplace (Agronomist, Veterinary, Soil Testing)
6. Logistics & Transport Platform
7. Warehouse Management System
8. Modern AI/ML Stack (RAG, Vector DB, Fine-tuning)
9. Offline-First & Low-Connectivity Architecture
10. Payment & Financial Services (Mobile Money, Cooperatives, Insurance)
11. Regulatory Compliance & Data Sovereignty
12. Security Architecture & Multi-tenancy
13. DevOps, Scaling & Deployment
14. Veterinary & Livestock Services
