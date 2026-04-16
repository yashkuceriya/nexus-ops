# ADR-003: Sensor Data Architecture -- MVP Polling to Production TSDB

## Status: Accepted
## Date: 2026-04-15

## Context

NexusOps ingests IoT sensor data (temperature, humidity, power, vibration, air quality)
from building management systems. Sensor readings drive dashboard visualizations, anomaly
detection, and automated work order creation when thresholds are breached. We needed to
decide on the storage and ingestion architecture for sensor time-series data.

## Current Approach (MVP)

Sensor data is stored in a `sensor_readings` table in the same SQLite (dev) / MySQL
(staging) database used by the rest of the application. The schema is:

- `sensor_source_id` (FK to `sensor_sources`)
- `value` (numeric reading)
- `recorded_at` (timestamp of the reading)
- `is_anomaly` (boolean flag set during ingest)

Ingestion happens via the `POST /api/sensors/ingest` endpoint, which accepts batches
of up to 1000 readings per request. The `SensorIngestService` validates each reading
against the sensor's configured thresholds and flags anomalies. External systems poll
this endpoint on a schedule (typically every 15 minutes for demo environments).

### MVP Scale Numbers
- 6 sensor sources in the demo seed
- 96 readings per sensor per day (one every 15 minutes)
- ~576 rows/day total
- At this scale, SQLite handles queries with sub-millisecond response times
- Index on `(sensor_source_id, recorded_at)` is sufficient

## Why This Works for MVP

- **Zero infrastructure overhead**: No additional services to deploy or maintain.
- **Familiar tooling**: Standard Eloquent queries, standard migrations, standard backups.
- **Sufficient for demo and pilot**: With <1000 rows/day, even unoptimized queries
  return in milliseconds. The `readings` endpoint with date range filtering performs
  well with the composite index.
- **Anomaly detection is synchronous**: At low volume, checking thresholds inline during
  ingest adds negligible latency (<5ms per batch).

## Production Scaling Challenge

Production deployments with real BMS integrations will operate at fundamentally
different scale:

- **1000 sensors x 1 reading/15 seconds = 5,760,000 rows/day (5.7M)**
- **30-day retention at this rate = 172M rows**
- **90-day retention = 518M rows**

At this volume, a standard relational table fails:
1. INSERT throughput becomes a bottleneck (write amplification from indexes).
2. Time-range queries over hundreds of millions of rows are slow even with indexes.
3. Storage grows linearly with no built-in downsampling or compression.
4. Backup and restore times become operationally painful.

## Production Architecture (Planned)

### Storage: TimescaleDB (PostgreSQL extension)
- **Hypertables** with automatic time-based partitioning (chunk interval: 1 day).
- **Native compression**: 10-20x compression ratio on time-series data, reducing 518M
  rows of storage from ~50GB to ~3-5GB.
- **Continuous aggregates**: Materialized views that pre-compute 5m, 1h, and 1d rollups
  for dashboard queries, eliminating expensive real-time aggregation.
- **Retention policies**: Automatically drop raw data older than 90 days while keeping
  aggregates for 2 years.

### Ingestion: MQTT Broker + Queue Worker
- Replace HTTP polling with MQTT subscriptions (standard BMS protocol).
- Laravel queue workers consume from an MQTT topic, batch-insert readings using
  TimescaleDB's `INSERT ... ON CONFLICT` with chunk-level optimizations.
- Anomaly detection moves to an async job that processes micro-batches every 5 seconds.

### Downsampling Retention Policy
| Granularity | Retention | Use Case                          |
|-------------|-----------|-----------------------------------|
| Raw (15s)   | 90 days   | Incident investigation, debugging |
| 5-minute    | 1 year    | Trend analysis, dashboards        |
| 1-hour      | 3 years   | Capacity planning, reporting      |
| 1-day       | Unlimited | Historical KPIs, compliance       |

## Migration Path

1. Deploy TimescaleDB as a separate connection in `config/database.php` (`timescale`).
2. Create hypertable migration for `sensor_readings` on the TimescaleDB connection.
3. Update `SensorIngestService` to write to the timescale connection.
4. Backfill existing readings from the relational table.
5. Add continuous aggregates and retention policies.
6. Update dashboard queries to read from continuous aggregates.

The `SensorController` API contract remains identical -- only the storage backend
changes. This is why the service layer abstraction exists.

## Alternatives Considered

- **InfluxDB**: Purpose-built TSDB with excellent write throughput, but introduces a
  non-SQL query language (Flux/InfluxQL) and a separate operational dependency. Team
  expertise is in SQL/PostgreSQL.
- **Clickhouse**: Excellent for analytics but overkill for our read patterns (mostly
  single-sensor time-range queries, not cross-sensor aggregations).
