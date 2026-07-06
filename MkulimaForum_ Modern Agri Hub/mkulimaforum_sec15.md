## 15. Development Roadmap & Milestones

MkulimaForum's evolution from Tanzania Minimum Viable Product (MVP) to East African Community (EAC) regional platform follows an 18-month, four-phase roadmap. Each phase delivers a self-releasing capability increment that generates measurable value before the next expansion begins. The roadmap addresses the $18.42B East African agritech opportunity, targeting the 40% post-harvest loss rate that costs the region an estimated $4.5B annually [^78^], while closing the extension officer deficit (1:1,172 in Tanzania; 1:1,380 in Kenya vs. the FAO standard of 1:400) [^46^].

### 15.1 Phase 1 — Tanzania MVP (Months 1–4)

Phase 1 proves product-market fit with Tanzania's farming population. The foundation layer implements multi-tenant Laravel 13 on AWS af-south-1 with country-scoped PostgreSQL Row-Level Security (RLS), pgvector for AI embeddings, and Meilisearch for marketplace discovery. Authentication combines OTP via SMS with WebAuthn biometric passkeys. KYC integrates TFRA (Tanzania Fertilizer Regulatory Authority) agrodealer verification, and RBAC supports all eight platform user roles.

MVP features span four pillars: Marketplace (listing, search, cart, escrow checkout via M-Pesa and Tigo Pesa); Disease Scanner (TensorFlow Lite MobileNetV3-Small, 2.5 MB, 10 diseases offline with Gemini Vision fallback); Forum (threads, posts, community moderation); and AI Agronomist (RAG over TARI's knowledge base, which reached 3,396,334 stakeholders in 2024/25 and is investing TZS 11.4 billion in digital repository infrastructure through 2029) [^62^] [^11^].

Success criteria: 10,000 registered users, 100 verified agrodealers, 5,000 disease scans, 500 daily forum posts, 99.5% uptime, and cold launch under 2 seconds on a Tecno Spark 10 — representative of the 41.8% smartphone penetration segment in Tanzania [^208^].

### 15.2 Phase 2 — Services & Kenya (Months 5–8)

Phase 2 launches the unified services marketplace and Kenya expansion. Five verticals — Agronomist Booking, Logistics (boda-to-truck), Warehouse, Veterinary, and Soil Testing — share a common booking engine, payment flow, and review system, addressing the finding that 30% of Tanzanian farmers live more than one hour from the nearest agro-dealer [^81^]. Each service implements a three-tier provider classification (basic, verified, premium) with commission structures defined in Chapter 9.

Kenya entry requires M-Pesa Daraja 3.0 integration (12,000 TPS capacity) [^1^], KEPHIS/PCPB compliance, KALRO knowledge localization for maize and dairy, and bilingual Swahili-English support. The Voice Service Layer launches with Whisper fine-tuned for Swahili (~17% WER) [^4^] and Google Cloud Text-to-Speech, enabling voice-first marketplace search and disease reporting. The Progressive Web App ships with full offline capability supporting 72 hours of queued operations.

### 15.3 Phase 3 — Uganda, Rwanda & Advanced AI (Months 9–12)

Phase 3 expands to Uganda and Rwanda while advancing the AI stack. Both markets require MTN MoMo API integration, partnerships with NARO (Uganda) and RAB (Rwanda), and language localization — French for Rwanda and Luganda for Uganda. Country-specific disease models target banana diseases (affecting 75% of Ugandan farming households) [^142^] and coffee leaf rust (Rwanda, where agriculture employs 43.7% of the workforce) [^197^].

The AI stack adds a fine-tuned agricultural LLM (Mistral-7B via QLoRA), expanding the scanner to 20 diseases with field-condition accuracy improvements through active learning. Sentinel-2 integration enables NDVI monitoring at 10-meter resolution. The SACCO module launches — targeting the 60%+ of Tanzanian SACCOS already digitized [^65^] — with member registration, share tracking, and bulk ordering.

### 15.4 Phase 4 — Scale & EAC Integration (Months 13–18)

Phase 4 transforms MkulimaForum into a true EAC regional marketplace. Onafriq payment rails (1 billion+ mobile wallets across 40 African markets) enable interoperable settlement between M-Pesa, MTN MoMo, and Airtel Money, supported by PAPSS for wholesale clearing [^240^]. Digital customs documentation addresses EAC Common Market Protocol requirements. Regional disease surveillance aggregates anonymized scanner data for early-warning alerts on threats like fall armyworm, which causes up to $13 billion in annual losses across sub-Saharan Africa [^59^].

Scale targets: 100,000 MAU, 500 verified agrodealers, 500 active service providers, 50,000 monthly scans, $500K monthly GMV, 15,000+ SACCO members, and 99.9% uptime.

| Phase | Duration | Key Deliverables | Success Criteria | Dependencies |
|:---|:---|:---|:---|:---|
| 1 — Tanzania MVP | Months 1–4 | Core platform (auth, KYC, RBAC, multi-tenancy); Marketplace with escrow; Disease Scanner (10 diseases, TF Lite + Gemini); Forum; AI Agronomist (RAG + TARI) | 10,000 users; 100 verified agrodealers; 5,000 scans; 500 daily posts; 99.5% uptime; <2s launch on Tecno Spark 10 | AWS af-south-1; TARI API access; M-Pesa/Tigo Pesa sandbox approval |
| 2 — Services & Kenya | Months 5–8 | 5 service verticals; Kenya launch (M-Pesa Daraja 3.0, KEPHIS/PCPB); Voice Service Layer; PWA offline-first | 35,000 users; 250 agrodealers; 200 service providers; 15,000 monthly scans | Phase 1; KALRO partnership; Whisper Swahili data |
| 3 — UG/RW & AI | Months 9–12 | UG/RW launch (MTN MoMo); NARO/RAB partnerships; Fine-tuned LLM; 20-disease scanner; Sentinel-2 NDVI; SACCO module | 65,000 MAU; 400 agrodealers; 350 providers; 30,000 scans; 5,000 SACCO members | Phase 2; QLoRA pipeline; MTN MoMo go-live |
| 4 — EAC Integration | Months 13–18 | Onafriq cross-border; PAPSS clearing; Digital customs; Cross-border logistics; Regional disease surveillance | 100,000 MAU; 500 agrodealers; 500 providers; 50,000 scans/mo; $500K GMV/mo; 99.9% uptime | Phase 3; Onafriq integration; EAC regulatory approval |

The four-phase sequencing reflects deliberate risk mitigation. Phase 1 validates farmer willingness to purchase inputs through escrow-protected digital payments. Phase 2 tests service marketplace network effects — each booked agronomist or logistics provider increases platform stickiness through the data flywheel described in Chapter 9. Phase 3 proves adaptability across regulatory, linguistic, and agricultural contexts. Only in Phase 4, once each national market demonstrates positive unit economics, does the platform absorb cross-border trade complexity. This sequencing aligns with the EAC Cross-Border Payment System Masterplan 2025, which envisions a regional instant retail payment switch over five years [^3^], positioning MkulimaForum to leverage that infrastructure as it matures.

### Gantt-Style 18-Month Timeline

```
Phase / Month: |  1   2   3   4  |  5   6   7   8  |  9  10  11  12  | 13  14  15  16  17  18 |
               |----+----+----+---|----+----+----+---|----+----+----+---|----+----+----+----+----+---|
PHASE 1: TZ MVP|████████████████|                 |                 |                       |
  Core Platform|████████████████|                 |                 |                       |
  Marketplace  |    ████████████|                 |                 |                       |
  Disease Scan |        ████████|                 |                 |                       |
  Forum        |    ████████████|                 |                 |                       |
  AI Agronomist|        ████████|                 |                 |                       |
  PWA (basic)  |            ████|                 |                 |                       |
               |----+----+----+---|----+----+----+---|----+----+----+---|----+----+----+----+----+---|
PHASE 2: SVCS  |                 |████████████████|                 |                       |
  5 Services   |                 |████████████████|                 |                       |
  Voice AI     |                 |    ████████████|                 |                       |
  Kenya Launch |                 |        ████████|                 |                       |
  PWA offline  |                 |            ████|                 |                       |
               |----+----+----+---|----+----+----+---|----+----+----+---|----+----+----+----+----+---|
PHASE 3: UG/RW |                 |                 |████████████████|                       |
  UG+RW Launch |                 |                 |    ████████████|                       |
  Adv. AI/LLM  |                 |                 |████████████████|                       |
  SACCO Module |                 |                 |        ████████|                       |
  NDVI/Sat     |                 |                 |    ████████████|                       |
               |----+----+----+---|----+----+----+---|----+----+----+---|----+----+----+----+----+---|
PHASE 4: EAC   |                 |                 |                 |████████████████████████|
  Onafriq/XB   |                 |                 |                 |████████████████████████|
  PAPSS Clear  |                 |                 |                 |    ████████████████████|
  Cross-border |                 |                 |                 |        ████████████████|
  Disease Surv.|                 |                 |                 |████████████████████████|
               |----+----+----+---|----+----+----+---|----+----+----+---|----+----+----+----+----+---|

Key Milestones:
  [M3]  Alpha release — internal testing
  [M4]  MVP public launch (Tanzania)
  [M6]  Services marketplace live
  [M8]  Kenya public launch + Voice AI
  [M10] Uganda public launch
  [M11] Rwanda public launch + Fine-tuned LLM
  [M12] SACCO module + Sentinel-2 NDVI
  [M14] Onafriq cross-border payments live
  [M16] Full EAC Common Market compliance
  [M18] 100,000 MAU target / Regional platform
```
