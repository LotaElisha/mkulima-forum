## 3. Platform Overview — All MkulimaForum Modules

MkulimaForum comprises five functional pillars — Agrodealer Marketplace, Plant Disease Scanner, Farmers Forum, AI Agronomist, and Services Marketplace — unified by shared services and bound through a cross-module data flywheel. Where existing platforms address single pain points (DigiFarm for inputs [^166^], FarmerChat for advice [^216^], Sokofresh for cold storage [^149^]), MkulimaForum's integration reflects the farmer's continuous workflow: soil testing informs planting, disease detection triggers purchases, and harvest requires logistics — with every interaction refining AI recommendations.

### 3.1 System Modules Map

#### 3.1.1 Module Interaction Overview

The five pillars exchange data through event-driven pipelines. A farmer photographing a diseased leaf receives both a TensorFlow Lite diagnosis and a ranked list of verified fungicides. Soil test NPK data feeds the AI Agronomist's fertiliser engine. Forum discussions resolved by experts are ingested into the RAG knowledge base. This embodies Insight 8: the services layer creates ecosystem lock-in where each interaction generates data that improves recommendations for all users [^170^].

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                        MKULIMAFORUM MODULE INTERACTION MAP                       │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│   ┌──────────────┐    diagnosis     ┌──────────────┐    product link      ┌──────┴───────┐
│   │   PLANT      │ ───────────────► │ AGRODEALER   │ ─────────────────► │  SERVICES    │
│   │   DISEASE    │                  │ MARKETPLACE  │                    │ MARKETPLACE  │
│   │   SCANNER    │ ◄─────────────── │              │ ◄──── inventory    │              │
│   └──────┬───────┘   purchase need  └──────┬───────┘      booking       └──────┬───────┘
│          │                                   │                                   │
│          │ confidence score                  │ order status                      │ booking
│          ▼                                   ▼                                   ▼
│   ┌──────────────┐    alert threads    ┌──────────────┐    consultation     ┌──────────────┐
│   │   FARMERS    │ ───────────────► │   SHARED     │ ◄───────────────  │   AI         │
│   │   FORUM      │ ◄─────────────── │   SERVICES   │    RAG query      │ AGRONOMIST   │
│   │              │   knowledge base  │   LAYER      │                   │              │
│   └──────────────┘                   └──────┬───────┘                   └──────┬─────────┘
│          ▲                                  │                                  │
│          │ forum posts                      │ weather, GPS,                    │ soil data,
│          │                                  │ payments, notifications          │ market prices
│          └──────────────────────────────────┘◄─────────────────────────────────┘
│                                                                                 │
│   ┌──────────────┐  disease reports  ┌─────────────────────┐                  │
│   │  DISEASE     │ ────────────────► │   REGIONAL ALERT    │                  │
│   │  SCANNER     │◄───────────────── │   ENGINE            │                  │
│   │  (feedback)  │  product links    │   (pest/price)      │                  │
│   └──────────────┘                   └─────────────────────┘                  │
│                                                                                 │
│   FLOW: ──► primary  ◄──► bidirectional  ─ ─► event-driven                    │
└─────────────────────────────────────────────────────────────────────────────────┘
```

#### 3.1.2 Eight User Roles with Granular RBAC

RBAC via Spatie Laravel Permission defines eight roles mapped to East African agricultural actors [^65^]. The `farmer` browses, buys, scans diseases, and books services. The `agrodealer` manages catalogs and escrow orders; onboarding requires a TFRA, KEPHIS, PCPB, or RAB license [^58^]. The `agronomist` provides consultations and curates the RAG knowledge base. The `veterinary_officer` offers tele-vet — critical with 50% of veterinary posts vacant in Uganda [^143^]. The `logistics_provider` and `warehouse_operator` handle delivery and storage respectively. The `extension_officer` publishes advisories, reflecting the extension deficit (1:1,172 in Tanzania, 1:1,380 in Kenya versus FAO's 1:400) [^51^] [^49^]. The `admin` holds full system access.

| Role | Marketplace | Disease Scanner | Farmers Forum | AI Agronomist | Services Marketplace |
|:---|:---|:---|:---|:---|:---|
| farmer | browse, buy, review | scan, view history | post, reply, vote, voice-note | query, voice I/O | book, cancel, review |
| agrodealer | list, manage stock, fulfill, analytics | — | reply (verified badge) | — | accept booking |
| agronomist | — | validate diagnoses | moderate, expert badge, pin answers | manage RAG docs | accept booking, chat/video |
| veterinary_officer | — | — | reply (vet badge) | — | accept booking, tele-vet |
| logistics_provider | — | — | — | — | accept delivery, GPS track |
| warehouse_operator | — | — | — | — | manage space, IoT dashboard |
| extension_officer | view aggregate sales | view regional alerts | publish advisories | upload official docs | schedule campaigns |
| admin | full CRUD + escrow | full CRUD + model mgmt | full CRUD + moderation | full CRUD + KB mgmt | full CRUD + vetting |

The matrix follows least privilege and expert verification hierarchy: agronomists and veterinary officers hold elevated forum privileges because farmer trust correlates with responder authority — Digital Green found 70% of farmers applied verified recommendations within 30 days [^188^]. Extension officers access only aggregate data, aligning with Tanzania's PDPA (2022) and Kenya's DPA (2019).

#### 3.1.3 Cross-Module Data Flywheel

The flywheel compounds value through five loops. *Soil-to-crops*: NPK readings calibrate fertiliser advice. *Disease-to-products*: diagnoses link to verified treatments; aggregated GPS patterns generate regional pest alerts. *Purchases-to-personalisation*: order history feeds collaborative filtering. *Forum-to-RAG*: resolved discussions expand the knowledge base. *Veterinary-to-livestock*: consultation records improve livestock advice. Each loop creates a network effect that single-feature competitors cannot replicate [^170^].

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                        CROSS-MODULE DATA FLYWHEEL                               │
│                                                                                 │
│                    ┌─────────────────────┐                                      │
│                    │   SERVICES          │                                      │
│                    │   MARKETPLACE       │                                      │
│                    │   (soil, vet)       │                                      │
│                    └──────────┬──────────┘                                      │
│                               │                                                 │
│              soil data (NPK)  │   vet records                                   │
│                               ▼                                                 │
│   ┌──────────────┐    ┌─────────────────────┐    ┌──────────────┐             │
│   │  FARMERS     │◄───│      AI             │◄───│  MARKETPLACE │             │
│   │  FORUM       │    │   AGRONOMIST        │    │  (purchase   │             │
│   │  (resolved   │───►│   (RAG + Gemini)    │───►│  history)    │             │
│   │   threads)   │    └──────────▲──────────┘    └──────┬───────┘             │
│   └──────┬───────┘               │                      │                      │
│          │ forum posts           │ RAG enrichment       │ collaborative        │
│          │                       │                      │ filtering            │
│          └───────────────────────┘◄─────────────────────┘                      │
│                                                                                 │
│   ┌──────────────┐  disease reports  ┌─────────────────────┐                  │
│   │  DISEASE     │ ────────────────► │   REGIONAL ALERT    │                  │
│   │  SCANNER     │◄───────────────── │   ENGINE            │                  │
│   │  (feedback)  │  product links    │   (pest/price)      │                  │
│   └──────────────┘                   └─────────────────────┘                  │
│                                                                                 │
│   Sources: TARI, KALRO, NARO, RAB [^62^] [^58^] [^143^]                        │
└─────────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Module Specifications

**Agrodealer Marketplace.** Multi-vendor e-commerce for seeds, fertilisers, pesticides, and tools with compliance flags (TFRA/KEPHIS/PCPB/RAB-verified). Meilisearch powers Swahili-aware search. Escrow checkout holds funds until delivery confirmation, addressing the trust deficit identified as the primary barrier to digitization [^14^]. Commission: 3–5%.

**Plant Disease Scanner.** Hybrid AI: TensorFlow Lite MobileNetV3-Small (2.5 MB, 20 diseases) for offline diagnosis, with Gemini Vision fallback below 70% confidence. Targets Fall Armyworm ($13 billion losses [^59^]), Banana Xanthomonas Wilt (30–100% losses [^116^]), Coffee Leaf Rust (83–97% infection rate [^167^]), and Maize Lethal Necrosis [^58^]. Severity estimation and marketplace linking complete the workflow.

**Farmers Forum.** Threaded discussions with rich media, expert badges, and RAG-powered FAQ. Voice-note posts target the 41.8% of Tanzanian farmers with smartphones but limited text literacy [^208^]. Sub-forums scoped to agro-ecological zones [^62^] ensure local relevance.

**AI Agronomist.** RAG assistant using Gemini 2.0 Flash ($0.075/1M tokens) with pgvector knowledge base from TARI, KALRO, NARO, and RAB [^62^] [^58^] [^143^]. Voice I/O in Swahili (expanding to Luganda, Kinyarwanda). Recommendations synthesise soil, weather, and price data.

**Services Marketplace.** Six categories: (1) *Agronomist consultation*; (2) *Logistics* with Mapbox GPS tracking, modelled on iProcure's 94% fill rate [^151^]; (3) *Warehouse* with IoT and blockchain receipts; (4) *Veterinary* tele-vet [^152^]; (5) *Soil Testing* in three tiers (AI-estimated, sample collection, lab analysis); (6) *Cold Storage* at ~KES 1–2/Kg/Day, targeting 40% post-harvest losses ($4.5 billion annually) [^78^].

### 3.3 Shared Platform Services

#### 3.3.1 Cross-Cutting Service Layer

Authentication via phone OTP (Africa's Talking/Twilio) with optional biometric fallback. KYC uses four-tier vetting: Tier 1 (national ID), Tier 2 (business registration + manufacturer cert), Tier 3 (facility inspection + IoT), Tier 4 (credentials + licensing board). Wallet escrow holds funds until delivery confirmation. Push notifications via Firebase Cloud Messaging with SMS fallback. Meilisearch for sub-50ms search. Automated content moderation with human escalation.

#### 3.3.2 Infrastructure Services

Weather from Open-Meteo cached hourly per GPS coordinate. Mapbox primary mapping with OpenStreetMap fallback — 46% cheaper than Google Maps at scale. Image compression reduces upload bandwidth, critical as rural data costs remain a barrier [^214^]. i18n supports English and Swahili, expanding to Luganda (2M+ Baganda farmers [^142^]) and Kinyarwanda. USSD via Africa's Talking reaches the 58.2% of Tanzanian subscribers without smartphones [^212^]. PWA support for low-end devices.

| Parameter | Tanzania | Kenya | Uganda | Rwanda |
|:---|:---|:---|:---|:---|
| Primary languages | Swahili, English | Swahili, English | English, Luganda | Kinyarwanda, English, French |
| Currency | TZS | KES | UGX | RWF |
| Mobile money | M-Pesa, Tigo Pesa, Airtel Money | M-Pesa | MTN MoMo, Airtel Money | MTN MoMo, Airtel Money |
| Input regulator | TFRA | KEPHIS | PCPB | RAB |
| Dominant crops | Maize, rice, beans, cassava | Maize, beans, tea, coffee | Banana, maize, cassava | Maize, beans, Irish potatoes [^198^] |
| Research partner | TARI | KALRO | NARO | RAB |
| Extension ratio | 1:1,172 [^51^] | 1:1,380 [^49^] | 1:1,800 [^46^] | ~1:500 |
| Agriculture GDP share | 26.2% [^93^] | 26% [^22^] | ~24% | 22–25% [^197^] |

Cross-country heterogeneity requires infrastructure-layer abstraction. M-Pesa dominates Kenya and Tanzania; MTN MoMo leads Uganda and Rwanda. Product verification requires compliance with four national bodies. Crop profiles dictate scanner priorities: maize lethal necrosis in Kenya [^58^], banana wilt in Uganda [^116^], coffee rust in Rwanda [^167^]. PostgreSQL row-level security by `country_code`, per-country gateway configs, and modular disease model weights per agro-ecological zone create a multi-tenant design that prevents refactoring when expanding from Tanzania (launch market, Swahili-dominant, lower competition) into Kenya, Uganda, and Rwanda.
