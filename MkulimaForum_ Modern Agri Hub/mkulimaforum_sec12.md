## 12. Real-Time, Logistics & Maps

### 12.1 Real-Time Communication Architecture

#### 12.1.1 Laravel Reverb: First-Party WebSocket at 90% Cost Reduction

MkulimaForum's real-time layer handles order updates, delivery tracking, chat, agronomist appointments, and pest alerts — all patterns demanding persistent, low-latency connections. The platform runs **Laravel Reverb**, a first-party WebSocket server co-located with the API layer via `php artisan reverb:start` [^60^].

Reverb delivers a **90% cost reduction** versus Pusher: a comparable Pusher deployment costs ~\$1,200/year, whereas Reverb on Laravel Cloud runs at \$5–\$50/month fixed [^65^]. Latency drops **40%** because messages traverse the same data center rather than transiting Pusher's US-East or EU endpoints [^65^]. Authentication is native to Laravel Sanctum; private and presence channels use standard gate policies [^64^].

A **triple-redundant cascade** guarantees delivery when connectivity degrades:

```
Event Dispatched (Laravel)
    |
    +--[1]--> WebSocket (Reverb)  --> Active app users (instant, < 100 ms)
    +--[2]--> FCM Push            --> Background/offline devices
    +--[3]--> SMS (Africa's Talking) --> Feature phones / last-resort fallback
```

Step 1 targets foreground app users. Step 2 reaches backgrounded devices via Firebase Cloud Messaging (FCM). Step 3 fires only when Steps 1 and 2 both fail acknowledgment within 60 seconds, dispatching an SMS at \$0.0075/message [^19^].

#### 12.1.2 Push Notifications: Firebase Cloud Messaging

Devices register FCM tokens on first launch, stored in a `push_tokens` table. The backend publishes to **topic channels**, decoupling dispatch from device churn: `region_tz_arusha_alerts` for pest alerts, `user_{uuid}_orders` for personal order updates, and `weather_tz_all` for severe weather warnings. Quiet hours respect the device timezone (Africa/Dar_es_Salaam, Africa/Nairobi). Rich notifications carry action buttons ("Confirm Delivery", "View Map", "Call Driver") handled by `firebase_messaging` in Flutter [^65^].

#### 12.1.3 Background Location and Geofencing

The driver app collects GPS coordinates every 10 seconds via Flutter `geolocator`. Battery drain is kept **below 5% per hour** through adaptive sampling: 10-second intervals during transit, 60-second intervals when stationary, and GPS shutdown within 100 meters of the destination [^65^].

Geofencing is implemented server-side with PostGIS and Turf.js. Circular geofences trigger events on boundary crossings:

```php
SELECT id, ST_DWithin(
    pickup_location::geography,
    ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, 200
) AS within_pickup_zone
FROM deliveries WHERE status = 'en_route' AND driver_id = ?;
```

Pest alert geofences default to a **50 km radius**, calibrated to Fall Armyworm (FAW) dispersal range. When a farmer reports an outbreak, every user whose farm boundary (stored as a PostGIS `POLYGON`) intersects the alert circle receives a notification within 2–3 seconds via the Reverb → FCM cascade.

### 12.2 Maps & Routing

#### 12.2.1 Mapbox Primary: Cost-Optimized Mapping

MkulimaForum uses **Mapbox** as the primary mapping provider with OpenStreetMap (OSM) via MapLibre as a zero-cost fallback for offline and rural contexts.

| Component | Mapbox | Google Maps | OSM + MapLibre |
|:---|:---|:---|:---|
| Maps/Tiles (monthly) | ~\$0 (free: 50K loads) [^48^] | ~\$2,000/mo [^48^] | Free |
| Geocoding | ~\$0 (free: 100K) [^48^] | ~\$2,500/mo [^48^] | Free (Nominatim) |
| Directions | ~\$400/mo [^48^] | ~\$600/mo [^48^] | Self-hosted OSRM |
| Places/Search | ~\$1,500/mo [^48^] | ~\$2,000/mo [^48^] | Limited |
| Map Matching | ~\$425/mo [^48^] | ~\$500/mo [^48^] | Not available |
| **Total at 10K users** | **~\$2,325/mo** [^48^] | **~\$7,600/mo** [^48^] | **~\$80/mo hosting** |
| Custom styling | Full | Limited | Full |
| Offline tiles | Yes (MBTiles) | No | Yes |

Mapbox is **69% cheaper** than Google Maps at this scale [^48^]. A critical cost control: Mapbox Search Box bills at \$0.005 per keystroke. MkulimaForum implements a **300 ms debounce** on all search inputs, preventing 10–20× cost inflation from unbatched keystrokes [^48^]. Custom agricultural markers (shamba boundaries, warehouse icons, driver pins) render via Mapbox's runtime styling API. MBTiles local caching enables offline navigation in areas where mobile data is absent — a condition affecting roughly 60% of rural Tanzania [^65^].

#### 12.2.2 Route Optimization

Mapbox Directions serves as the primary routing API. A **self-hosted OSRM** instance on the same VPC provides hot fallback when Mapbox latency exceeds 500 ms or returns no route for rural waypoints. OSRM processes weekly East Africa OpenStreetMap extracts.

Fare calculation combines four variables:

```php
$fare = (
    $distanceKm * $ratePerKm + $vehicleBaseFare +
    $weightSurcharge * ($cargoKg / 100) + $fuelAdjustment * $fuelIndex
) * $demandMultiplier;
```

The `demandMultiplier` (1.0–1.8) scales with real-time driver availability within 5 km of pickup. This model, derived from Chapter 9's vehicle fare structures, produces quotes within 3% of final charged amounts.

#### 12.2.3 GPS Tracking Dashboard

The dispatch dashboard pipelines GPS coordinates through three stages:

```
+-----------+     +--------------+     +------------------+     +------------------+
|  Driver   |     |  Location    |     |  Route Engine    |     |  Dispatch        |
|  Flutter  |---> |  Ingestion   |---> |  (Mapbox Snap    |---> |  Dashboard       |
|  App      |     |  API + PostGI|     |  + OSRM + ETA)   |     |  (Flutter Web)   |
+-----------+     +--------------+     +------------------+     +------------------+
  GPS 10s               |                       |
  interval              v                       v
                   +--------------+     +------------------+
                   |  Geofence    |     |  FCM Broadcast   |
                   |  (Turf.js)   |     |  (ETA updates)   |
                   +--------------+     +------------------+
                          |
                          v
                   +--------------+
                   |  SMS Fallback|
                   |  (Africa's   |
                   |   Talking)   |
                   +--------------+
```

The driver app posts `{lat, lon, timestamp, delivery_id}` every 10 seconds. PostGIS stores the trace as a `LINESTRING`. The route engine snaps GPS to road networks via Mapbox Map Matching (or OSRM), then computes ETA with real-time traffic. The dashboard subscribes to Reverb channel `delivery.{id}` for live coordinate streams. Geofence triggers fire Turf.js operations against the snapped polyline; deviations exceeding 500 m for 2 minutes auto-alert dispatchers.

| Stage | Channel | Latency | Fallback | Use Case |
|:---|:---|:---|:---|:---|
| WebSocket | Laravel Reverb | < 100 ms | — | Live driver dot on map |
| Push | FCM `delivery_{id}` | 1–3 s | — | ETA updates to customer |
| SMS | Africa's Talking | 5–15 s | None | Feature-phone notification |
| In-app | Local notification | < 50 ms | — | Geofence entry/exit alerts |

This **Real-Time Delivery Matrix** maps each channel to its operational context. The live dot serves dispatchers; FCM reaches smartphone-holding customers; SMS ensures even farmers with basic GSM devices receive "Your order has arrived" confirmations — the operational expression of the triple-redundant cascade from Section 12.1.1.

Delivery proof is a composite record: a confirmation photograph (captured in-app), GPS coordinates at capture time, and a server-signed timestamp. The three elements are hashed together and stored immutably in `delivery_confirmations`. Performance analytics — on-time rate, average speed per segment, driver idle time — are computed nightly by a Laravel queued job and surfaced as 7-day and 30-day rolling aggregates.
