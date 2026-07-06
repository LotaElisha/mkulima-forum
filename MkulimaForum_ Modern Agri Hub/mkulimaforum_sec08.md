## 8. Flutter Frontend Architecture — Clean Architecture, Modern UI

### 8.1 Architecture Patterns

#### 8.1.1 Clean Architecture Layers

MkulimaForum's Flutter client follows Clean Architecture, a layered pattern that separates concerns through concentric dependency rings. Each layer knows only about the layer immediately inward, producing a codebase that is testable, framework-independent, and amenable to large-team development. The three principal layers are:

**Presentation** — Flutter widgets plus BLoC (Business Logic Component) state containers managed by `flutter_bloc` 8.x. BLoCs expose `Stream`-based state objects and consume events from the UI, yielding unidirectional data flow that is deterministic and replayable [^20^].

**Domain** — Pure Dart entities, use cases (interactors), and repository interfaces. This layer has zero external dependencies; it defines *what* the application does, not *how*.

**Data** — Repository implementations, API clients (Dio with interceptors), and local persistence (Drift/Hive). This layer translates between external data formats and domain entities [^20^].

Dependency injection wires concrete implementations to abstract interfaces via GetIt, allowing test doubles to be substituted without modifying presentation code.

The layer diagram below illustrates the dependency direction (inward-pointing arrows) and the boundary protocols between rings.

```
┌──────────────────────────────────────────────────────────────────┐
│                      PRESENTATION LAYER                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │   Material 3  │  │  BLoC State  │  │   UI Events /        │   │
│  │   Widgets     │  │  Controllers │  │   StreamBuilder      │   │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────┘   │
│         │                  │                      │                │
│         ▼                  ▼                      ▼                │
│  ┌──────────────────────────────────────────────────────────┐    │
│  │  Domain boundary: Repository interfaces (abstract)       │    │
│  │  Use case interactor calls                               │    │
│  └────────────────────────────┬─────────────────────────────┘    │
└───────────────────────────────┼──────────────────────────────────┘
                                │
┌───────────────────────────────▼──────────────────────────────────┐
│                         DOMAIN LAYER                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │   Entities    │  │  Use Cases   │  │  Repository Contracts │   │
│  │  (pure Dart)  │  │  (business   │  │  (interfaces only)    │   │
│  │               │  │   rules)     │  │                       │   │
│  └───────────────┘  └──────┬───────┘  └──────────────────────┘   │
│                            │ DI via GetIt                         │
└────────────────────────────┼──────────────────────────────────────┘
                             │
┌────────────────────────────▼──────────────────────────────────────┐
│                          DATA LAYER                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │   Drift DB    │  │  Dio API     │  │  Repository Impl      │    │
│  │  (SQLite)     │  │  Client      │  │  (concrete)           │    │
│  └───────────────┘  └──────┬───────┘  └──────────────────────┘    │
│                            │                                       │
└────────────────────────────┼───────────────────────────────────────┘
                             │
                    ┌────────▼────────┐
                    │  REST/JSON API  │
                    │  (Laravel 13)   │
                    └─────────────────┘
```

*Diagram: Clean Architecture dependency graph. Arrows point inward; the Domain layer has no external dependencies.*

The BLoC below demonstrates the event-driven, cache-first pattern used across all feature modules. On `LoadProductsEvent`, the bloc first emits a loading state with shimmer configuration, then queries the local Drift database for cached data, and finally fetches fresh data from the Laravel backend via Dio.

```dart
// lib/features/marketplace/presentation/bloc/product_bloc.dart
@injectable
class ProductBloc extends Bloc<ProductEvent, ProductState> {
  final GetProductsUseCase _getProducts;
  final ProductLocalDatasource _local;

  ProductBloc(this._getProducts, this._local)
      : super(const ProductState.loading()) {
    on<LoadProductsEvent>(_onLoad);
    on<RefreshProductsEvent>(_onRefresh);
    on<FilterByCategoryEvent>(_onFilter);
  }

  Future<void> _onLoad(
    LoadProductsEvent event,
    Emitter<ProductState> emit,
  ) async {
    emit(const ProductState.loading());

    // 1. Emit cached data immediately (offline-first)
    final cached = await _local.getProducts(category: event.category);
    if (cached.isNotEmpty) {
      emit(ProductState.loaded(cached, fromCache: true));
    }

    // 2. Fetch from API, normalize errors
    final result = await _getProducts(
      Params(category: event.category, region: event.region),
    );

    result.fold(
      (failure) => emit(ProductState.error(failure.message)),
      (products) => emit(ProductState.loaded(products, fromCache: false)),
    );
  }
}
```

#### 8.1.2 State Management with BLoC

Every feature module (marketplace, disease scanner, forum, services) is organized as a self-contained package with its own BLoC, events, and states. This feature-based modularity prevents merge conflicts in large teams and enables on-demand code splitting. BLoC states are immutable, constructed with `freezed` unions that model every UI phase: `initial`, `loading`, `loaded`, `empty`, `error`, and `offline`. Offline state detection uses `connectivity_plus` to monitor network transitions; when connectivity drops, the UI automatically surfaces cached data with a non-intrusive offline banner [^24^].

Loading states render Material 3 shimmer skeletons via the `shimmer` package, maintaining perceived performance by avoiding blank screens. Error normalization maps server-side exceptions (timeout, 4xx, 5xx) to user-facing Swahili/English messages through a centralized `Failure` hierarchy.

#### 8.1.3 Offline-First Data Layer

The offline-first philosophy treats the local database as the single source of truth: reads resolve locally (instant, always available), writes commit locally first, and synchronization occurs asynchronously in the background [^20^]. This design is non-negotiable for MkulimaForum because rural agricultural areas across East Africa experience intermittent or absent connectivity, with actual mobile internet usage significantly below subscription figures [^20^].

Drift (formerly Moor) serves as the relational local database, providing type-safe SQL queries, schema migrations, and reactive streaming that integrates natively with BLoC's `StreamBuilder` [^20^]. Hive complements Drift for lightweight key-value caching (auth tokens, user settings, API response metadata). The custom SyncEngine orchestrates bidirectional synchronization through a database-backed outbox queue that survives application crashes and device restarts [^25^].

The Drift schema below defines core tables for marketplace products and the sync outbox, which records every pending mutation with CRDT vector-clock metadata for conflict resolution.

```dart
// lib/core/data/drift/app_database.dart
@DriftDatabase(tables: [Products, SyncOutbox, Diagnoses, ForumPosts])
class AppDatabase extends _$AppDatabase {
  AppDatabase() : super(impl.connect());

  @override
  int get schemaVersion => 4;

  // Stream-based reactive queries for BLoC consumption
  Stream<List<Product>> watchProductsByCategory(String category) {
    return (select(products)
      ..where((p) => p.category.equals(category))
      ..orderBy([(p) => OrderingTerm.desc(p.updatedAt)]))
      .watch();
  }

  // Outbox: queued mutations for background sync
  Future<int> enqueueMutation(Insertable<SyncOutboxData> row) {
    return into(syncOutbox).insert(row);
  }

  @override
  MigrationStrategy get migration => MigrationStrategy(
        onCreate: (m) => m.createAll(),
        onUpgrade: (m, from, to) => runMigrationSteps(
          migrator: m,
          from: from,
          to: to,
          steps: migrationSteps(
            from1To2: (m, schema) async {/* v2 */},
            from2To3: (m, schema) async {/* v3 */},
            from3To4: (m, schema) async {/* v4: CRDT vector clocks */},
          ),
        ),
      );
}
```

The SyncEngine, shown below in outline form, is the heart of the offline-first system. It exposes four sub-services: OutboxService manages the durable mutation queue; PushService uploads changes to the Laravel backend; PullService fetches delta responses from `/sync?since=timestamp`; and ConflictService applies CRDT semantics to resolve divergent updates [^25^][^79^].

```dart
// lib/core/sync/sync_engine.dart
class SyncEngine {
  final OutboxService _outbox;
  final PushService _push;
  final PullService _pull;
  final ConflictService _conflict;
  final ConnectivityMonitor _connectivity;

  SyncEngine(this._outbox, this._push, this._pull,
             this._conflict, this._connectivity) {
    // Trigger sync on connectivity restoration
    _connectivity.onOnline.listen((_) => _performSync());
  }

  Future<void> _performSync() async {
    final pending = await _outbox.pendingOperations();
    for (final op in pending) {
      try {
        await _push.send(op);
        await _outbox.markSent(op.id);
      } on ConflictException catch (e) {
        final resolved = await _conflict.resolve(op, e.serverVersion);
        await _outbox.updateWithResolution(op.id, resolved);
      } on NetworkException {
        // Exponential backoff; operation remains in outbox
        await _outbox.incrementRetry(op.id);
      }
    }
    // Pull server-side changes after push completes
    final lastSync = await _outbox.lastSyncTimestamp();
    final delta = await _pull.fetchSince(lastSync);
    await _conflict.mergeDeltas(delta);
  }
}
```

The server exposes `GET /sync?since={timestamp}` returning only changed records since the client's last sync, minimizing payload size over slow rural connections. Conflict resolution uses state-based CRDTs: G-Counter for upvote tallies (commutative, grow-only) and LWW-Element-Set for forum post collections [^79^][^82^]. Background sync is scheduled through `WorkManager` with battery-aware constraints, ensuring queued operations are retried even when the app is in the background [^25^].

| Component | Technology | Responsibility | Key Behavior |
|-----------|-----------|----------------|-------------|
| Local Database | Drift (SQLite) | Relational data, streaming queries | Type-safe, migration-ready, source of truth for reads [^20^] |
| Key-Value Cache | Hive | Auth tokens, user settings, API metadata | Lightweight, encrypted at rest, <1 ms access |
| Outbox Queue | Drift table `sync_outbox` | Durable mutation log | Survives crashes, records retry count with exponential backoff [^25^] |
| Push Service | Dio + REST | Upload changes to Laravel | Batches mutations, handles 409 Conflict responses |
| Pull Service | Delta sync API | Fetch server changes | `GET /sync?since=timestamp` returns only deltas |
| Conflict Resolver | CRDT (G-Counter, LWW-Set) | Merge divergent updates | Mathematically convergent, no central lock required [^79^] |
| Background Scheduler | WorkManager | Retry when connectivity returns | Battery-aware, persists across restarts [^25^] |
| Connectivity Monitor | connectivity_plus | Detect online/offline transitions | Triggers sync on restoration, surfaces UI banner [^24^] |

*Table: Offline-First Sync Architecture components. Each element is independently replaceable; the outbox queue is the central durability mechanism.*

The outbox pattern is the critical architectural choice that differentiates MkulimaForum from naive sync implementations. By persisting every pending mutation to SQLite rather than holding it in memory, the system guarantees that a farmer's marketplace order or disease diagnosis submission is never lost, even if the device loses power immediately after the user taps "submit." Exponential backoff with jitter prevents thundering-herd behavior when connectivity is restored across many devices simultaneously.


### 8.2 Modern UI Implementation

#### 8.2.1 Material 3 Design System

MkulimaForum adopts Material 3 (codename "You") with a custom color scheme derived from the brand's agrarian identity: forest green primary (`#5B8C5A`), moss secondary (`#7BA05B`), and sage tertiary (`#9DC183`). Dynamic theming generates surface tints from the user's wallpaper on Android 12+, personalizing the interface without custom asset work. Glassmorphism cards with `BackdropFilter` blur elevate content above full-bleed agricultural photography while maintaining text legibility through semi-transparent scrim layers.

Dark mode is the default — it reduces battery drain on OLED panels (common in mid-range devices) and minimizes eye strain during early-morning and late-evening farm checks. Predictive back gestures (Android 13+) provide animated previews of the previous screen, reinforcing navigation orientation. Built-in widgets introduced in Flutter 3.24 — `CarouselView` for product browsing and `TreeView` for forum thread navigation — reduce custom widget count and improve accessibility out of the box [^21^].

Shimmer skeleton screens, implemented via the `shimmer` package, replace traditional loading spinners. They mirror the final layout's structure (card heights, text line counts) so the transition from loading to loaded state is visually continuous, reducing cognitive disruption.

#### 8.2.2 Adaptive Layouts

The device landscape across East Africa spans feature phones (USSD fallback), low-end Android with 4-inch screens, mid-range 6.5-inch smartphones, and tablets used by agrodealers for inventory management. MkulimaForum's responsive system targets 4-7 inch screens as the primary breakpoint, with expanded layouts for tablet agrodealer dashboards.

| Tier | Screen Size | RAM | Target APK | Optimization Strategy |
|------|------------|-----|-----------|----------------------|
| Entry | 4.0-4.7 in | 1-2 GB | <15 MB (per-ABI) | Disable animations, reduce image quality, use Impeller software fallback |
| Primary | 5.0-6.7 in | 3-4 GB | <25 MB (per-ABI) | Full Material 3, progressive images, deferred heavy widgets |
| Premium | 6.7+ in | 6+ GB | <30 MB (universal) | All effects, 60 fps target, Flutter GPU API preview [^27^] |
| Tablet | 8-10 in | 4+ GB | <30 MB (universal) | Side-panel layouts, data-dense tables, multi-select for inventory |

*Table: Device tier specifications. Per-ABI APK splitting (arm64-v8a, armeabi-v7a) reduces download size by 30-40% on the Google Play Store.*

All interactive elements maintain a minimum 16 dp (density-independent pixel) touch target, exceeding the WCAG 2.1 Level AA minimum for pointer target size. High contrast mode boosts the contrast ratio to 7:1 for text on all surfaces, supporting users with low vision or those operating devices under bright outdoor conditions. Screen reader support via TalkBack/VoiceOver is validated on every release through automated accessibility audits. Large text scaling (up to 200%) uses `MediaQuery.textScalerOf` to reflow layouts without truncation, a critical accommodation for the 15%+ of smallholder farmers aged 55 and above.

#### 8.2.3 Performance Strategy

Flutter's Impeller rendering engine eliminates shader compilation jank by pre-compiling shaders at build time, replacing the runtime compilation that previously caused frame drops on first animation [^21^][^22^]. On iOS and macOS, Impeller is the default renderer; on Android, it is enabled for supported devices and falls back to Skia on older chipsets. The performance target is 60 frames per second (frame time <16 ms), verified through Flutter DevTools timeline profiling.

Image loading uses progressive JPEG decoding via `cached_network_image` with placeholder blur-hash thumbnails, giving users visible content within 100 ms even on 2G connections. List views implement lazy loading with pagination (page size 20-50 items) to keep memory footprint constant regardless of catalog size [^24^]. Heavy widgets — charts, maps, rich text editors — are deferred through `deferFirstFrame` and loaded only when scrolled into the viewport. The APK size target of <30 MB (universal) or <15 MB (per-ABI split) is enforced through tree shaking, resource stripping, and selective dependency inclusion; `flutter build apk --split-per-abi` is the standard CI artifact [^74^].


### 8.3 Module-Specific Frontend Patterns

#### 8.3.1 Marketplace UI

The marketplace module presents a product grid with faceted filters (category, price range, TFRA verification status, distance from farm). `CarouselView` renders featured products horizontally with parallax scrolling, while the main grid uses `GridView.builder` with 2-column layout on phones and 3-column on tablets. Cart management implements swipe-to-delete with `Dismissible` widgets and real-time escrow-aware price computation. Swahili autocomplete and voice search (via `speech_to_text`) lower the barrier for farmers with limited typing proficiency, reflecting Insight 5 that voice-first design is the primary interface for 60%+ of the addressable market.

#### 8.3.2 Disease Scanner UX

The disease scanner follows a six-stage user flow designed for high-stress field conditions: (1) camera capture with a real-time focus reticle and overlay guides; (2) scanning animation with pulsing ring; (3) results screen displaying diagnosis name, confidence percentage rendered as a radial gauge, and severity color coding; (4) treatment recommendations drawn from the TARI knowledge base; (5) direct product links to verified inputs in the marketplace; (6) one-tap save to history for offline reference. The TensorFlow Lite model (MobileNetV3-Small, 2.5 MB) runs on-device for the 20 most common diseases; uncertain classifications trigger a cloud fallback to Gemini Vision for complex cases [^27^].

#### 8.3.3 Services Booking Flow

The services module (agronomist hiring, soil testing, logistics booking) implements a discovery-to-completion pipeline: category browser with icon grid → provider listing integrated with `flutter_map` for proximity visualization → profile page with review breakdown and expert badges → booking calendar with availability slots → escrow payment confirmation → booking dashboard with status timeline. Each provider undergoes a 4-tier verification system, and the booking state machine mirrors the marketplace order lifecycle for consistency.

#### 8.3.4 Forum UI

Community discussion uses a thread list with upvote/downvote controls backed by G-Counter CRDTs for conflict-free tallying. The rich text editor supports voice note attachment (recorded via `flutter_sound`), inline image galleries, and `@mention` auto-completion. Regional sub-forum tabs (`tz-mwanza`, `ke-rift-valley`) segment content by geography. Expert badges (verified agronomist, TARI researcher, KEPHIS inspector) appear as avatars with trust-icon overlays. An AI-suggested questions panel, powered by Gemini 2.0 Flash with RAG over the forum corpus, surfaces related discussions before a user posts a duplicate query, reducing moderator workload and improving information discoverability.

The sync flow diagram below illustrates the end-to-end path of an offline write — in this example, a forum reply drafted without connectivity — from local commit through outbox queue to server reconciliation and final UI update.

```
┌──────────────────────────────────────────────────────────────────────┐
│                       OFFLINE-FIRST SYNC FLOW                       │
│                                                                     │
│   ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐   │
│   │   User   │───►│  Local   │───►│  Outbox  │───►│ WorkMgr  │   │
│   │  Action  │    │  Drift   │    │  Queue   │    │  Sync    │   │
│   │ (reply)  │    │  Write   │    │  (CRDT)  │    │  Trigger │   │
│   └──────────┘    └──────────┘    └──────────┘    └─────┬────┘   │
│                                                          │         │
│   ┌──────────────────────────────────────────────────────┘         │
│   │                                                                 │
│   ▼                                                                 │
│   ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    │
│   │  Delta   │───►│  Laravel │───►│ Conflict │───►│   BLoC   │    │
│   │  Sync    │    │  Backend │    │ Resolve  │    │  Update  │    │
│   │  API     │    │  (v13)   │    │  (CRDT)  │    │   UI     │    │
│   └──────────┘    └──────────┘    └──────────┘    └──────────┘    │
│                                                                     │
│   Step 1: User writes reply offline → Drift persists immediately    │
│   Step 2: Outbox records mutation with vector clock (HLC)           │
│   Step 3: WorkManager retries on connectivity restored [^25^]       │
│   Step 4: Delta sync API (`/sync?since=t`) sends minimal payload    │
│   Step 5: Laravel 13 backend applies, returns 200 or 409            │
│   Step 6: ConflictService merges if server diverged [^79^]          │
│   Step 7: BLoC emits new state → StreamBuilder rebuilds UI          │
└──────────────────────────────────────────────────────────────────────┘
```

*Diagram: Offline-first sync flow for a forum reply. The outbox queue guarantees durability across app restarts and network interruptions; CRDT semantics ensure all devices converge to the same state without centralized locking.*
