# NMS (Network Management System) Implementation Plan

Rencana pengembangan OpenAccess menjadi sistem NMS yang dapat menangani ribuan perangkat aktif dengan fitur monitoring real-time, alerting, dan traffic analytics.

## User Review Required

> [!IMPORTANT]
> **Keputusan Arsitektur yang Perlu Persetujuan:**
> 1. Integrasi dengan **LibreNMS** sebagai poller engine (vs build from scratch)
> 2. Penggunaan **InfluxDB** untuk time-series data traffic
> 3. Penggunaan **Redis + Laravel Echo** untuk real-time updates

> [!WARNING]
> **Breaking Changes:**
> - Memerlukan tambahan infrastruktur (InfluxDB, Redis)
> - Perlu migration untuk menambah kolom monitoring pada tabel device
> - Memerlukan background queue workers yang selalu aktif

---

## Arsitektur Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         OpenAccess UI                               │
│                  (Laravel + Inertia + Vue)                          │
│         Real-time Dashboard, Alerts, Traffic Charts                 │
└─────────────────────────┬───────────────────────────────────────────┘
                          │ WebSocket (Laravel Echo + Pusher/Soketi)
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     Laravel Backend                                 │
│              API Controllers + Event Broadcasting                   │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
        ┌─────────────────┼─────────────────┐
        ▼                 ▼                 ▼
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│    MySQL      │  │   InfluxDB    │  │    Redis      │
│   (Config)    │  │   (Traffic)   │  │   (Cache)     │
└───────────────┘  └───────────────┘  └───────────────┘
        ▲                 ▲
        │                 │
┌───────────────────────────────────────────────────────────────────┐
│                      Queue Workers (Horizon)                       │
│         Polling Jobs, Alert Processing, Data Collection           │
└───────────────────────────────────────────────────────────────────┘
        ▲
        │ SNMP/ICMP/API
        ▼
┌───────────────────────────────────────────────────────────────────┐
│                     Network Devices                                │
│              Routers, Switches, OLTs, APs, etc.                   │
└───────────────────────────────────────────────────────────────────┘
```

---

## Phase 1: Core Infrastructure

### Modul Baru: `Modules/Nms`

Membuat modul baru untuk menampung semua fitur NMS.

#### [NEW] Module Structure
```
Modules/Nms/
├── app/
│   ├── Console/Commands/
│   │   └── PollDevices.php           # Artisan command untuk polling
│   ├── Http/Controllers/
│   │   ├── MonitoringController.php  # Dashboard monitoring
│   │   ├── AlertController.php       # Manage alerts
│   │   └── TrafficController.php     # Traffic analytics
│   ├── Jobs/
│   │   ├── PingDevice.php            # ICMP ping job
│   │   ├── SnmpPollDevice.php        # SNMP polling job
│   │   └── ProcessAlert.php          # Alert processing
│   ├── Models/
│   │   ├── DeviceStatus.php          # Status history
│   │   ├── Alert.php                 # Alert definitions
│   │   ├── AlertHistory.php          # Alert log
│   │   └── TrafficData.php           # Traffic metrics (reference)
│   ├── Events/
│   │   ├── DeviceStatusChanged.php   # Real-time status event
│   │   └── AlertTriggered.php        # Alert event
│   └── Services/
│       ├── SnmpService.php           # SNMP operations
│       ├── PingService.php           # ICMP ping
│       └── AlertService.php          # Alert logic
├── config/
│   └── config.php                    # NMS configuration
├── database/migrations/
├── resources/assets/js/Pages/
│   ├── Monitoring/
│   │   └── Dashboard.vue             # Real-time dashboard
│   ├── Alert/
│   │   ├── Index.vue                 # Alert list
│   │   └── Settings.vue              # Alert settings
│   └── Traffic/
│       └── Analytics.vue             # Traffic analytics
└── routes/
    └── web.php
```

---

### Database Migrations

#### [NEW] device_statuses table
```php
Schema::create('device_statuses', function (Blueprint $table) {
    $table->id();
    $table->morphs('monitorable');              // Router, Switch, OLT, etc.
    $table->enum('status', ['up', 'down', 'warning', 'unknown'])->default('unknown');
    $table->integer('latency_ms')->nullable();  // Ping latency
    $table->float('cpu_usage')->nullable();     // CPU percentage
    $table->float('memory_usage')->nullable();  // Memory percentage
    $table->integer('uptime_seconds')->nullable();
    $table->timestamp('last_check_at')->nullable();
    $table->timestamp('last_up_at')->nullable();
    $table->timestamp('last_down_at')->nullable();
    $table->json('extra_data')->nullable();     // Additional SNMP data
    $table->timestamps();
    
    $table->index(['monitorable_type', 'monitorable_id']);
    $table->index('status');
});
```

#### [NEW] alerts table
```php
Schema::create('alerts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('type');                      // device_down, high_cpu, high_memory, etc.
    $table->json('conditions');                  // {"threshold": 80, "duration_minutes": 5}
    $table->json('notification_channels');       // ["email", "telegram", "sms"]
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### [NEW] alert_histories table
```php
Schema::create('alert_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('alert_id')->constrained();
    $table->morphs('alertable');                 // Device yang trigger alert
    $table->enum('severity', ['info', 'warning', 'critical']);
    $table->string('message');
    $table->timestamp('triggered_at');
    $table->timestamp('acknowledged_at')->nullable();
    $table->timestamp('resolved_at')->nullable();
    $table->foreignId('acknowledged_by')->nullable();
    $table->timestamps();
});
```

---

### Packages yang Diperlukan

#### [MODIFY] [composer.json](file:///home/homenet/dev/oa-inertia/openaccess/composer.json)
```php
"require": {
    // ... existing
    "freedsx/snmp": "^0.7",           // SNMP library
    "influxdata/influxdb-client-php": "^3.4", // InfluxDB client
    "pusher/pusher-php-server": "^7.2" // atau soketi untuk WebSocket
}
```

#### [MODIFY] [package.json](file:///home/homenet/dev/oa-inertia/openaccess/package.json)
```json
"dependencies": {
    // ... existing
    "laravel-echo": "^1.15",
    "pusher-js": "^8.3",
    "chart.js": "^4.4",
    "vue-chartjs": "^5.3"
}
```

---

## Phase 2: Real-time Device Monitoring

### Polling System

#### [NEW] PingService.php
```php
class PingService
{
    public function ping(string $ip, int $timeout = 5): array
    {
        // Use exec() for ICMP ping
        // Returns: ['success' => bool, 'latency_ms' => int]
    }
}
```

#### [NEW] SnmpService.php
```php
class SnmpService
{
    public function getUptime(string $ip, string $community = 'public'): ?int;
    public function getCpuUsage(string $ip, string $community = 'public'): ?float;
    public function getMemoryUsage(string $ip, string $community = 'public'): ?float;
    public function getInterfaceTraffic(string $ip, string $community = 'public'): array;
}
```

#### [NEW] Scheduled Polling (Console Kernel)
```php
// routes/console.php
Schedule::job(new PollAllDevices)->everyMinute();  // Status check
Schedule::job(new CollectTrafficData)->everyFiveMinutes();  // Traffic data
```

### WebSocket Events

#### [NEW] DeviceStatusChanged Event
```php
class DeviceStatusChanged implements ShouldBroadcast
{
    public function __construct(
        public string $deviceType,
        public int $deviceId,
        public string $status,
        public ?int $latencyMs
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('device-monitoring');
    }
}
```

---

## Phase 3: Alerting System

### Alert Types
| Type | Condition | Severity |
|------|-----------|----------|
| `device_down` | No ping response | Critical |
| `high_latency` | Latency > threshold | Warning |
| `high_cpu` | CPU > 80% | Warning |
| `high_memory` | Memory > 80% | Warning |
| `interface_down` | Port status change | Warning |

### Notification Channels
- **Email** - Laravel Mail (sudah ada)
- **Telegram** - Bot API integration
- **SMS** - via SMS Gateway
- **Web Push** - Browser notifications
- **In-App** - Database notifications

---

## Phase 4: Traffic History & Analytics

### InfluxDB Integration

#### [NEW] config/influxdb.php
```php
return [
    'url' => env('INFLUXDB_URL', 'http://localhost:8086'),
    'token' => env('INFLUXDB_TOKEN'),
    'org' => env('INFLUXDB_ORG', 'openaccess'),
    'bucket' => env('INFLUXDB_BUCKET', 'network_traffic'),
];
```

### Data Points
- **interface_traffic** - bytes in/out per interface
- **device_metrics** - CPU, memory, temperature
- **latency_history** - ping response times

### Retention Policy
| Duration | Resolution |
|----------|------------|
| 24 hours | 1 minute |
| 7 days | 5 minutes |
| 30 days | 1 hour |
| 1 year | 1 day |

---

## Phase 5: Dashboard & UI

### Real-time Monitoring Dashboard

#### [NEW] Monitoring/Dashboard.vue
```vue
<!-- Features -->
- Device grid dengan status cards (up/down/warning)
- Real-time status updates via WebSocket
- Quick stats: Total UP, DOWN, Warning
- Filter by type, area, status
- Map view dengan device markers colored by status
```

### Traffic Analytics Page

#### [NEW] Traffic/Analytics.vue
```vue
<!-- Features -->
- Device selector dropdown
- Time range picker (1h, 6h, 24h, 7d, 30d)
- Traffic chart (bytes in/out over time)
- Top talkers table
- Export to CSV
```

### Alert Management

#### [NEW] Alert/Index.vue
```vue
<!-- Features -->
- Active alerts table dengan severity indicators
- Alert acknowledgment button
- Alert history with filtering
- Sound/visual notification for new alerts
```

---

## Verification Plan

### Automated Tests

#### Unit Tests
```bash
php artisan test --filter=PingServiceTest
php artisan test --filter=SnmpServiceTest
php artisan test --filter=AlertServiceTest
```

#### Feature Tests
```bash
php artisan test --filter=DeviceMonitoringTest
php artisan test --filter=AlertNotificationTest
```

### Manual Verification

1. **Device Polling Test**
   - Add a test device with valid IP
   - Wait for scheduled job to run
   - Verify status appears in dashboard

2. **Alert Test**
   - Create alert rule for "device_down"
   - Disconnect a test device
   - Verify notification is sent

3. **Traffic Chart Test**
   - Select device with traffic data
   - Verify chart displays correctly
   - Test time range changes

4. **WebSocket Test**
   - Open dashboard in browser
   - Change device status via tinker
   - Verify UI updates without refresh

---

## Infrastructure Requirements

### Docker Services (docker-compose.yml additions)

```yaml
services:
  # ... existing services
  
  influxdb:
    image: influxdb:2.7
    ports:
      - "8086:8086"
    volumes:
      - influxdb_data:/var/lib/influxdb2
    environment:
      - DOCKER_INFLUXDB_INIT_MODE=setup
      - DOCKER_INFLUXDB_INIT_USERNAME=admin
      - DOCKER_INFLUXDB_INIT_PASSWORD=password
      - DOCKER_INFLUXDB_INIT_ORG=openaccess
      - DOCKER_INFLUXDB_INIT_BUCKET=network_traffic

  soketi:
    image: quay.io/soketi/soketi:1.6-16-alpine
    ports:
      - "6001:6001"
      - "9601:9601"
    environment:
      - SOKETI_DEFAULT_APP_ID=openaccess
      - SOKETI_DEFAULT_APP_KEY=app-key
      - SOKETI_DEFAULT_APP_SECRET=app-secret

volumes:
  influxdb_data:
```

### Environment Variables (.env)

```env
# InfluxDB
INFLUXDB_URL=http://localhost:8086
INFLUXDB_TOKEN=your-token
INFLUXDB_ORG=openaccess
INFLUXDB_BUCKET=network_traffic

# WebSocket (Soketi)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=openaccess
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret
PUSHER_HOST=localhost
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

---

## Implementation Timeline (Estimasi)

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| Phase 1: Core Infrastructure | 2-3 hari | - |
| Phase 2: Real-time Monitoring | 3-4 hari | Phase 1 |
| Phase 3: Alerting System | 2-3 hari | Phase 2 |
| Phase 4: Traffic Analytics | 3-4 hari | Phase 1 |
| Phase 5: Dashboard & UI | 4-5 hari | Phase 2, 3, 4 |

**Total Estimasi: 14-19 hari kerja**

---

## Alternative: LibreNMS Integration

Jika ingin lebih cepat, bisa mengintegrasikan dengan LibreNMS yang sudah mature:

### Pros
- Sudah handle SNMP polling untuk ribuan device
- Auto-discovery devices
- Built-in alerting & graphing
- Community besar & documentation lengkap

### Cons
- Separate system (perlu maintenance)
- UI terpisah dari OpenAccess
- Perlu sinkronisasi data device

### Integration Approach
```php
// Consume LibreNMS API
class LibreNmsService
{
    public function getDeviceStatus(string $ip): array;
    public function getDeviceGraphs(int $deviceId): array;
    public function getAlerts(): array;
}
```

---

## Pertanyaan untuk User

1. **Mau build from scratch atau integrasi LibreNMS?**
   - From scratch = kontrol penuh, tapi waktu lebih lama
   - LibreNMS = lebih cepat, fitur lengkap, tapi UI terpisah

2. **Notification channels apa yang diperlukan?**
   - Email ✓
   - Telegram?
   - SMS?
   - WhatsApp?

3. **Berapa banyak device yang akan dimonitor dalam tahap awal?**
   - < 100 device → single worker cukup
   - 100-1000 device → multiple workers
   - > 1000 device → distributed architecture

4. **Apakah sudah ada SNMP community string yang digunakan?**
   - Default: `public` (read-only)
   - Custom per device?
