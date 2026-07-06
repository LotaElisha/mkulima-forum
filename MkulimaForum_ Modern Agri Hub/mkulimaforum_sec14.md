## 14. DevOps, Deployment & Scaling

East African agriculture's intense seasonality — planting (February–March) and harvest (June–July) driving 8-12× traffic spikes — demands serverless auto-scaling, African-region data residency, and automated CI/CD. MkulimaForum deploys multiple times daily without interruption, scaling from minimum off-season capacity to 100 containers during peak demand.

### 14.1 CI/CD Pipeline

#### 14.1.1 GitHub Actions Pipeline

Every pull request triggers a six-stage GitHub Actions pipeline.

| Stage | Tools | Purpose | Exit Criteria |
|-------|-------|---------|---------------|
| **Test** | PHPUnit, `flutter test` | Backend and mobile unit/integration tests | ≥90% coverage, zero failures |
| **Static Analysis** | PHPStan (level 9), `dart analyze`, SonarQube | Type safety, code smell detection | Zero errors, gate pass |
| **Security Scan** | Snyk, SonarQube SAST, Trivy | Dependency and container vulnerability scan | Zero critical/high findings |
| **Build** | Docker (FrankenPHP + Octane), `flutter build` | Multi-arch containers, APK/IPA | Signed artifacts |
| **Distribute** | Firebase App Distribution, Codemagic | Beta delivery to QA and stakeholders | Upload confirmed |
| **Deploy** | AWS ECS (staging → production) | Blue-green with auto-rollback | `/health` pass within 60 s |

Flutter AOT compiles to arm64 for East Africa's low-to-mid-range Android devices; Codemagic handles iOS signing. Blue-green deployment uses FrankenPHP's zero-downtime reload. Migrations run against the inactive (green) environment before traffic switches, preventing schema-lock timeouts on the `orders` table. Feature flags via Laravel Pennant enable canary rollouts to 5% of Tanzanian mkulima before full enablement. Three consecutive health-check failures trigger auto-rollback within 30 seconds.

```
┌─────────────────────────────────────────────────────────────────────┐
│                     CI/CD PIPELINE FLOW                             │
│                                                                     │
│  Developer pushes to feature/branch                                 │
│       │                                                             │
│       ▼                                                             │
│  ┌────────────┐   ┌──────────────┐   ┌──────────────┐             │
│  │   Test     │───│   Static     │───│   Security   │             │
│  │  (PHPUnit  │   │  Analysis    │   │    Scan      │             │
│  │  Flutter)  │   │ (PHPStan,    │   │ (Snyk,Trivy) │             │
│  └─────┬──────┘   └──────┬───────┘   └──────┬───────┘             │
│        │                  │                    │                     │
│        └──────────────────┼────────────────────┘                     │
│                           ▼                                         │
│                    ┌──────────────┐                                  │
│                    │ Build & Push │                                  │
│                    │ Docker Images│                                  │
│                    └──────┬───────┘                                  │
│                           │                                         │
│              ┌────────────┼────────────┐                            │
│              ▼            ▼            ▼                            │
│        ┌─────────┐  ┌──────────┐  ┌──────────┐                    │
│        │ Staging │  │ Firebase │  │ Codemagic│                    │
│        │ Deploy  │  │App Dist  │  │ iOS Build│                    │
│        └────┬────┘  │(Android) │  └──────────┘                    │
│             │       └──────────┘                                   │
│             ▼                                                       │
│        ┌─────────┐     Health OK?     ┌─────────┐                  │
│        │  Blue   │───────────────────→│  Live   │                  │
│        │  Deploy │     Fail?          │ Traffic │                  │
│        │         │◄───────────────────│         │                  │
│        └─────────┘   Auto-Rollback   └─────────┘                  │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 14.2 Infrastructure & Scaling

#### 14.2.1 Cloud Architecture

MkulimaForum deploys on AWS `af-south-1` (Cape Town), providing ~40–60 ms latency from Dar es Salaam and Nairobi [^73^]. The Nairobi Local Zone (`af-south-1-nbo-1a`) will host latency-sensitive services for sub-20 ms response [^69^]. African-region hosting is a compliance requirement: Tanzania's PDPA 2022 and Kenya's DPA 2019 impose strict cross-border transfer conditions [^62^][^63^]. The backend runs on AWS Fargate, auto-scaling from 2 tasks (minimum HA) to 100 during peak seasons. PostgreSQL uses RDS Multi-AZ with a read replica; Redis ElastiCache powers caching, queues, and sessions.

```
┌─────────────────────────────────────────────────────────────────────┐
│              MKULIMAFORUM AWS AFRICA DEPLOYMENT                     │
│                                                                     │
│   East Africa Users                                                 │
│        │                                                            │
│   ┌────┴────┐   CloudFront CDN (edge: NBO, DAR, EBB)              │
│   │ Flutter │──────────┬─────────────────┬──────────┐              │
│   │   App   │          │                 │          │              │
│   └────┬────┘          ▼                 ▼          ▼              │
│        │          ┌─────────┐      ┌──────────┐  ┌──────┐         │
│        │          │  S3     │      │  ALB     │  │ USSD │         │
│        │          │ Static  │      │ (API     │  │GW    │         │
│        │          │ Assets  │      │ Gateway) │  │      │         │
│        │          └─────────┘      └────┬─────┘  └──────┘         │
│        │                               │                           │
│        │                    ┌──────────┴──────────┐               │
│        │                    ▼                     ▼               │
│        │          ┌─────────────────┐    ┌──────────────┐        │
│        │          │ ECS Fargate     │    │  AWS WAF     │        │
│        │          │ (FrankenPHP +   │    │  (DDoS/XSS   │        │
│        │          │  Laravel)       │    │   filter)    │        │
│        │          │ ┌─────┐┌─────┐┌┴────┐└──────────────┘        │
│        │          │ │Web  ││Queue││Ws   │                       │
│        │          │ │Pods ││Pods ││Pods │                       │
│        │          │ └─────┘└─────┘└─────┘                       │
│        │          └────┬─────────────┬──────────┘               │
│        │               │             │                           │
│        │      ┌────────┴──────┐ ┌────┴────────┐                 │
│        │      ▼               ▼ ▼             ▼                 │
│        └─→ ┌──────────┐  ┌──────────┐  ┌──────────┐            │
│            │ RDS      │  │ElastiCache│ │  S3      │            │
│            │PostgreSQL│  │  Redis   │  │(images,  │            │
│            │Multi-AZ  │  │  Cluster │  │ backups) │            │
│            │+ Replica │  └──────────┘  └──────────┘            │
│            └──────────┘                                         │
│                                                                     │
│   Region: af-south-1 (Cape Town)   Latency: ~40-60ms              │
│   Future: Nairobi Local Zone       Latency: <20ms                 │
└─────────────────────────────────────────────────────────────────────┘
```

FrankenPHP serves Laravel via Octane, yielding 5-10× throughput over PHP-FPM by keeping the application resident in memory [^80^]. Laravel Reverb handles WebSockets with 40% lower latency at 90% lower cost than third-party alternatives [^60^][^65^]. Horizontal Pod Autoscaling triggers on CPU (>70%), memory (>80%), and request rate (>1,000 RPM per pod). PgBouncer pools database connections — load testing showed 2,000 concurrent price checks exhaust un-pooled RDS limits. CloudFront reduces origin load by ~60%.

### 14.3 Monitoring, Observability & Disaster Recovery

Laravel Pulse tracks slow queries, queue throughput, cache hit rates, and exceptions. Sentry correlates backend errors with releases; Firebase Crashlytics covers Flutter crashes. The 99.9% SLA allows 43 minutes of downtime monthly. Prometheus scrapes infrastructure every 15 s; Grafana visualizes P95 latency per tenant. PagerDuty alerts on P95 latency >500 ms, error rate >1%, and queue depth >10,000 jobs for >5 minutes. PostgreSQL PITR enables restoration within a 24-hour RPO; daily snapshots are retained 30 days with weekly copies to `eu-west-1`. The most recent quarterly DR drill achieved a 34-minute RTO.

### 14.4 Infrastructure Cost Projection

| Component | 10,000 Users | 50,000 Users | 100,000 Users | Scaling Driver |
|-----------|-------------:|-------------:|--------------:|----------------|
| **ECS Fargate** | $340 | $1,280 | $2,400 | Request volume, seasonality |
| **RDS PostgreSQL (Multi-AZ)** | $285 | $520 | $840 | Connections, query complexity |
| **RDS Read Replica** | — | $285 | $520 | Reporting/analytics load |
| **ElastiCache Redis** | $95 | $185 | $340 | Sessions, cache, queues |
| **CloudFront CDN** | $65 | $220 | $410 | Asset delivery volume |
| **S3 Storage** | $25 | $85 | $170 | User-generated content |
| **WAF + ALB** | $120 | $120 | $185 | Fixed + rule evaluation |
| **Data Transfer** | $45 | $160 | $310 | API egress |
| **Monitoring** | $85 | $165 | $310 | Error volume, team size |
| **Monthly Total** | **$1,050** | **$3,020** | **$5,485** | |
| **Per-User Cost** | **$0.105** | **$0.060** | **$0.055** | |

The per-user cost declines 48% from $0.105 to $0.055 as fixed infrastructure amortizes across users. Seasonal peaks add 15–25% to compute during planting and harvest months. Infrastructure spend stays ~2.3% of projected revenue, within the 5–8% SaaS benchmark.
