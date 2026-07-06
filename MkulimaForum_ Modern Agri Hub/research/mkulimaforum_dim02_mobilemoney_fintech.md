## Dimension 2: Mobile Money & Agricultural Fintech

**Research Date:** July 2025
**Scope:** East Africa (Kenya, Tanzania, Uganda, Rwanda)
**Objective:** Design payment architecture for MkulimaForum agricultural marketplace

---

### Key Findings

- **M-Pesa Daraja 3.0** launched November 2025 with cloud-native architecture, capacity for 12,000 TPS, and new APIs including Ratiba (recurring payments), Security APIs, and IoT APIs — while maintaining backward-compatible STK Push endpoints [^1^][^2^].
- **Tanzania's payment ecosystem** features six licensed mobile money providers (M-Pesa, Airtel Money, Mixx by Yass/Tigo Pesa, HaloPesa, T-Pesa, AzamPesa) with API access primarily through licensed aggregators like ClickPesa due to direct integration complexity [^4^][^104^][^112^].
- **MTN MoMo Open APIs** are available in Uganda and Rwanda with developer portals, sandbox environments, and Collections/Disbursements/Remittances API products; go-live requires KYC approval [^5^][^7^][^105^].
- **East African Payment System (EAPS)** currently connects only 4 of 8 EAC Partner States (Kenya, Rwanda, Tanzania, Uganda) for wholesale cross-border transfers; retail mobile money interoperability remains bilateral or aggregator-dependent [^3^][^8^].
- **The EAC Cross-Border Payment System Masterplan 2025** envisions a regional instant retail payment switch, ISO 20022 messaging standards, and harmonized mobile money regulatory framework over 5 years [^3^].
- **Agricultural financing platforms** like Apollo Agriculture use AI-driven credit scoring combining satellite imagery, farm data, and mobile money transaction history; loans are disbursed and repaid via M-Pesa [^10^][^11^].
- **Pula (now Coalition)** has insured over 19 million farmers across 19 countries using parametric insurance bundled with inputs and credit; ACRE Africa uses satellite + AI-based Picture-Based Monitoring for claims processing [^54^][^55^][^56^].
- **Escrow services in East Africa** remain underdeveloped; existing models include EscrowLock (Nigeria-focused), Accrue's Cashramp P2P escrow network, and aggregator-based trust models [^59^][^62^].
- **Kenya's Virtual Asset Service Providers Act 2025** establishes a dual regulatory framework (CBK + CMA) covering stablecoin issuance, digital wallets, and exchanges — making Kenya the most advanced EAC jurisdiction for digital asset regulation [^57^][^99^][^101^].
- **SACCO digitization** is accelerating through shared platforms like EFT Corporation's SACCO payments platform (Kenya) and SCK's shared core banking system, enabling direct participation in national payment systems [^46^][^47^].

---

### Mobile Money API Landscape

#### M-Pesa (Kenya) — Daraja API
| Feature | Detail |
|---------|--------|
| **API Version** | Daraja 3.0 (launched November 2025) [^2^] |
| **Architecture** | Cloud-native, microservices-based, 12,000 TPS capacity [^1^] |
| **STK Push** | `/mpesa/stkpush/v1/processrequest` — prompts customer with PIN entry on phone [^2^][^12^] |
| **C2B (Paybill)** | Customer-to-business payments via registered paybill numbers |
| **B2C (Disbursement)** | Business-to-customer payouts — requires separate Safaricom approval [^1^] |
| **B2B** | Inter-paybill transfers for supplier settlements |
| **Recurring Payments** | *Ratiba API* — daily, weekly, monthly, yearly billing cycles (NEW in v3.0) [^2^] |
| **Security APIs** | Built-in fraud detection, identity verification (NEW in v3.0) [^2^] |
| **Mini Apps** | Runs inside M-Pesa Super App using Ant Group's Mini Program framework [^2^] |
| **Authentication** | OAuth2 client credentials flow with Consumer Key + Secret [^12^] |
| **Base URLs** | `sandbox.safaricom.co.ke` (test), `api.safaricom.co.ke` (production) [^2^] |
| **Onboarding** | Self-service model replacing paper-based manual approval [^2^] |
| **Developers** | 105,000+ registered developers [^2^] |

**M-Pesa Transaction Fees (Kenya, 2025):**
- Send Money (registered users): KES 0–108 per transaction (max KES 250,000 per tx) [^78^][^83^]
- Send Money (inter-network): Harmonized — same as Safaricom-to-Safaricom from 2025 [^78^]
- Withdrawal from Agent: KES 11–309 [^78^]
- **Lipa Na M-Pesa (Buy Goods):** Merchant pays **0.5% max KES 200** per transaction [^137^]
- Paybill: Varies by service provider, KES 0–100 [^80^]
- Maximum daily transaction limit: KES 500,000 [^78^]
- Deposits: Free; Airtime purchase: Free; Balance inquiry: Free [^78^]

**API Access Model:**
- API access itself is free; revenue share/transaction fees apply [^58^]
- Third-party intermediaries like Lipana charge ~1% per successful transaction [^124^]
- Sandbox available for testing before compliance approval [^58^]

#### M-Pesa (Tanzania) — Vodacom Developer Portal
| Feature | Detail |
|---------|--------|
| **Developer Portal** | `business.m-pesa.com/developers` [^66^] |
| **APIs Exposed** | C2B, Reversals, Transaction Status Query [^66^] |
| **Integration** | RESTful APIs; SDK library file (`portal-sdk.jar`) for Java provided [^66^] |
| **Authentication** | API Key + Public Key encryption [^66^] |
| **Environments** | Sandbox testing available; production requires go-live approval [^66^] |
| **Format** | XML scripting for API requests; host-to-host VPN may be required [^136^] |

**M-Pesa Tanzania Transaction Fees:** Generally higher fees compared to newer entrants like AzamPesa; competitive pressure from multiple providers [^140^].

#### Mixx by Yass (Tigo Pesa) — Tanzania
- Rebranded from Tigo Pesa; now operates as Mixx by Yass [^104^]
- API integration primarily through licensed aggregators (ClickPesa) [^104^]
- Collection methods: USSD Push, Bill Payments via Control Numbers [^104^]
- Disbursement: Single payments and bulk payments supported [^104^]

#### Airtel Money — Tanzania & Regional
- API integration via aggregators (ClickPesa in Tanzania) [^4^]
- Collection: USSD Push and Bill Payments via Control Numbers [^4^]
- Disbursement: Single and bulk payment options [^4^]
- Active in Tanzania, Uganda, Rwanda, Kenya, and other EAC markets

#### HaloPesa — Tanzania
- Viettel-owned mobile money operator [^79^]
- API access through aggregators like ClickPesa [^112^]
- USSD Push and Control Number payment methods [^112^]
- Licensed non-bank payment system provider under Bank of Tanzania oversight [^79^]

#### MTN MoMo — Uganda
| Feature | Detail |
|---------|--------|
| **Developer Portal** | `momodeveloper.mtn.com` |
| **API Products** | Collections, Disbursements, Remittances, Collection Widget [^105^] |
| **Key APIs** | RequestToPay, Transfer, Balance, GetTransactionStatus, ValidateAccountHolder [^105^] |
| **Sandbox** | Available after free sign-up; replicates production environment [^7^] |
| **Go-Live Timeline** | ~10 days after KYC submission (vs. 2+ months historically) [^105^] |
| **Fees** | Free portal access; transaction fees apply to commercialized products [^105^] |
| **Channels** | Web, App, USSD all supported [^105^] |

#### MTN MoMo — Rwanda
| Feature | Detail |
|---------|--------|
| **Developer Portal** | `momodeveloper.mtn.co.rw` [^5^] |
| **Products** | Collections, Disbursements |
| **API Flow** | Generate access token → RequestToPay/Transfer → Callback handling [^108^] |
| **Go-Live** | KYC documents submitted via `momodeveloper.mtn.co.rw/go-live` [^5^] |
| **Partner Portal** | `momoapi.mtn.co.rw` for live API dashboard access [^5^] |

#### IremboPay — Rwanda (Government & Enterprise Payment Platform)
- REST Payment API designed for Rwanda's payment ecosystem [^128^]
- Supports MTN Mobile Money, Airtel Money, multiple banks, Visa, Mastercard, Amex [^128^]
- Provides unified API over multiple provider backends — model for MkulimaForum's architecture [^128^][^132^]

---

### Cross-Border Mobile Money Transfer Landscape

**East African Payment System (EAPS):**
- Current status: Connects Kenya, Rwanda, Tanzania, Uganda for wholesale (RTGS) transfers only [^3^][^9^]
- Operates Monday–Friday, 08:30–16:00 EAT; local currencies only [^9^]
- NOT suitable for retail/mobile money transactions in its current form [^8^]
- 4 of 8 Partner States not yet connected (Burundi, DRC, South Sudan, Somalia) [^3^]

**Cross-Border Mobile Money Options:**
- **Bilateral MNO partnerships:** Safaricom-Vodacom, MTN-Airtel cross-network arrangements
- **Private aggregators:** Onafriq (formerly MFS Africa), Cellulant, ClickPesa provide cross-border rails [^68^][^130^]
- **Onafriq:** 1 billion wallets, 400,000 agents in Nigeria, 30+ BINs; bulk payment API for agritech/NGO use cases [^130^]
- **Accrue (Cashramp):** Stablecoin-based P2P cross-border transfers with escrow; active in Kenya, Nigeria, Ghana, Zambia, Cameroon, South Africa [^62^]

**EAC Masterplan Initiatives (2025–2030):**
- Harmonized cross-border mobile money regulatory framework (Initiative 3) [^3^]
- Regional instant retail payment switch (medium-term) [^3^]
- ISO 20022 as harmonized messaging standard (Initiative 4) [^3^]
- Standardized proxy identifiers using mobile numbers (Initiative 14) [^3^]
- Regional QR code standard (TANQR in Tanzania, KE-QR in Kenya) [^57^]
- CBDC feasibility exploration for cross-border transactions (Initiative 16) [^3^]

---

### Escrow & Wallet Architecture Patterns

**Current State of Escrow in East Africa:**
Escrow services specifically tailored for East Africa remain limited. The most relevant models for MkulimaForum include:

**1. Aggregator-Based Trust Model (Most Practical)**
- Payment gateway holds funds in trust account until delivery confirmation
- Licensed by central bank as payment system provider
- Example: ClickPesa (Tanzania) operates as licensed payment gateway with trust account infrastructure [^4^][^112^]
- Bank of Tanzania requires reconciliation of trust account balances against e-money balances in real-time [^79^]

**2. P2P Escrow via Stablecoin Infrastructure**
- Accrue's Cashramp uses smart contract escrow between agents across countries [^62^]
- Agents deposit stablecoins as collateral; escrow releases upon confirmation
- 200,000+ users across 6 African countries [^62^]
- **Regulatory note:** Requires VASP licensing in Kenya under VASPA 2025 [^99^]

**3. Traditional Escrow Platforms**
- EscrowLock (Nigeria-focused): 1.25–3.25% fees; buyer-seller protection for online transactions [^59^]
- ListBuy/IderaOS: Nigerian e-commerce escrow platform [^63^]
- These are **not yet operational in East African markets** — represents a market gap

**Recommended Pattern for MkulimaForum:**
- **Hybrid escrow wallet:** Buyer funds held in segregated trust account at licensed bank
- **Milestone-based release:** Funds released to seller upon delivery confirmation, inspection period, or dispute resolution
- **Multi-signature authorization:** Platform + Buyer + optional Arbiter for dispute cases
- **Interest-bearing escrow:** Held funds earn micro-interest distributed to buyer (encourages platform trust)

---

### Agricultural Financing Models

#### Apollo Agriculture (Kenya & Zambia)
| Feature | Detail |
|---------|--------|
| **Credit Model** | AI-driven, data-based lending decisions — no collateral required [^11^] |
| **Data Sources** | Satellite imagery, farm GPS boundaries, household info, farming behavior, credit bureau records [^10^][^11^] |
| **Loan Size** | KES 15,000–24,000 (~$115–$180) for maize loans up to 1 hectare [^11^] |
| **Repayment** | 8-month schedule aligned to agricultural season; via mobile money gradually over season [^11^] |
| **API Integration** | Not publicly available; partnerships with agri-input dealers for last-mile distribution [^10^] |
| **Scale** | Thousands of farmers; machine learning automates credit scoring after manual data verification [^11^] |

#### One Acre Fund
| Feature | Detail |
|---------|--------|
| **Model** | Nonprofit serving 5 million farmers across 9 African countries [^55^] |
| **Services** | Input financing + bundled insurance (One Acre Shield) + agronomic training |
| **Repayment** | M-Pesa-based; group leaders collect and submit aggregate payments [^86^] |
| **Digital Payments** | Mobile repayment piloted in 2013; now core to operations [^86^] |
| **Insurance Arm** | One Acre Shield provides reinsurance innovations; parametric products [^55^] |

#### Other Models
- **M-Shwari (Safaricom + NCBA):** Savings and loan product integrated in M-Pesa; loan eligibility based on M-Pesa transaction history [^84^]
- **KCB M-Pesa:** Similar model with KCB Bank; disbursement and repayment via M-Pesa [^84^]
- **Connected Farmer Alliance:** Vodafone + TechnoServe + USAID platform for digitalizing agribusiness procurement via M-Pesa infrastructure [^88^]

**Key Insight for MkulimaForum:** All successful models use mobile money as both disbursement AND collection rail. The platform should integrate M-Pesa/MTN MoMo disbursement APIs for farm input financing and automatic repayment deductions.

---

### Crop Insurance Integration

#### Pula (now Coalition)
| Feature | Detail |
|---------|--------|
| **Scale** | 19 million farmers protected across 19 countries in Africa and Asia [^55^] |
| **Model** | Parametric insurance bundled with inputs and credit; satellite + crop-cutting experiments [^55^] |
| **Products** | Crop insurance, livestock insurance, disaster risk coverage, data consulting [^55^] |
| **Platform** | Pula Insurance Engine (PIE) — cloud-based digital pricing platform for yield and weather indices [^56^] |
| **Premiums Facilitated** | $126 million; Claims paid: $92 million [^55^] |
| **API Availability** | Not publicly documented; enterprise partnerships required |

#### ACRE Africa
| Feature | Detail |
|---------|--------|
| **Scale** | 5 million+ farmers insured; 1.2–1.3 million expected in 2025 [^55^] |
| **Technology** | Satellite data + village champions for ground verification + Picture-Based Monitoring (PBM) using AI/ML [^54^] |
| **Claims** | Hybrid verification: satellite detects triggers, field agents confirm, AI speeds validation [^54^] |
| **Innovation** | Piloting blockchain-based claims processing for <2 week payout time [^54^] |
| **Gender Focus** | 60% uptake of PBM among women farmers in Uganda [^54^] |
| **National Schemes** | Digital platform supports government-backed insurance programs [^55^] |

#### Ibisa Network
- Reaches 700,000+ people; facilitated $600 million in premiums [^55^]
- Coverage: rainfall, solar radiation, heat stress, flood risks
- Client-facing dashboards, operational data management, risk modeling tools

**Integration Pattern for MkulimaForum:**
- **Embedded insurance:** Bundle crop insurance with input purchases at checkout
- **API integration:** Partner with Pula/ACRE for programmatic policy issuance and claims
- **Index-based triggers:** Satellite data integration for automatic payout on drought/flood detection
- **Premium financing:** Deduct insurance premiums in installments via mobile money alongside input repayments

---

### Wallet and Super-App Payment Patterns

**Leading App Architectures in East Africa:**

**1. Cellulant Pattern (Multi-Provider Gateway)**
- Powers Jumia, Glovo, Booking.com, Ethiopian Airlines, Kenya Airways, Uber, Bolt [^68^]
- Single integration connects 250+ payment methods
- Supports mobile money, cards, bank transfers, alternative payment methods (APMs)

**2. IremboPay Pattern (One API, Multiple Providers)**
- Abstracts MTN MoMo, Airtel Money, multiple banks, cards behind unified REST API [^128^][^132^]
- Normalizes provider statuses, callbacks, receipts, refunds, reconciliation
- Reduces provider lock-in; separates business records from provider-specific formats [^132^]

**3. M-Pesa Super App + Mini Apps (Daraja 3.0)**
- Ant Group Mini Program framework
- Lightweight apps run inside M-Pesa Super App
- JavaScript-based SDK, separate IDE, submission/approval process [^2^]

**Recommended Pattern for MkulimaForum:**
- **Unified Payment Layer:** Single internal API abstracting all mobile money providers (M-Pesa, MTN MoMo, Airtel Money)
- **Provider Connectors:** Separate adapters for each MNO's API (Collections, Disbursements, Status Query)
- **Wallet Architecture:** User wallets with sub-wallets — Main, Escrow, Savings, Insurance
- **Reconciliation Engine:** Normalized transaction status across all providers with webhook callbacks

---

### CBDC and Stablecoin Developments

#### Kenya
- **Virtual Asset Service Providers Act, 2025 (VASPA):** Passed October 2025; Kenya's first comprehensive digital asset law [^57^][^99^][^101^]
- **Dual Regulators:** CBK oversees wallets, payment processors, stablecoin issuance; CMA oversees exchanges, brokers, tokenization [^99^][^100^]
- **Stablecoin Regulation:** CBK has authority to set requirements for stablecoin issuance, reserve backing, redemption [^102^]
- **Integration with M-Pesa:** Policy advocates promoting integration between stablecoins and mobile money infrastructure [^57^]
- **Sandbox:** Capital Markets Authority regulatory sandbox operational; 7+ fintech firms admitted [^64^]

#### Tanzania
- **No CBDC pilot announced** as of 2025
- Bank of Tanzania is participating in EAC-wide CBDC feasibility exploration under the Cross-Border Payment System Masterplan (Initiative 16) [^3^]
- Foreign currency prohibition regulations issued March 2025 — all domestic transactions must be in TZS [^85^]
- This strengthens the case for shilling-denominated stablecoins if CBDC is explored

#### Regional (EAC)
- **EAC Masterplan Initiative 16:** Exploring feasibility of CBDCs for regional cross-border transactions [^3^]
- **Initiative 17:** Developing regulatory approaches for virtual assets across all Partner States [^3^]
- Kenya is the clear regulatory leader; other EAC countries have not yet enacted comprehensive VA legislation

**Implications for MkulimaForum:**
- Stablecoin integration is a **medium-term opportunity** (2–3 years) once VASP regulations are fully operational
- In the immediate term, focus on fiat mobile money rails (M-Pesa, MTN MoMo)
- Monitor CBK's stablecoin licensing requirements for potential agricultural commodity-backed tokenization

---

### Payment Regulations 2025–2026

#### Tanzania
| Aspect | Detail |
|--------|--------|
| **Regulator** | Bank of Tanzania (BoT) |
| **Key Law** | National Payment Systems Act 2015; Electronic Money Regulations 2015 [^79^] |
| **Licensed PSPs** | 131 total (42 banks, 72 non-banks); 13 new licenses issued in 2025 [^79^] |
| **EMI Restrictions** | E-money issuance restricted to banks and MNOs only [^79^] |
| **KYC** | Tiered KYC with transaction limits; e-KYC supported |
| **Transaction Limits** | Vary by provider and KYC tier; B2W/W2B fees capped at TZS 5,000 [^79^] |
| **Foreign Currency** | Prohibited for domestic transactions from March 2025; all pricing in TZS [^85^] |
| **Tax** | 16% VAT on digital transactions via banks/electronic payment systems (Finance Act 2025) [^79^] |
| **NPS Levy** | Max TZS 4,000 for transactions ≥TZS 3,000,000; salary payments exempt [^87^] |
| **Trust Accounts** | Real-time reconciliation of trust account balances against e-money required [^79^] |
| **TANQR** | National QR code standard launched 2022 for mobile payments [^57^] |

#### Kenya
| Aspect | Detail |
|--------|--------|
| **Regulator** | Central Bank of Kenya (CBK) |
| **Key Law** | National Payment Systems Act 2011; NPS Regulations 2014 [^138^] |
| **Vision** | NPS Vision & Strategy 2021–2025: "cash-lite, world-class payments system" [^138^] |
| **KYC** | e-KYC and tiered-KYC regulations; IPRS identity verification [^138^] |
| **Transaction Limits** | Max KES 250,000 per transaction; KES 500,000 daily balance limit [^78^] |
| **Interoperability** | Full interoperability between M-Pesa, Airtel Money, T-Kash [^78^] |
| **Pricing** | CBK pricing principles: transparency, fairness, affordability [^138^] |
| **Consumer Protection** | Framework for digital payments consumer protection under development [^138^] |
| **Open APIs** | CBK facilitating open but secure API standards [^138^] |
| **VASPA 2025** | Virtual Asset Service Providers Act: licensing regime for crypto/stablecoins [^101^] |
| **KE-QR** | Kenya Quick Response Code Standard (EMVCo-aligned) launched 2023 [^57^] |

#### Uganda
| Aspect | Detail |
|--------|--------|
| **Regulator** | Bank of Uganda (BoU) |
| **Key Law** | National Payment Systems Act 2020; Mobile Money Guidelines 2013 [^53^][^113^] |
| **KYC** | Tiered KYC; NIN as basic requirement for account opening; Uganda scores 90/100 on KYC index [^52^][^53^] |
| **Interoperability** | P2P interoperable between MTN, Airtel, UTL since 2017/2018 [^106^] |
| **Escrow Requirement** | Mobile money operators must hold equivalent e-value in escrow account at partner bank [^113^] |
| **AML/CFT** | Operators must comply with AML Act; real-time transaction monitoring [^113^] |
| **Sandbox** | Bank of Uganda + Capital Markets Authority regulatory sandbox established 2021 [^53^] |

#### Rwanda
| Aspect | Detail |
|--------|--------|
| **Regulator** | National Bank of Rwanda (BNR) |
| **Switch** | eKash — cross-domain retail switch connecting all PSPs (only operational switch in EAC besides Tanzania's TIPS) [^3^] |
| **Interoperability** | Full domestic interoperability through eKash |
| **Irembo** | Government digital services platform integrating MTN MoMo, Airtel Money, banks, cards [^131^] |
| **API Standards** | Open API framework through IremboPay for private sector [^128^] |
| **EAPS** | Connected to EAPS for cross-border wholesale transfers [^3^] |

---

### Recommended Payment Architecture for MkulimaForum

Based on this research, the following architecture is recommended for MkulimaForum's agricultural marketplace platform:

#### 1. Core Payment Layer
- **Unified Payment API:** Single internal API abstracting all provider-specific implementations (following IremboPay/GBOX pattern) [^128^][^132^]
- **Provider Connectors:** Modular adapters for:
  - M-Pesa Daraja 3.0 (Kenya + Tanzania)
  - MTN MoMo Open API (Uganda + Rwanda)
  - Airtel Money API (all markets via aggregators where needed)
  - HaloPesa, Mixx by Yass (Tanzania via ClickPesa or direct)
- **Aggregator Fallback:** Use ClickPesa (Tanzania), Cellulant, or Onafriq for markets where direct MNO integration is complex

#### 2. Wallet Architecture
- **User Wallets:** Each farmer and buyer gets a platform wallet
- **Sub-Wallet Structure:**
  - **Main Wallet:** Available balance for transactions
  - **Escrow Wallet:** Held funds pending delivery confirmation
  - **Savings Wallet:** Optional interest-bearing sub-account
  - **Insurance Wallet:** Accumulated premiums for crop insurance
- **Mobile Money Linking:** Wallet funded via M-Pesa/MTN MoMo push; withdrawals to mobile money

#### 3. Escrow Service
- **Trust Account:** Segregated account at licensed commercial bank (required in TZ, UG, KE) [^79^][^113^]
- **Milestone-Based Release:**
  1. Buyer pays → funds held in escrow
  2. Seller delivers goods → funds released upon buyer confirmation
  3. Dispute → arbitration window (48 hours) before auto-release or refund
- **Fee:** 0.5–1.5% per escrow transaction (competitive with Lipa Na M-Pesa merchant rates) [^137^]

#### 4. Agricultural Financing Integration
- **Input Financing API:** Partner with Apollo Agriculture or similar for embedded credit
- **Disbursement:** Use B2C disbursement APIs to send loan funds directly to farmer mobile money wallets
- **Repayment:** Automatic collection via STK Push or standing instructions (Ratiba API for recurring) [^2^]
- **Credit Scoring:** Integrate satellite data, mobile money transaction history, and platform behavior

#### 5. Insurance Integration
- **Embedded Insurance:** Partner with Pula/Coalition or ACRE Africa
- **Trigger:** Offer insurance at input purchase checkout
- **Premium Collection:** Deducted in installments via mobile money alongside input repayments
- **Claims:** API-based parametric claims; satellite-triggered automatic payouts

#### 6. Compliance & Regulatory
- **KYC:** Tiered KYC aligned to each country's regulatory requirements
  - Tier 1: Basic (name, phone, national ID) — low limits
  - Tier 2: Verified (ID scan, selfie, address) — standard limits
  - Tier 3: Enhanced (bank statements, business registration) — high limits
- **AML/CFT:** Transaction monitoring, suspicious activity reporting, sanctions screening
- **Licensing Strategy:**
  - **Tanzania:** Apply for non-bank PSP license (131 already licensed; BoT encouraging new entrants) [^79^]
  - **Kenya:** PSP authorization under NPS Act; consider sandbox entry for innovative features [^138^]
  - **Uganda:** Partner with licensed financial institution for mobile money escrow (required by BoU) [^113^]
  - **Rwanda:** Leverage eKash switch and IremboPay integration standards [^128^]

#### 7. Cross-Border Strategy
- **Phase 1:** Domestic transactions only (per-country marketplace instances)
- **Phase 2:** Cross-border via Onafriq or similar aggregator for regional trade
- **Phase 3:** Direct EAPS integration once retail switch is operational (medium-term, per EAC Masterplan) [^3^]

#### 8. Technology Stack Recommendations
- **API Gateway:** Kong or AWS API Gateway for unified endpoint management
- **Message Queue:** Apache Kafka or RabbitMQ for async payment processing
- **Database:** PostgreSQL for transactional data; Redis for session/cache
- **Webhook System:** Idempotent webhook delivery with retry logic for provider callbacks
- **Reconciliation:** Automated daily reconciliation against provider settlement reports
- **ISO 20022:** Adopt early for future-proofing (EAC Masterplan direction) [^3^]

#### 9. Fee Structure (Recommended)
| Service | Fee |
|---------|-----|
| Buyer payment processing | Free to buyer; merchant pays 0.5–1% |
| Escrow service | 1–1.5% of transaction value |
| Withdrawal to mobile money | Pass-through of MNO fees |
| Input financing disbursement | 2–5% platform fee (embedded in loan) |
| Insurance premium | 3–7% of input value (crop-dependent) |
| Cross-border transfer | 1–2% + FX spread |

---

### Sources

[^1^] https://cnbcode.com/blog/what-is-mpesa-api-explained — M-Pesa API Overview for Businesses
[^2^] https://dev.to/ronnyabuto/what-daraja-30-actually-changed-for-developers-and-what-it-did-not-3ek4 — Daraja 3.0 Developer Changes
[^3^] https://www.brb.bi/sites/default/files/2025-10/EAC-PAYMENT-SYSTEMS-MASTERPLAN.pdf — EAC Cross-Border Payment System Masterplan 2025
[^4^] https://clickpesa.com/payment-gateway/payment-and-payout-methods/airtel-money-api-integration-guide/ — Airtel Money API Integration Guide (ClickPesa)
[^5^] https://community.shopify.com/t/need-a-developer-to-intergrate-a-check-out-payment-using-mtn-and-orange-mobile-money-momo-api/6281 — MTN MoMoPay Integration Guide (Shopify)
[^7^] https://www.ericsson.com/en/cases/2023/mtn-mobile-money-open-apis — MTN Mobile Money Open APIs Case Study
[^8^] https://ecdpm.org/application/files/1616/9657/9822/Interoperability-digital-payment-systems-Lessons-from-East-African-Community-ECDPM-Discussion-Paper-357-2023.pdf — Interoperability of Digital Payment Systems in EAC
[^9^] https://www.elibrary.imf.org/view/journals/087/2025/004/article-A001-en.xml — Digital Payment Innovations in Sub-Saharan Africa (IMF)
[^10^] https://documents1.worldbank.org/curated/en/461421559326915086/pdf/The-Digital-Financial-Services-for-Agriculture-Handbook.pdf — World Bank Digital Financial Services for Agriculture Handbook
[^11^] https://www.gsma.com/solutions-and-impact/connectivity-for-good/mobile-for-development/programme/agritech/ai-driven-smallholder-farmer-lending-in-africa-insights-from-apollo-agriculture/ — Apollo Agriculture AI-Driven Farmer Lending (GSMA)
[^12^] https://blog.lxmwaniky.me/testing-mpesa-daraja-apis-with-postman — Testing M-Pesa Daraja APIs with Postman
[^46^] https://www.fsdkenya.org/blogs-publications/the-rise-of-shared-services-for-savings-and-credit-co-operative-organisations-saccos-in-kenya/ — FSD Kenya SACCO Shared Services
[^47^] https://techafricanews.com/2025/06/26/eft-corporation-partners-with-kenyan-saccos-to-co-create-shared-digital-payments-platform/ — EFT Corporation SACCO Digital Payments Platform
[^52^] https://thedocs.worldbank.org/en/doc/6a8ad94689065f6c423a304e79f97fdb-0050062022/original/FindexNote7.pdf — World Bank Findex Note: Mobile Money in Sub-Saharan Africa
[^53^] http://kba.co.ke/wp-content/uploads/2025/05/Digital-Financial-Services-Regulations-Their-Evolution-and-Impact-on-Financial-Inclusion-in-East-Africa-%E2%80%93-Ronald-Ochen-and-Enock-Will-Nsubuga-Bulime.pdf — Digital Financial Services Regulations in East Africa
[^54^] https://mercycorpsagrifin.org/walking-with-farmers-through-risk-how-acre-africa-is-redefining-agricultural-insurance-across-the-continent/ — ACRE Africa Agricultural Insurance
[^55^] http://www.smefinanceforum.org/post/sme-finance-virtual-marketplace-insurtech-smallholder-farmers — Insurtech for Smallholder Farmers (SME Finance Forum)
[^56^] https://www.unsgsa.org/stories/empowering-kenyan-smallholder-farmers-pulas-game-changing-digital-insurance — Pula's Digital Insurance (UNSGSA)
[^57^] https://cytonnreport.com/topicals/stablecoins-and-their-potential-applications-in-kenyas-digital-economy — Stablecoins in Kenya's Digital Economy (Cytonn)
[^58^] https://medium.com/@unreal_joova/is-m-pesa-api-free-and-what-you-should-know-before-integrating-8b9631910ae6 — Is M-Pesa API Free?
[^59^] https://www.escrowlock.com/ — EscrowLock Nigeria
[^62^] https://www.mariblock.com/stories/african-payments-company-accrue-raises-1-58-million — Accrue Raises $1.58M (Mariblock)
[^64^] https://bowmanslaw.com/insights/kenyan-regulators-set-their-sights-on-free-and-easy-fintech/ — Kenyan Regulators Fintech Sandbox
[^66^] https://business.m-pesa.com/developers/ — M-Pesa Developer Portal (Tanzania)
[^68^] https://www.cellulant.io/2024/01/09/unleashing-africas-e-commerce-potential-through-alternative-payment-methods/ — Cellulant Alternative Payment Methods
[^78^] https://khusoko.com/2025/01/04/safaricom-updates-m-pesa-charges-for-2025/ — Safaricom M-PESA Charges 2025
[^79^] https://www.bot.go.tz/Publications/Regular/Annual%20Report/en/2026060211494565.pdf — Bank of Tanzania National Payment Systems Annual Report 2025
[^80^] https://decodehash.com/app/blogs/m-pesa-charges-in-2025 — M-Pesa Charges in 2025
[^83^] https://techweez.com/2024/12/30/m-pesa-charges-withdrawal-and-send-money-fees-2025/ — M-Pesa Charges 2025 (Techweez)
[^85^] https://www.dentons.com/en/insights/alerts/2025/april/25/tanzania-enacts-sweeping-restrictions-on-foreign-currency-transactions — Tanzania Foreign Currency Restrictions (Dentons)
[^86^] https://www.cgap.org/blog/can-mobile-money-extend-financial-services-to-smallholder-farmers — Mobile Money for Smallholder Farmers (CGAP)
[^87^] https://www.lexology.com/library/detail.aspx?g=50f25107-a8d7-410f-b772-7e9cdb69fc3b — Tanzania NPS Act Amendments (Lexology)
[^88^] https://cgspace.cgiar.org/server/api/core/bitstreams/9c80182c-e980-45b4-ad47-c86d8b8cce2b/content — Case Studies on Digitalized Payments in Agri-Food (CGIAR)
[^99^] https://bowmanslaw.com/insights/kenya-major-regulatory-and-licensing-reforms-in-the-evolving-virtual-asset-space/ — Kenya VASP Act Regulatory Reforms (Bowmans)
[^100^] https://www.afriwise.com/blog/impact-of-the-virtual-asset-service-providers-act-2025-on-kenyas-digital-asset-ecosystem — VASP Act Impact on Kenya's Digital Asset Ecosystem (Afriwise)
[^101^] https://new.kenyalaw.org/akn/ke/act/2025/20 — Virtual Asset Service Providers Act 2025 (Kenya Law)
[^102^] https://www.treasury.go.ke/sites/default/files/Latest%20updates/Regulatory%20Impact%20Statement%20on%20VASP%20Regulations%20-%202026%20-%20PP.pdf — Regulatory Impact Statement on VASP Regulations 2026
[^104^] https://clickpesa.com/payment-gateway/payment-and-payout-methods/mixx-by-yas-tigo-pesa-api-integration-guide/ — Mixx by Yass (Tigo Pesa) API Integration (ClickPesa)
[^105^] https://www.mtn.co.ug/helppersonal/mtn-open-api/ — MTN Open API Uganda
[^106^] https://fsduganda.or.ug/our-work/digital-economy/interoperability-rules-for-mobile-money-network/ — Interoperability Rules for Mobile Money (FSD Uganda)
[^108^] https://cleverengineer.substack.com/p/going-live-with-mtn-momo-api-in-2025 — Going Live with MTN MoMo API in 2025
[^112^] https://clickpesa.com/payment-gateway/payment-and-payout-methods/halopesa-api-integration-guide/ — HaloPesa API Integration (ClickPesa)
[^113^] https://www.bis.org/review/r150310d.htm — Bank of Uganda Mobile Money Regulation (BIS)
[^124^] https://lipana.dev/ — Lipana M-Pesa API for Developers
[^126^] https://clickpesa.com/clickpesa-launches-new-payment-apis-in-tanzania/ — ClickPesa New Payment APIs in Tanzania
[^128^] https://irembopay.gitbook.io/irembopay-api-docs/getting-started/quickstart — IremboPay API Documentation
[^130^] https://onafriq.com/insights/article/meeting-the-demands-of-bulk-payments-for-complex-business-needs/ — Onafriq Bulk Payments Platform
[^131^] https://dial.global/modern-governments-run-on-data-exemplars/ — Irembo Government Data Integration (DIAL)
[^132^] https://gbox.rw/en/blog/one-api-multiple-payment-providers-africa/ — One API for Multiple Payment Providers (GBOX)
[^137^] https://www.scribd.com/document/681684505/The-Mpesa-Business-Till-Tariff — M-PESA Business Till Tariff
[^138^] https://www.centralbank.go.ke/wp-content/uploads/2020/12/CBK-NPS-Vision-and-Strategy.pdf — CBK National Payments System Vision and Strategy 2021-2025
[^140^] https://www.scribd.com/document/973943481/Tanzania-Mobile-Money-Payments-Banks — Tanzania Mobile Money vs Banks 2025
