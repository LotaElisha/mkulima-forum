## 6. Database Architecture — PostgreSQL + pgvector + Advanced Schema

MkulimaForum's data layer unifies seven functional domains across four East African countries inside a single PostgreSQL 16 instance. PostgreSQL was selected because it combines relational transactions, vector search for AI retrieval, geospatial queries, and row-level multi-tenant security — eliminating the operational complexity of running separate databases, a critical factor where database administration expertise is scarce [^28^].

### 6.1 PostgreSQL Schema Design

#### 6.1.1 Core Schema

The schema spans 43 tables across seven domains, each using UUID primary keys and `timestamptz` for cross-border operations in East Africa Time (UTC+3).

**Table 1 — Core Schema Entity Reference by Domain**

| Domain | Tables | Purpose |
|--------|--------|---------|
| Identity | `users`, `profiles`, `kyc_verifications`, `roles` | Authentication, RBAC, TFRA/KEPHIS verification |
| Marketplace | `products`, `categories`, `orders`, `order_items`, `reviews` | Agro-input e-commerce with escrow |
| Community | `forum_threads`, `forum_posts`, `notifications` | Farmer Q&A and discussion |
| Agronomy | `diagnoses`, `crop_diseases`, `knowledge_chunks`, `soil_tests` | Disease scans, RAG knowledge, soil analysis |
| Services | `service_bookings`, `service_providers`, `availability_slots` | Agronomist/veterinary booking with 4-tier vetting |
| Finance | `wallets`, `transactions`, `escrow_releases` | Mobile money; `transactions` partitioned monthly |
| Logistics | `deliveries`, `tracking_events`, `warehouses` | Delivery routing; `warehouses` uses PostGIS Point |

The schema encodes agricultural specificity throughout. The `crop_diseases` table maps diagnoses to TARI treatment protocols. `knowledge_chunks` stores vector embeddings of TARI, FAO, and KEPHIS publications for RAG-powered AI advice [^1^]. Soil tests use JSONB for nutrient breakdowns because analytes differ between iSDAsoil API data (30m resolution, 15+ variables at two depths [^3^]) and physical lab reports.

#### 6.1.2 Multi-Tenancy with Row-Level Security

MkulimaForum uses shared-database, shared-schema multi-tenancy with `country_code` as the tenant discriminator, avoiding per-country database proliferation while satisfying Tanzania's PDPA 2022 and Kenya's DPA 2019 [^62^][^63^]. PostgreSQL RLS policies enforce isolation at the database level.

```sql
ALTER TABLE products ENABLE ROW LEVEL SECURITY;
ALTER TABLE orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE knowledge_chunks ENABLE ROW LEVEL SECURITY;

CREATE POLICY country_isolation ON products
    USING (country_code = current_setting('app.current_country', true));
```

**Diagram 1 — Multi-Tenancy RLS Enforcement Flow**

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│  Client Request │────►│ Tenant Resolution │────►│ Subdomain/Header    │
│                 │     │   Middleware       │     │  tz.mkulimaforum    │
└─────────────────┘     └──────────────────┘     └─────────────────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │ SET app.current_     │
                    │     country = 'TZ'   │
                    └──────────────────────┘
                               │
                               ▼
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│  Laravel Eloquent│────►│  Global Scope    │────►│  WHERE country_code  │
│  Query Builder   │     │  (TenantScope)   │     │  = 'TZ' appended    │
└─────────────────┘     └──────────────────┘     └─────────────────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │ PostgreSQL RLS       │
                    │ Policy Enforcement   │
                    │ (database-level)     │
                    └──────────────────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │  Tenant-Scoped       │
                    │  Result Set          │
                    └──────────────────────┘
```

Tenant resolution occurs via subdomain (`tz.mkulimaforum.com`), URL path, or `X-Country-Code` header. Laravel middleware sets `app.current_country` before any query executes; a `TenantScope` global scope on all tenant-aware models appends the country filter automatically [^33^][^36^]. Composite unique indexes incorporate `country_code` so the same phone format can exist once per country without conflict.

#### 6.1.3 Partitioning and Read Scaling

The `orders` and `transactions` tables use native PostgreSQL 16 monthly range partitioning on `created_at`, keeping partitions below 50 million rows. Automated jobs create partitions three months ahead and archive partitions older than 24 months to S3 in `af-south-1`. Read replicas serve reporting; PgBouncer in transaction pooling manages short-lived mobile connections [^69^].

### 6.2 Vector Database with pgvector

#### 6.2.1 pgvector Performance

The pgvector extension provides `vector` data types and HNSW indexing. With pgvectorscale optimizations, it achieves 471 QPS at 28 ms p95 with 50 million vectors — outperforming Qdrant (41 QPS) and Weaviate (~50-80 QPS) at this scale [^28^][^29^]. MkulimaForum's initial knowledge base of ~500,000 chunks (TARI publications, FAO guidelines, KEPHIS alerts, moderated farmer Q&A) operates well within this envelope.

**Table 2 — pgvector vs Qdrant vs Weaviate Benchmark**

| Metric | pgvector + pgvectorscale | Qdrant | Weaviate |
|--------|--------------------------|--------|----------|
| QPS at 50M vectors | 471 [^28^] | 41 [^29^] | ~50-80 |
| p95 Latency at 50M | 28 ms [^28^] | Higher | Higher |
| Additional Infrastructure | None | Separate cluster | Separate cluster |
| Multi-Tenancy | PostgreSQL RLS | Payload filter | Native tenants |
| African Deployment Fit | Excellent [^28^] | Good | Moderate |

Running vectors in the same PostgreSQL instance as transactional data means embedding inserts and metadata updates participate in the same ACID transaction — knowledge chunks and their relational metadata commit atomically.

#### 6.2.2 RAG Knowledge Base Schema

**Table 3 — RAG Knowledge Base Schema (`knowledge_chunks`)**

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID PRIMARY KEY | Chunk identifier |
| `content` | TEXT | Plain text passage (200-500 tokens) |
| `embedding` | vector(1536) | OpenAI text-embedding-3-small |
| `source` | VARCHAR(32) | `TARI`, `FAO`, `KEPHIS`, `FARMER_QA` |
| `metadata` | JSONB | Crop type, region, season, language |
| `country_code` | CHAR(2) | Tenant scope; RLS policy applies |

```sql
CREATE EXTENSION IF NOT EXISTS vector;

CREATE TABLE knowledge_chunks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    content TEXT NOT NULL,
    embedding vector(1536) NOT NULL,
    source VARCHAR(32) NOT NULL,
    source_doc_id VARCHAR(128) NOT NULL,
    metadata JSONB NOT NULL DEFAULT '{}',
    country_code CHAR(2) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_knowledge_embedding_hnsw ON knowledge_chunks
    USING hnsw (embedding vector_cosine_ops)
    WITH (m = 16, ef_construction = 64);

CREATE INDEX idx_knowledge_metadata ON knowledge_chunks USING GIN (metadata);
```

HNSW parameters (`m = 16`, `ef_construction = 64`) target >95% recall@10, verified through cross-encoder reranking before LLM context injection [^1^].

#### 6.2.3 Semantic Search Pipeline

Vector similarity alone misses domain terminology ("njaa" for Fall Armyworm). MkulimaForum uses a three-stage pipeline: vector similarity via pgvector, full-text search via `tsvector`, and cross-encoder reranking.

**Diagram 2 — pgvector RAG Query Flow**

```
┌─────────────────────────────────────────────────────────────────────┐
│                     RAG SEMANTIC SEARCH PIPELINE                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  STAGE 1: QUERY PROCESSING                                          │
│  ┌─────────────┐    ┌──────────────┐    ┌─────────────────────┐   │
│  │ User Query   │───►│ Query Intent │───►│ Entity Extraction   │   │
│  │ (Sw/En)      │    │ Classification│    │ Crop, Region, Stage │   │
│  └─────────────┘    └──────────────┘    └─────────────────────┘   │
│                              │                                       │
│                              ▼                                       │
│  STAGE 2: DUAL RETRIEVAL                                          │
│  ┌─────────────────┐      ┌─────────────────┐                      │
│  │ Vector Search    │      │ Full-Text Search │                     │
│  │ (pgvector HNSW)  │      │ (tsvector GIN)   │                     │
│  │ embedding <=>    │      │ ts_rank(fts,     │                     │
│  │ query_vec LIMIT  │      │ query) DESC      │                     │
│  │ 50               │      │ LIMIT 50         │                     │
│  └────────┬────────┘      └────────┬────────┘                     │
│           │                        │                                │
│           └────────┬───────────────┘                                │
│                    ▼                                                │
│           ┌─────────────────┐                                       │
│           │ RRF Fusion (k=60)│                                      │
│           └────────┬────────┘                                       │
│                    ▼                                                │
│  STAGE 3: Cross-Encoder Rerank (top-20 → top-5)                   │
│                    │                                                │
│                    ▼                                                │
│  ┌─────────────────────────────────────────────────────┐           │
│  │ Top-5 chunks → Gemini 2.0 Flash with citations      │           │
│  └─────────────────────────────────────────────────────┘           │
└─────────────────────────────────────────────────────────────────────┘
```

```sql
WITH vector_results AS (
    SELECT id, content, source, metadata,
        1 - (embedding <=> :query_embedding::vector) AS score
    FROM knowledge_chunks
    WHERE country_code = :country_code
      AND metadata @> '{"crop_type": :crop_type}'
    ORDER BY embedding <=> :query_embedding::vector
    LIMIT 50
),
text_results AS (
    SELECT id, content, source, metadata,
        ts_rank(to_tsvector('simple', content),
                plainto_tsquery('simple', :query_text)) AS score
    FROM knowledge_chunks
    WHERE country_code = :country_code
      AND metadata @> '{"crop_type": :crop_type}'
      AND to_tsvector('simple', content)
          @@ plainto_tsquery('simple', :query_text)
    ORDER BY score DESC
    LIMIT 50
)
SELECT id, content, source, metadata,
    COALESCE(1.0 / (60 + v.rn), 0) +
    COALESCE(1.0 / (60 + t.rn), 0) AS rrf_score
FROM vector_results v
FULL OUTER JOIN text_results t USING (id)
ORDER BY rrf_score DESC
LIMIT 20;
```

Reciprocal Rank Fusion with $k = 60$ combines rankings. Metadata filters on `crop_type` and `country_code` execute via the GIN index before vector comparison, reducing the candidate set [^1^][^7^].

### 6.3 Specialized Data Types

#### 6.3.1 PostGIS for Geospatial

PostGIS stores farm boundaries as `geometry(Polygon, 4326)`, warehouses as `geometry(Point, 4326)`, and delivery routes as `geometry(LineString, 4326)`. GIST spatial indexes support sub-second radius searches. The bodaboda dispatch query uses `ST_DWithin` with a 10 km radius on `geography` casts for accurate spheroid distance across East African terrain [^69^].

#### 6.3.2 JSONB for Flexible Attributes

Product specifications vary by type (fertilizer N-P-K vs. seed germination rates) and country. The `attributes` JSONB column stores type-specific key-value pairs indexed via GIN, avoiding schema migrations when adding new product categories.

#### 6.3.3 Full-Text Search

Meilisearch (via Laravel Scout) powers typo-tolerant autocomplete for product names and forum titles. PostgreSQL `tsvector` handles in-content search with a custom Swahili dictionary mapping agricultural synonyms ("njaa" → "Fall Armyworm", "mnyoo" → "Cassava Brown Streak Disease"), ensuring Swahili queries retrieve English knowledge base entries [^1^].

The consolidated PostgreSQL instance deploys on RDS Multi-AZ in `af-south-1` with read replicas. When the AWS Nairobi Local Zone becomes available, a replica there reduces East African latency from ~45-65 ms to under 20 ms [^69^].
