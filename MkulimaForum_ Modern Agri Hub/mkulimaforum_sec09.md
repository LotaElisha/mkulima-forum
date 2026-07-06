# 9. Module Deep-Dives (Marketplace, Scanner, Forum, Services)

## 9.1 Agrodealer Marketplace

### 9.1.1 Data Model and Inventory

The marketplace persists five core entities: **products** (JSONB variant specs for NPK ratios, germination rates, active ingredients), **vendors** (TFRA/KEPHIS-licenced agrodealers), **categories** (per-country taxonomies), **inventory** (real-time stock with reorder points), and **orders** (deterministic state machine). Reviews carry a *verified purchase* badge only when `review.user_id` matches a completed order's `buyer_id` — the pattern iProcure demonstrated drives 94% fill rates in verified networks [^151^]. Spatie Media Library generates WebP variants at 320px, 800px, and 1600px, reducing bandwidth 60-80% versus unoptimised JPEGs. When `stock_qty <= reorder_point`, a queued job notifies the vendor and flags the product as low-stock in search results. Seasonal pricing rules — pre-planting discounts triggered by crop calendar transitions (Vuli rains October-January, Masika March-June in Tanzania [^62^]) — run via a scheduled command at season boundaries.

### 9.1.2 Order State Machine

Every transaction follows an escrow-protected lifecycle addressing the trust gap: farmers fear counterfeit inputs and non-delivery, while dealers fear credit default [^151^] [^160^]. The state machine encodes automatic refund eligibility at each cancellation point.

```
                          ORDER STATE MACHINE
    =====================================================================

    [CART] ──checkout()──► [CHECKOUT] ──payment_hold()──► [PAYMENT_CONF]
                                                               │
         cancel() ──► [CANCELLED] + full refund              │ process()
         (within 30 min)                                     ▼
                                                          [PROCESSING]
                                                               │
         cancel() ──► [CANCELLED] + 100% refund              │ ship()
         (before vendor ships)                                ▼
                                                          [SHIPPED]
                                                               │
         (GPS tracking active)                                 │ deliver()
         cancel() ──► [RETURN_INITIATED]                       ▼
         + refund on receipt                            [DELIVERED]
                                                               │
         confirm() ──► [ESCROW_RELEASED] ◄── auto-confirm(48h)
                          │
                          ▼
                    [COMPLETED]
                          │
         dispute() ──► [DISPUTED] ──► resolve() ──► [REFUNDED]
         (within 72h)        │                         or release
                            │
                            └────► [RETURN_INITIATED] (from SHIPPED)
```

**Table 1: Order State Transitions and Refund Rules**

| State | Enter Action | Cancellation Rule | Refund % | Auto-trigger |
|---|---|---|---|---|
| Cart | Item persistence | Silent delete | N/A | 7-day expiry |
| Checkout | Escrow hold via STK push | Full refund within 30 min | 100% | 15-min payment timeout |
| Payment Confirmed | Vendor SMS notify | Full refund before processing | 100% | 24-h processing SLA |
| Processing | Inventory lock | Refund on vendor approval | 100% | Vendor SLA breach |
| Shipped | GPS tracking on | Return initiation | 100% on receipt | 72-h delivery SLA |
| Delivered | Photo proof required | Return within 48 h | 100% after inspection | 48-h auto-confirm |
| Escrow Released | Commission computed | N/A | N/A | Immediate disbursement |
| Completed | Review invitation | N/A | N/A | 14-day review window |

The `Order` model implements transitions via a PHP enum with guard clauses:

```php
<?php
namespace App\Domains\Marketplace\Enums;

enum OrderStatus: string
{
    case CART = 'cart';
    case CHECKOUT = 'checkout';
    case PAYMENT_CONFIRMED = 'payment_confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case ESCROW_RELEASED = 'escrow_released';
    case COMPLETED = 'completed';
    case DISPUTED = 'disputed';
    case CANCELLED = 'cancelled';
    case RETURN_INITIATED = 'return_initiated';

    public function transition(OrderAction $action, ?\DateTimeImmutable $at = null): ?self
    {
        return match ($this) {
            self::CART => match ($action) {
                OrderAction::CHECKOUT => self::CHECKOUT,
                OrderAction::EXPIRE => self::CANCELLED,
                default => null,
            },
            self::CHECKOUT => match ($action) {
                OrderAction::PAYMENT_HOLD => self::PAYMENT_CONFIRMED,
                OrderAction::CANCEL => $this->withinMinutes($at, 30)
                    ? self::CANCELLED : null,
                OrderAction::PAYMENT_TIMEOUT => self::CANCELLED,
                default => null,
            },
            self::PAYMENT_CONFIRMED => match ($action) {
                OrderAction::PROCESS => self::PROCESSING,
                OrderAction::CANCEL => self::CANCELLED,
                OrderAction::VENDOR_SLA_BREACH => self::CANCELLED,
                default => null,
            },
            self::PROCESSING => match ($action) {
                OrderAction::SHIP => self::SHIPPED,
                OrderAction::CANCEL => self::CANCELLED,
                default => null,
            },
            self::SHIPPED => match ($action) {
                OrderAction::DELIVER => self::DELIVERED,
                OrderAction::CANCEL => self::RETURN_INITIATED,
                default => null,
            },
            self::DELIVERED => match ($action) {
                OrderAction::CONFIRM => self::ESCROW_RELEASED,
                OrderAction::AUTO_CONFIRM => $this->withinHours($at, 48)
                    ? self::ESCROW_RELEASED : null,
                OrderAction::DISPUTE => $this->withinHours($at, 72)
                    ? self::DISPUTED : null,
                default => null,
            },
            self::ESCROW_RELEASED => match ($action) {
                OrderAction::COMPLETE => self::COMPLETED,
                default => null,
            },
            self::DISPUTED => match ($action) {
                OrderAction::RESOLVE_RELEASE => self::ESCROW_RELEASED,
                OrderAction::RESOLVE_REFUND => self::CANCELLED,
                default => null,
            },
            default => null,
        };
    }

    private function withinMinutes(?\DateTimeImmutable $at, int $m): bool {
        return $at !== null && (new \DateTimeImmutable())->diffInMinutes($at) <= $m;
    }
    private function withinHours(?\DateTimeImmutable $at, int $h): bool {
        return $at !== null && (new \DateTimeImmutable())->diffInHours($at) <= $h;
    }
}
```

Funds are held in platform escrow at `PAYMENT_CONFIRMED` and released to the vendor only on farmer confirmation or auto-confirm after 48 hours. Commission of 3-5% (category-varying: seeds at 3%, crop protection at 5%) is deducted at escrow release, with monthly disbursement to vendor mobile money wallets on the 5th of each month, minimum threshold TZS 50,000.

---

## 9.2 Plant Disease Scanner — Hero Feature

### 9.2.1 Scanner Architecture

The scanner is MkulimaForum's primary acquisition channel, exploiting the "wow factor" of instant visual diagnosis [^2^]. The six-stage pipeline targets low-end Android devices (41.8% smartphone penetration in Tanzania) [^208^].

```
                    DISEASE SCANNER UX PIPELINE
    ================================================================

    [1] CAPTURE          Flutter Camera + 6-leaf reticle (88% acc) [^2^]
         ▼
    [2] COMPRESSION      Progressive JPEG, <= 2 MB, 224x224 input
         ▼
    [3] ON-DEVICE        TF Lite + NNAPI delegate
         INFERENCE       MobileNetV3-Small INT8 (2.5 MB)
         ▼               Inference < 50 ms on 1 GB RAM
    [4] POST-PROCESS     NMS + confidence threshold, top-3 ranked
         ▼
    [5] CONFIDENCE       > 70% ──► [LOCAL RESULT + marketplace links]
         BRANCH          50-70% ──► [GEMINI VISION second opinion]
                         < 50% ──► [HUMAN AGRONOMIST QUEUE, 4h SLA]
```

**Table 2: Disease Scanner Coverage (20-Disease Target Set)**

| # | Disease | Crop | On-Device | Lab Acc. | Field-Adj. |
|---|---------|------|-----------|----------|------------|
| 1 | Cassava Mosaic Disease | Cassava | Yes | 93% [^2^] | 70-79% |
| 2 | Cassava Brown Streak | Cassava | Yes | 73% [^2^] | 55-65% |
| 3 | Cassava Green Mite | Cassava | Yes | 93% [^2^] | 70-79% |
| 4 | Fall Armyworm | Maize | Yes | 85% | 60-75% |
| 5 | Maize Lethal Necrosis | Maize | Yes | 80% [^58^] | 60-70% |
| 6 | Maize Streak Virus | Maize | Yes | 82% | 62-72% |
| 7 | Gray Leaf Spot | Maize | Yes | 78% | 58-68% |
| 8-13 | Tomato (6 diseases) | Tomato | Yes | 80-88% | 60-76% |
| 14-15 | Early/Late Blight | Potato | Extension | 85-87% | 64-75% |
| 16-17 | BXW, Black Sigatoka | Banana | Extension | 80-82% [^116^] | 60-72% |
| 18-19 | Leaf Rust, Berry Dis. | Coffee | Extension | 79-81% [^167^] | 59-71% |
| 20 | Rice Blast | Rice | Extension | 84% | 63-73% |

The *Field-Adjusted* column applies the 10-40% accuracy degradation that models trained on PlantVillage's controlled images exhibit in real field conditions [^10^]. Confidence thresholds drive the fallback tree: on-device for >70%, Gemini Vision for 50-70%, human agronomist for <50%. Model versioning uses Firebase Remote Config for A/B deployment and hot-swap without restart. Extension modules (5 MB per crop) download on first use, keeping initial install under 15 MB.

### 9.2.2 Cloud Fallback and Active Learning

Gemini Vision provides cloud second opinion at 50-70% confidence, returning disease name, treatment, and TARI protocol identifier via queued Laravel job. Below 50%, cases join an agronomist queue targeting 4-hour SLA — directly addressing the extension officer gap of 1:1,380 in Kenya against FAO's 1:400 standard [^49^]. Farmer thumbs-up/down feedback feeds weekly batch retraining, with agronomist-validated corrections incorporated to close the lab-to-field accuracy gap [^10^]. Diagnosis results link to verified treatment products in the marketplace, and aggregated geohashed data feeds epidemic early-warning dashboards at TARI and KALRO [^62^] [^58^].

---

## 9.3 Farmers Forum & Community

### 9.3.1 Data Model and Expert Verification

Forum organisation follows three axes: **country** (TZ, KE, UG, RW), **crop** (Coffee Corner, Maize Masters, Banana Board), and **topic**. Posts support rich text, inline images, and voice notes transcribed by Whisper fine-tuned for Swahili at ~17% WER [^4^]. Vote tallies use CRDT counters for correct offline-to-online sync.

**Table 3: Service Provider Vetting Tiers**

| Tier | Badge | Verification Requirements | Validity |
|------|-------|--------------------------|----------|
| 1 | `verified_agronomist` | BSc Agriculture+, TFRA/KEPHIS check, peer ref. | 2 years |
| 2 | `verified_veterinary` | TVB/KVB registration, specialisation cert, insurance | 1 year |
| 3 | `verified_agrodealer` | TFRA licence, warehouse inspection, manufacturer auth | 1 year |
| 4 | `top_contributor` | 500+ helpful answers, 4.5+ rating, 6-month tenure | Rolling |

```
                    PROVIDER VETTING PROCESS
    ================================================================

    [APPLICATION] ──► [DOC UPLOAD] ──► [AUTOMATED CHECKS]
         │                                   │
         │                         ┌─────────┴─────────┐
         │                    [LICENCE API]     [WATCHLIST]
         │                    (TFRA/KEPHIS)    (sanctions)
         │                         │                 │
         │                         └────────┬────────┘
         │                                  │
         │                            [SCORE 0-100]
         │                          ┌─────┴─────┐
         │                    < 60 │           │ >= 60
         │                         ▼           ▼
         │                   [REJECTED]  [VIDEO INTERVIEW]
         │                   + appeal        + peer review
         │                                     │
         │                                     ▼
         │                              [BADGE ISSUED]
         │                              + escrow eligibility
         │                                     │
         │                              [MONITORING]
         │                         rating < 4.0: review
         │                         3 flags: suspension
         │                         annual re-verification
```

Each badge renders as a green checkmark with detail sheet (verification date, authority, credential number) — transparency that iProcure showed drives 94% fill rates in verified networks [^151^]. RAG-based *Similar Questions* query pgvector embeddings (28ms p95 at 50M vectors) [^7^] before posting; high-engagement threads are auto-summarised into FAQ entries by Gemini 2.0 Flash at $0.075/1M tokens [^6^]. Country forums operate in local languages with automatic translation; crop forums auto-generate seasonal pinned threads from the crop calendar and Open-Meteo forecasts [^5^].

---

## 9.4 Agronomist Services

### 9.4.1 Booking Engine

Agronomist profiles expose specialisations (crop advisory, soil, pest, irrigation), certifications, coverage polygons (PostGIS), and availability slots. Service types span chat (TZS 5,000-15,000), video (TZS 20,000-50,000), and farm visits (TZS 75,000-150,000).

```
                    SERVICES BOOKING FLOW
    ================================================================

    [FARMER] ──► [SEARCH by crop/topic/lang] ──► [SELECT by rating/price]
                                                              │
                                                              ▼
    [DEPOSIT 20%] ◄── [SCHEDULE from real-time ICS] ◄── [VIEW PROFILE]
         │
         ▼
    [ESCROW HOLD] ──► [CONSULTATION DAY]
                            │
                    ┌───────┴───────┐
                    ▼               ▼
              [VIDEO/VOICE]    [CHAT + IMAGES]
              (WebRTC)         (Firebase RTDB)
                    │
                    ▼
            [CONSULTATION END]
                    │
            ┌───────┴───────┐
            ▼               ▼
    [RELEASE ESCROW]  [FOLLOW-UP NEEDED]
    + AI summary        + prescription
    + rating prompt     + reschedule
                        + follow-up suggestions
```

Real-time availability uses an Interval Conflicts Scheduler (ICS): `SELECT slot FROM availability WHERE provider_id = ? AND NOT EXISTS (SELECT 1 FROM bookings WHERE (start_time, end_time) OVERLAPS (slot_start, slot_end))`. The 20% deposit enters escrow via mobile money STK push; the balance is charged on completion. The consultation room uses WebRTC with TURN fallback, Firebase Realtime Database for chat, and a shared image canvas. Post-consultation, Gemini 2.0 Flash generates a structured summary that the agronomist reviews before releasing.

### 9.4.2 Commission

Agronomist commission is 15%, tiered down for volume: >50/month = 12%, >100 = 10%. The transparent fee breakdown on checkout addresses the extension officer deficit (1:1,380 in Kenya) [^49^] by incentivising private agronomists to serve smallholders through a trusted platform.

---

## 9.5 Logistics & Transport Services

**Table 4: Logistics Vehicle Types and Pricing Parameters**

| Vehicle Type | Capacity | Base Fare/km | Weight Factor | Typical Use |
|---|---|---|---|---|
| Boda boda | 50-100 kg | $0.30 | +$0.10/kg | Seed/fertiliser delivery |
| Tuk-tuk | 200-300 kg | $0.40 | +$0.08/kg | Peri-urban parcels |
| Pickup truck | 1-2 tonnes | $0.80 | +$0.05/kg | Produce collection |
| Lorry (3-10t) | 3-10 tonnes | $1.20 | +$0.03/kg | Bulk harvest transport |
| Refrigerated | 2-5 tonnes | $2.50 | +$0.06/kg | Cold chain: milk, horticulture |

Fare estimation: $\text{Fare} = (d \times r_b) + (w \times r_w) + f_d$, where $d$ = distance from Mapbox Directions, $r_b$ = base rate by vehicle, $w$ = weight, $r_w$ = weight surcharge, $f_d$ = weekly fuel adjustment. Driver verification requires commercial licence, vehicle inspection, background check, and TZS 100,000 refundable deposit.

GPS streams every 5 seconds when moving; Mapbox Map Matching snaps points to roads, correcting for low-end smartphone GPS inaccuracy. Geofenced alerts (500m radius) fire at pickup and delivery. Delivery confirmation requires a farmer-uploaded photo stored in Cloudflare R2 as immutable proof. Commission is 10% (7% platform, 3% driver incentive pool). Performance scoring weights on-time rate (40%), photo compliance (30%), farmer rating (20%), and cancellation rate (10%); drivers above 4.7 earn 1.1-1.3x weekly bonus multipliers.

---

## 9.6 Warehouse Services

### 9.6.1 Data Model, Booking, and IoT

Warehouse records store location (PostGIS), type (grain store, cold storage, silo, hermetic bag depot), capacity, amenities, and seasonal pricing rules that spike 40-60% during harvest windows. Farmers search via `ST_DWithin` (50km default), filtering by capacity, crop compatibility, and price. Mobile money deposit (30%) enters escrow; balance is due on check-in.

**Table 5: Warehouse IoT Thresholds by Crop Type**

| Crop | Temperature | Humidity | Moisture Target | Alert Latency |
|------|-------------|----------|-----------------|---------------|
| Maize grain | < 30°C | < 65% RH | < 13% [^17^] | 5 min |
| Paddy rice | < 28°C | < 70% RH | < 14% | 5 min |
| Irish potatoes | 4-7°C | 85-95% RH | N/A | 2 min |
| Avocado (ripe) | 5-8°C | 85-90% RH | N/A | 2 min |
| Dried beans | < 25°C | < 60% RH | < 15% | 10 min |
| Coffee parchment | 18-22°C | 55-65% RH | 10-12% | 5 min |

Sensors stream via MQTT to a Laravel subscriber; breaches trigger escalating alerts: in-app push at +1°C/+3% RH, SMS at +2°C/+5% RH, emergency call at +3°C/+8% RH. Warehouse receipts comply with Tanzania's Warehouse Receipt System Act 2005 [^15^], anchored to Stellar blockchain for collateral-grade documentation — Silo Africa's SmartSilo model demonstrates this approach reduces post-harvest losses by up to 30% [^17^]. Commission is 5% on storage fees; operators may subscribe at TZS 150,000/month for priority listing, occupancy forecasting, and insurance integration (3-7% of stored value).

---

## 9.7 Veterinary Services

### 9.7.1 Service Model and Tele-Veterinary

Veterinary profiles require TVB/equivalent registration, specialisation tags (large animal, poultry, dairy, equine), coverage radius, and emergency availability. Service types span consultation, farm visit, vaccination, and emergency response. Each consultation updates a livestock health record tied to the animal's NFC/QR ear tag. Remote diagnosis accepts image/video uploads; an AI symptom triage layer (decision tree + Gemini 2.0 Flash) suggests probable conditions before veterinarian review. OTC prescriptions issue through in-app digital RX; controlled medications require physical verification. Vaccination schedules generate from livestock profiles with calendar reminders at 7 days, 1 day, and 4 hours before due dates.

### 9.7.2 Emergency Response

```
                    VETERINARY EMERGENCY RESPONSE
    ================================================================

    [EMERGENCY CALL] ──► [GPS CAPTURE ±50m] ──► [NEAREST VET SEARCH]
                                                      │
                                               PostGIS ST_DWithin
                                               15 km, emergency_available
                                                      │
                                                      ▼
    [PRE-ARRIVAL FIRST AID] ◄──── [VET NOTIFIED push+SMS]
            │                     [ETA COMPUTED < 30 min target]
            │                               │
            │                               ▼
            │                     [VET EN ROUTE, GPS every 60s]
            │                               │
            │                               ▼
            └──────────────── [ARRIVAL: geofence 500m trigger]
                                              │
                                              ▼
                                    [TREATMENT + health record]
                                              │
                                              ▼
                                    [FOLLOW-UP + prescription]
```

The sub-30-minute response target addresses Rwanda's livestock health data showing East Coast Fever (36.8% of cattle diagnoses) and Anaplasmosis (17.4%) require rapid intervention to prevent mortality [^181^]. Pre-arrival first aid instructions display immediately after the emergency call, customised to species and reported symptoms.

---

## 9.8 Soil Testing Services

### 9.8.1 Three-Tier Architecture

```
                    SOIL TESTING 3-TIER ARCHITECTURE
    ================================================================

    TIER 1: INSTANT AI (Free)
    ─────────────────────────
    Input:   GPS coordinates from shamba boundary
    Source:  iSDAsoil REST API, 30 m resolution [^3^]
    Vars:    pH, N, P, K, Ca, Mg, S, Zn, Fe, clay, sand, silt,
             organic C, bulk density (14 total)
    Output:  Crop suitability + fertiliser recommendation
    Latency: < 3 seconds
    Cost:    Free

                    │ upgrade if low confidence
                    ▼
    TIER 2: PHYSICAL COLLECTION
    ───────────────────────────
    Pickup:  Nearest certified lab, within 48h, GPS-tagged
    SLA:     72-hour processing
    Output:  N, P, K, micronutrients, pH, OM, texture, CEC
    Cost:    TZS 15,000-50,000

                    │ recommended for commercial farms
                    ▼
    TIER 3: LAB COMPREHENSIVE
    ─────────────────────────
    Input:   Multi-depth samples (0-20cm, 20-50cm)
    Analysis: Full spectrometry + XGBoost AI blend (99.09% acc) [^22^]
    Output:  14 vars + S, Zn, B, Cu, Fe, Mn, Mo, CEC, salinity
    Cost:    TZS 80,000-150,000 | Turnaround: 5-7 days
```

Tier 1's recommendation engine applies XGBoost models to generate fertiliser prescriptions as Urea + SSP + MoP, DAP + Urea + MoP, and NPK blends with cost comparisons [^22^] [^24^]. Tier 2 lab partners are onboarded with ISO 17025 certification checks, equipment inventory, and staff credential verification. The lab dashboard provides sample queue management, digital results entry with anomaly flagging, QA scoring (replicate consistency and blind reference accuracy), and turnaround monitoring. Labs below 85% QA index receive corrective action plans; persistent underperformance triggers delisting. Automated farmer notification fires on result publication, with results permanently linked to the farm profile.

**Table 6: Services Commission Structure Summary**

| Service | Commission | Tiered Reduction | Deposit | Payout | Key Innovation |
|---|---|---|---|---|---|
| Marketplace | 3-5% | Category-based | 100% upfront | Monthly | Escrow, verified reviews |
| Agronomist | 15% | >50/mo=12%, >100=10% | 20% | Weekly | AI summary, digital RX |
| Logistics | 10% | 1.1-1.3x bonus >4.7 rating | 100% | Weekly | GPS, photo proof |
| Warehouse | 5% | Subscription tier | 30% | Monthly | IoT alerts, blockchain receipts |
| Veterinary | 12% | Emergency +20% premium | 100% | Weekly | < 30 min emergency response |
| Soil Testing | 8% | None | 100% | Per-test | 3-tier: free AI to precision lab |

The commission structure reflects deliberate platform economics: lower rates on high-frequency transactions (marketplace 3-5%) and higher rates on professional services (agronomist 15%, veterinary 12%) where MkulimaForum's vetting, escrow, scheduling, and AI augmentation add the most value. Agronomist tiered reduction at >100 consultations/month creates volume incentives addressing the extension deficit — reaching 24x more farmers per officer than Kenya's 1:1,380 ratio [^49^]. All six services share the unified booking, payment, and review infrastructure, ensuring consistent interaction patterns across categories — a critical determinant of stickiness for the 60% of users on feature phones [^164^] [^215^].
