# MkulimaForum — Cross-Verification Report

## High Confidence Findings (Confirmed by ≥2 agents)

1. **Offline-first is mandatory** — Confirmed by agritech landscape (87% internet subscriptions vs 29% actual users in TZ) and architecture research (CRDTs, Drift sync engines). Rural connectivity remains the #1 constraint.

2. **Mobile money dominance** — M-Pesa (Kenya + TZ), Tigo Pesa/Airtel Money (TZ), MTN MoMo (UG + RW) are the primary payment rails. Confirmed by fintech research and agritech platform analysis (all major platforms integrate mobile money).

3. **Gemini 2.0 Flash is optimal LLM** — Confirmed by AI research ($0.075/1M tokens, 1M context, excellent Swahili) and competitive analysis vs GPT-4o and Claude. 30-60x cheaper than alternatives.

4. **pgvector is optimal vector DB** — Confirmed by AI research (28ms p95 at 50M vectors) and architecture research (471 QPS with pgvectorscale, beats Qdrant). Zero additional infrastructure cost.

5. **Fall Armyworm remains #1 threat** — Confirmed by agritech landscape ($13B losses) and logistics/services research. Digital detection tools are in high demand.

6. **Extension officer ratios critically low** — 1:1,380 (Kenya), 1:1,172 (Tanzania) vs FAO standard 1:400. Confirmed by both agritech and logistics research. AI/digital extension is essential.

7. **Cold chain gap is massive** — 40% post-harvest losses, only 5% through cold chain. $12.87B market growing at 5.1% CAGR. Confirmed by logistics research.

8. **Post-harvest losses at 40%** — Confirmed by multiple sources across agritech and logistics dimensions. Cold chain + warehouse services directly address this.

9. **Women's digital gap is significant** — 24% women vs 35% men use mobile internet. Confirmed by agritech landscape. Gender-specific UX needed.

10. **TARI/KEPHIS digital tools exist but fragmented** — TARI RBMS, KALRO Maize Seed Tracker, KEPHIS seed certification — all operate as separate systems. MkulimaForum can unify access.

## Medium Confidence Findings

1. **iSDAsoil API for soil analysis** — Single authoritative source but well-documented. Free 30m resolution for all sub-Saharan Africa.

2. **PlantVillage Nuru accuracy (65-93%)** — Wide range depends on crop and conditions. Offline capability confirmed but with accuracy trade-offs.

3. **Wakandi CAMS for SACCO digitization** — Single source. Claims 60%+ SACCOS digitized in TZ.

4. **M-Pesa Daraja 3.0 (12,000 TPS)** — Announced Nov 2025. Capacity claim from Safaricom developer docs.

5. **Sokofresh 32+ solar cold rooms** — Single source but verifiable.

## Low Confidence / Needs Validation

1. **Exact smartphone penetration among farmers** — Varies widely by source (41.8% TZ general population vs 40-50% Kenyan farmers). Rural penetration likely much lower.

2. **QLoRA fine-tuning on T4 GPU** — Technical claim from academic paper. Production viability needs verification.

3. **VASPA 2025 stablecoin positioning** — Regulatory speculation. Kenya's stance on digital currencies is evolving.

## Conflict Zones

1. **Plant disease model accuracy in field vs lab** — AI research shows 10-40% accuracy drop in real field conditions vs lab datasets. This is a significant architecture concern: hybrid model (edge AI + cloud fallback) recommended.

2. **Best mapping API for East Africa** — Logistics research favors Mapbox (custom styling, offline tiles). Architecture research notes Google Maps has best coverage but highest cost ($7/1000 loads). HERE offers middle ground. Decision: Mapbox primary, OSM fallback.

3. **Flutter vs React Native for offline-first** — Original document chose Flutter. Architecture research confirms Flutter 3.24+ with Impeller rendering and Drift/SQLite offline support. No conflict — Flutter remains optimal.
