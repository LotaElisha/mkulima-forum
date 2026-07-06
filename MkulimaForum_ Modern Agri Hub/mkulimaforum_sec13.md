## 13. Security, Compliance & Data Sovereignty

MkulimaForum processes farmer PII, handles mobile money payments, and stores crop disease data that East African governments classify as strategically sensitive. Security must address region-specific fraud patterns; compliance must satisfy four national data-protection regimes simultaneously.

### 13.1 Security Architecture

#### 13.1.1 Authentication Hardening

Social engineering accounts for 58–72% of mobile money fraud in East Africa [^37^]. MkulimaForum eliminates password phishing through Passkey/WebAuthn (native in Laravel 13), replacing passwords with device-bound cryptographic key pairs.

SIM swap scams represent 43% of attacks [^37^][^39^]. Device fingerprinting at registration creates a stable hardware identifier; requests from mismatched devices trigger step-up biometric + PIN. Biometric auth has demonstrated a 72% fraud reduction in comparable deployments [^37^]. Certificate pinning prevents MITM attacks; root/jailbreak detection blocks compromised devices.

#### 13.1.2 Data Protection

KYC documents are encrypted at rest using AES-256-GCM with in-region AWS KMS keys. TLS 1.3 protects data in transit. PII is hashed with bcrypt before log inclusion; financial records are written to an append-only WORM audit trail. API tokens reside in Android Keystore or iOS Keychain and rotate via Laravel Sanctum.

#### 13.1.3 Threat Mitigation

**Table 1 — Threat Matrix and Countermeasures**

| Threat Vector | Prevalence | Countermeasure | Risk Reduction |
|---|---|---|---|
| Social engineering | 58–72% of fraud [^37^] | Passkey/WebAuthn — eliminates passwords | Near-complete credential-theft elimination |
| SIM swap scams | 43% of attacks [^37^][^39^] | Device fingerprinting + biometric/PIN step-up | Blocks unauthorised device transfers |
| Agent-assisted fraud | 38% of incidents [^37^] | Biometric + local PIN for high-value transactions | 72% fraud reduction demonstrated [^37^] |
| Fake payment notifications | Widespread [^35^] | API callback verification from MNO before wallet credit | Eliminates SMS spoofing |
| Mobile malware / fake apps | Rising [^35^] | Certificate pinning + root/jailbreak detection | Prevents tampered client access |

Passkey is the single highest-impact security investment given the 58–72% social-engineering share [^37^]. Device fingerprinting addresses SIM swap as a silent detection layer adding no friction to legitimate upgrades.

**Diagram 1 — Layered Defence Architecture**

```
┌────────────────────────────────────────────────────────────────────┐
│  PERIMETER     Cloudflare WAF + DDoS + Africa PoPs (LOS/NBO/JHB) │
│  TRANSPORT     TLS 1.3 │ Certificate pinning │ mTLS for MNO APIs  │
│  APPLICATION   Passkey/WebAuthn │ Device fingerprint │ Bio + PIN   │
│  DATA          AES-256-GCM │ PostgreSQL RLS │ WORM audit trail    │
└────────────────────────────────────────────────────────────────────┘
```

### 13.2 Regulatory Compliance

#### 13.2.1 Data Protection

**Table 2 — Regulatory Compliance by Country**

| Jurisdiction | Statute | Regulator | Key Requirement | Implementation |
|---|---|---|---|---|
| Tanzania | PDPA 2022 [^62^] | PDPC | Cross-border transfer prohibited except under PDPA conditions | RDS in af-south-1; RLS `country_code = 'TZ'`; keys in-region |
| Kenya | DPA 2019 + VASPA 2025 [^63^][^99^] | ODPC + CBK | Mandatory controller registration; DPIA for high-risk | Granular consent toggles; CBK sandbox for escrow-wallet |
| Uganda | DPPA 2019 [^52^][^113^] | NITA-U | NIN-required KYC; escrow at partner bank [^113^] | Licensed FI partner; NIN field in KYC profile |
| Rwanda | Law 058/2021 | NCSA | 72-hour breach notification; DPO for large-scale processing | Automated breach detection; per-tenant DPO designation |

Tanzania's PDPA 2022 prohibits cross-border personal data transfers unless the destination offers "adequate protection" per PDPC [^62^]. MkulimaForum hosts all Tanzanian data in AWS af-south-1 (Cape Town) [^73^] with keys that never leave Africa. The planned Nairobi Local Zone targets <20ms latency [^69^].

#### 13.2.2 Agricultural Regulation

Agro-dealers must hold valid national licences: TFRA (Tanzania); KEPHIS/PCPB (Kenya); UNADA (Uganda); RAB (Rwanda). Veterinary sellers are additionally verified against TVB (Tanzania) or KVB (Kenya) registers. Verification calls are cached 30 days with manual document-upload fallback when regulator APIs are unavailable.

#### 13.2.3 Financial Compliance

PSP licensing is required per country for escrow-wallet operation. BoT has licensed 131 PSPs (42 banks, 72 non-banks) as of 2025 [^79^]; MkulimaForum pursues direct licensing in Tanzania. Kenya's CBK sandbox offers a compliance-light validation path [^64^][^138^]. Uganda mandates escrow partnership with a licensed FI [^113^]. Rwanda's eKash switch — the only cross-domain retail switch in the EAC besides TIPS [^3^] — integrates via IremboPay [^128^]. Escrow funds reside in segregated trust accounts with real-time reconciliation per BoT regulation [^79^].

### 13.3 Data Sovereignty Architecture

#### 13.3.1 Regional Data Residency

The sovereignty posture rests on three pillars: African cloud residency, per-country isolation, and in-region encryption. Primary deployment is AWS af-south-1 (Cape Town) [^73^]. Cloudflare PoPs in Lagos, Nairobi, and Johannesburg cache at the edge. PostgreSQL RLS enforces tenant isolation via `country_code`, ensuring a TZ agro-dealer's data is never returned to a KE API consumer.

#### 13.3.2 Government Data Sharing

Data sovereignty doubles as a competitive moat. Foreign platforms routing farmer data through European or US clouds face rising regulatory scrutiny and farmer distrust. MkulimaForum converts this into advantage by offering aggregated, anonymised data to government research bodies: disease patterns for TARI, yield benchmarks for KALRO, input quality metrics for NARO, and extension-impact dashboards for RAB. All shared data applies k-anonymity with $k \geq 5$ at district level, preventing re-identification of individual shambas. Monthly reports are delivered via a read-only government API.

**Diagram 2 — Data Sovereignty Compliance Flow**

```
┌──────────────────────────────────────────────────────────────────┐
│  MKULIMAFORUM                                                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐             │
│  │ TZ tenant│ │ KE tenant│ │ UG tenant│ │ RW tenant│ (RLS scoped)│
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘             │
│       └─────────────┴─────────────┴─────────────┘                  │
│                         │                                        │
│            ┌────────────▼────────────┐                           │
│            │ PostgreSQL af-south-1   │                           │
│            │ AES-256-GCM │ KMS Africa│                           │
│            └────────────┬────────────┘                           │
└─────────────────────────┼────────────────────────────────────────┘
                          │
              ┌───────────▼────────────┐
              │ ANONYMISATION: k>=5    │
              │ District-level aggregate│
              └───────────┬────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
┌───────▼──────┐ ┌────────▼───────┐ ┌──────▼────────┐
│ TARI (TZ)    │ │ KALRO (KE)     │ │ NARO/RAB      │
│ Disease data │ │ Yield benchmarks│ │ Extension API │
└──────────────┘ └────────────────┘ └───────────────┘
```

By architecting for African data residency from day one, MkulimaForum satisfies regulatory demands across all four jurisdictions while building institutional trust that foreign-hosted competitors cannot readily replicate.
