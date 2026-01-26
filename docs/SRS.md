# Software Requirements Specification (SRS)

## Sistem Pendataan Infrastruktur ISP

**Versi:** 1.2
**Tanggal:** 23 Januari 2026
**Status:** Draft - Updated with Laravel Starter Kit + Inertia.js 2.0

---

## 1. Pendahuluan

### 1.1 Tujuan Dokumen

Dokumen ini menjelaskan spesifikasi kebutuhan perangkat lunak untuk Sistem Pendataan Infrastruktur ISP (Internet Service Provider). Dokumen ini ditujukan untuk developer, project manager, dan stakeholder untuk memahami ruang lingkup, fungsi, dan batasan sistem.

### 1.2 Ruang Lingkup Produk

Sistem Pendataan Infrastruktur ISP adalah aplikasi berbasis web yang dirancang untuk mengelola dan mendokumentasikan seluruh aset infrastruktur jaringan ISP, termasuk perangkat aktif, perangkat pasif, Point of Presence (POP), server, dan Customer Premises Equipment (CPE).

**Tujuan Utama:**

- Menyediakan platform terpusat untuk pendataan infrastruktur ISP
- Memfasilitasi manajemen multi-perusahaan dengan isolasi data
- Memberikan visualisasi geografis aset infrastruktur
- Mendukung dokumentasi visual dan teknis yang komprehensif
- Menyediakan kontrol akses berbasis role yang fleksibel

### 1.3 Definisi, Akronim, dan Singkatan

- **ISP**: Internet Service Provider
- **POP**: Point of Presence - lokasi fisik tempat peralatan ISP berada
- **OLT**: Optical Line Terminal
- **ODF**: Optical Distribution Frame
- **OTB**: Optical Termination Box
- **ODP**: Optical Distribution Point
- **ONT**: Optical Network Terminal
- **CPE**: Customer Premises Equipment
- **NOC**: Network Operations Center
- **JB**: Joint Box
- **SRS**: Software Requirements Specification
- **UI/UX**: User Interface/User Experience
- **SPA**: Single Page Application (via Inertia.js)
- **Monolith**: Arsitektur aplikasi di mana frontend dan backend disatukan dalam satu codebase

### 1.4 Referensi

- Laravel 12 Documentation (Starter Kit: Breeze/Jetstream)
- Inertia.js 2.0 Documentation
- Vue.js 3 Documentation (Composition API)
- Pinia State Management Documentation
- Tailwind CSS Documentation
- Shadcn-vue Component Library
- MySQL 8.0+ Documentation
- IEEE Std 830-1998 (SRS Template)

---

## 2. Deskripsi Umum

### 2.1 Perspektif Produk

Sistem ini merupakan aplikasi **Modern Monolith** yang dibangun menggunakan **Laravel 12** sebagai framework utama, dipadukan dengan **Inertia.js** untuk menghubungkan backend Laravel dengan frontend **Vue.js 3**. Pendekatan ini memungkinkan pengembangan SPA (Single Page Application) tanpa kerumitan memisahkan repositori API dan Frontend.

**Komponen Utama:**

- **Backend & Routing**: Laravel 12 (Web Routes & Controllers)
- **Frontend Layer**: Inertia.js + Vue.js 3 (Composition API)
- **Styling**: Tailwind CSS + Shadcn-vue
- **State Management**: Pinia (untuk client-side state yang kompleks)
- **Database**: MySQL 8.0+
- **Container orchestration**: Docker

**Arsitektur (Inertia.js Monolith):**

```
┌─────────────────────────────────────────┐
│         Browser (Client)                │
│  ┌────────────────────────────────┐    │
│  │  Vue.js 3 (Inertia App)        │    │
│  │  - Pages (Vue Components)      │    │
│  │  - Shared Components           │    │
│  │  - Shadcn-vue + Tailwind       │    │
│  │  - Inertia Link / Form Helper  │    │
│  └────────────────────────────────┘    │
└─────────────────────────────────────────┘
              ▲   │
   JSON Props │   │ XHR / Inertia Visit
   (via AJAX) │   ▼
┌─────────────────────────────────────────┐
│      Web Server (Laravel 12)            │
│  ┌────────────────────────────────┐    │
│  │  Routes (web.php)              │    │
│  │  Controllers                   │    │
│  │  Inertia Middleware            │    │
│  │  Services (Business Logic)     │    │
│  │  Eloquent Models               │    │
│  └────────────────────────────────┘    │
└─────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│        MySQL Database                   │
└─────────────────────────────────────────┘
```

### 2.2 Fungsi Produk

Sistem menyediakan fungsi-fungsi berikut:

1. **Manajemen Perusahaan**: Pendataan informasi perusahaan ISP
2. **Manajemen Wilayah**: Pendataan wilayah infrastruktur dengan hierarki geografis
3. **Manajemen POP**: Pendataan Point of Presence dengan detail lokasi dan kapasitas
4. **Manajemen Server**: Pendataan server dan perangkat di ruang server
5. **Manajemen Perangkat Pasif**: Pendataan ODF/OTB, tiang, ODP, kabel, joint box, splitter, slack, tower
6. **Manajemen Perangkat Aktif**: Pendataan router, switch, OLT, radio, ONT, access point
7. **Manajemen Splicing Point**: Pendataan titik sambungan fiber optik
8. **Manajemen CPE**: Pendataan perangkat di sisi pelanggan
9. **Manajemen User & Role**: Kontrol akses berbasis modul dan permission

### 2.3 Karakteristik Pengguna

| Role               | Deskripsi                      | Hak Akses                                              |
| ------------------ | ------------------------------ | ------------------------------------------------------ |
| **Superadmin**     | Administrator sistem tertinggi | Full access ke semua modul dan data seluruh perusahaan |
| **Company Admin**  | Administrator perusahaan       | Full access hanya untuk data perusahaannya sendiri     |
| **Staff** (Future) | Operator data entry            | Akses terbatas sesuai assignment modul                 |

### 2.4 Batasan

1. **Teknologi**:
   - Framework: Laravel 12 (dengan Starter Kit bundling Inertia)
   - Frontend Adapter: Inertia.js 2.0 (Vue 3)
   - UI Library: Shadcn-vue, Tailwind CSS
   - Database: MySQL 8.0+

2. **Operasional**:
   - Memerlukan koneksi internet untuk akses
   - Browser modern (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)
   - Upload foto maksimal 10MB per file

3. **Keamanan**:
   - Autentikasi menggunakan Laravel Session/Sanctum (built-in Starter Kit)
   - CSRF Protection otomatis oleh Laravel
   - Data isolasi per perusahaan untuk Company Admin

### 2.5 Asumsi dan Ketergantungan

**Asumsi:**

- Pengguna memiliki pemahaman dasar tentang infrastruktur jaringan ISP
- Server support PHP 8.3+ dan Node.js untuk build process

**Ketergantungan:**

- Ketersediaan server hosting
- Layanan geocoding untuk validasi koordinat
- Library pihak ketiga (Composer & NPM packages)

---

## 3. Kebutuhan Spesifik

### 3.1 Kebutuhan Fungsional

#### 3.1.1 Modul Manajemen Perusahaan

**FR-COMP-001**: Sistem harus dapat menambah data perusahaan baru
**FR-COMP-002**: Sistem harus dapat mengubah data perusahaan
**FR-COMP-003**: Sistem harus dapat menghapus data perusahaan (soft delete)
**FR-COMP-004**: Sistem harus dapat menampilkan daftar perusahaan dengan pagination
**FR-COMP-005**: Sistem harus dapat mencari perusahaan berdasarkan nama/kode

**Data Perusahaan:**

- ID Perusahaan [PK]
- Kode Perusahaan [Unique]
- Nama Perusahaan
- Alamat Lengkap
- Nomor Telepon
- Email
- Website
- Logo Perusahaan
- Status Aktif
- Tanggal Dibuat
- Tanggal Diubah

#### 3.1.2 Modul Manajemen Wilayah Infrastruktur

**FR-AREA-001**: Sistem harus dapat menambah wilayah infrastruktur baru
**FR-AREA-002**: Sistem harus dapat mengubah data wilayah
**FR-AREA-003**: Sistem harus dapat menghapus wilayah (dengan validasi relasi)
**FR-AREA-004**: Sistem harus dapat menampilkan hierarki wilayah per perusahaan
**FR-AREA-005**: Sistem harus dapat menampilkan dashboard wilayah dengan statistik aset
**FR-AREA-006**: Wilayah harus terhubung ke satu perusahaan

**Data Wilayah:**

- ID Wilayah [PK]
- ID Perusahaan [FK]
- Kode Wilayah [Unique per Company]
- Nama Wilayah
- Provinsi
- Kabupaten
- Kecamatan
- Desa
- Deskripsi
- Status Aktif
- Tanggal Dibuat

#### 3.1.3 Modul Manajemen POP (Point of Presence)

**FR-POP-001**: Sistem harus dapat menambah data POP baru
**FR-POP-002**: Sistem harus dapat mengubah data POP
**FR-POP-003**: Sistem harus dapat menghapus data POP (dengan validasi relasi)
**FR-POP-004**: Sistem harus dapat menampilkan daftar POP per wilayah
**FR-POP-005**: Sistem harus dapat menampilkan detail POP dengan foto
**FR-POP-006**: Sistem harus dapat menampilkan lokasi POP di peta
**FR-POP-007**: Sistem harus dapat upload dan menampilkan foto POP

**Data POP:**

- ID POP [PK]
- Kode POP [Unique per Company]
- ID Perusahaan [FK]
- ID Wilayah [FK]
- Nama POP
- Alamat Lengkap
- Provinsi
- Kabupaten
- Kecamatan
- Desa
- Longitude
- Latitude
- Foto POP (path/url)
- Kapasitas Listrik (VA/Watt)
- Sumber Listrik [Enum: PLN, Tenaga Surya, Genset, Hybrid]
- Backup Listrik [Boolean]
- Keterangan/Catatan [Text]
- Status Aktif
- Tanggal Dibuat
- Tanggal Diubah

#### 3.1.4 Modul Manajemen Server

**FR-SRV-001**: Sistem harus dapat menambah data server baru
**FR-SRV-002**: Sistem harus dapat mengubah data server
**FR-SRV-003**: Sistem harus dapat menghapus data server
**FR-SRV-004**: Sistem harus dapat upload multiple foto (ruangan, rak, instalasi, kabel)
**FR-SRV-005**: Sistem harus dapat menampilkan detail server dengan galeri foto
**FR-SRV-006**: Sistem harus dapat filter server berdasarkan fungsi

**Data Server:**

- ID Server [PK]
- ID Perusahaan [FK]
- ID Wilayah [FK]
- ID POP [FK, nullable]
- Kode Server [Unique per Company]
- Nama Server
- Fungsi [Enum: Server, OLT, Core Network, NOC]
- Lokasi Gedung
- Lokasi Lantai
- Lokasi Area
- Longitude
- Latitude
- Foto Ruangan (path/url)
- Foto Rak (path/url)
- Foto Instalasi Perangkat (path/url)
- Foto Jalur Kabel (path/url)
- Keterangan/Catatan
- Status Aktif
- Tanggal Dibuat
- Tanggal Diubah

#### 3.1.5 Modul Manajemen Perangkat Pasif

**FR-PPAS-001**: Sistem harus dapat menambah perangkat pasif berdasarkan tipe
**FR-PPAS-002**: Sistem harus dapat mengubah data perangkat pasif
**FR-PPAS-003**: Sistem harus dapat menghapus perangkat pasif
**FR-PPAS-004**: Sistem harus dapat menampilkan daftar perangkat pasif per tipe
**FR-PPAS-005**: Sistem harus dapat filter perangkat pasif berdasarkan wilayah
**FR-PPAS-006**: Sistem harus dapat mencatat lokasi geografis untuk setiap perangkat

**Tipe Perangkat Pasif:**

1. ODF/OTB
2. Tiang
3. ODP
4. Kabel Fiber Optic
5. Joint Box
6. Splitter
7. Slack
8. Tower

**Skema Data Umum (Polymorphic):**

- ID Perangkat Pasif [PK]
- ID Perusahaan [FK]
- ID Wilayah [FK]
- Tipe Perangkat [Enum]
- Kode Perangkat [Unique per Company per Type]
- Nama/Label Perangkat
- Longitude (nullable)
- Latitude (nullable)
- Spesifikasi [JSON - berbeda per tipe]
- Foto (path/url, nullable)
- Keterangan
- Status Aktif
- Tanggal Instalasi
- Tanggal Dibuat
- Tanggal Diubah

**Spesifikasi per Tipe:**

**ODF/OTB:**

- Jumlah Port
- Port Terpakai
- Kapasitas Core
- Merek
- Model

**Tiang:**

- Tinggi (meter)
- Material [Enum: Beton, Besi, Kayu]
- Kepemilikan [Enum: Sendiri, PLN, Sewa]
- Nomor Tiang (jika PLN)

**ODP:**

- Jumlah Port
- Port Terpakai
- Tipe Splitter (1:x)
- Merek
- Model

**Kabel Fiber Optic:**

- Panjang (meter)
- Jumlah Core
- Tipe [Enum: Single Mode, Multi Mode]
- Merek
- Titik Awal (reference)
- Titik Akhir (reference)

**Joint Box:**

- Kapasitas Core
- Jumlah Input
- Jumlah Output
- Merek
- Model

**Splitter:**

- Ratio (contoh: 1:8, 1:16, 1:32)
- Merek
- Model
- Loss (dB)

**Slack:**

- Panjang (meter)
- Jumlah Core
- Lokasi Reference

**Tower:**

- Tinggi (meter)
- Tipe [Enum: SST, Monopole, Guyed]
- Kepemilikan [Enum: Sendiri, Sewa]
- Kapasitas Antenna

#### 3.1.6 Modul Manajemen Perangkat Aktif

**FR-PACT-001**: Sistem harus dapat menambah perangkat aktif berdasarkan tipe
**FR-PACT-002**: Sistem harus dapat mengubah data perangkat aktif
**FR-PACT-003**: Sistem harus dapat menghapus perangkat aktif
**FR-PACT-004**: Sistem harus dapat menghubungkan perangkat aktif ke Server/POP/Tower/CPE
**FR-PACT-005**: Sistem harus dapat menampilkan topologi koneksi perangkat
**FR-PACT-006**: Sistem harus dapat tracking inventori perangkat (serial number, warranty)

**Tipe Perangkat Aktif:**

1. Router
2. Switch
3. OLT
4. Radio
5. ONT
6. Access Point

**Skema Data Umum:**

- ID Perangkat Aktif [PK]
- ID Perusahaan [FK]
- ID Wilayah [FK]
- Tipe Perangkat [Enum]
- Kode Perangkat [Unique per Company per Type]
- Nama/Label Perangkat
- Merek
- Model
- Serial Number [Unique]
- MAC Address (nullable)
- IP Address (nullable)
- Lokasi Tipe [Enum: Server, POP, Tower, CPE]
- Lokasi ID [FK polymorphic]
- Spesifikasi [JSON - berbeda per tipe]
- Foto (path/url, nullable)
- Tanggal Pembelian
- Garansi Hingga
- Status Operasional [Enum: Aktif, Standby, Rusak, Maintenance]
- Keterangan
- Tanggal Dibuat
- Tanggal Diubah

**Spesifikasi per Tipe:**

**Router:**

- Jumlah Port Ethernet
- Jumlah Port SFP
- Throughput (Gbps)
- Routing Protocol Support

**Switch:**

- Jumlah Port
- Tipe Port [Enum: FastEthernet, GigabitEthernet, 10G]
- Manageable [Boolean]
- VLAN Support [Boolean]
- PoE Support [Boolean]

**OLT:**

- Jumlah PON Port
- Kapasitas ONT per Port
- Teknologi [Enum: GPON, EPON, XGS-PON]
- Jumlah Uplink Port

**Radio:**

- Frekuensi (GHz)
- Bandwidth (MHz)
- Jarak Maksimal (km)
- Throughput (Mbps)
- Tipe [Enum: PTP, PTMP, Mesh]

**ONT:**

- Jumlah Port LAN
- Port POTS (voice)
- WiFi Support [Boolean]
- WiFi Standard (nullable)

**Access Point:**

- WiFi Standard (802.11ac/ax/be)
- Frekuensi Support (2.4GHz/5GHz/6GHz)
- Jumlah Antenna
- Coverage Area (m²)
- PoE Powered [Boolean]

#### 3.1.7 Modul Manajemen Splicing Point

**FR-SPLIC-001**: Sistem harus dapat menambah splicing point baru
**FR-SPLIC-002**: Sistem harus dapat mengubah data splicing point
**FR-SPLIC-003**: Sistem harus dapat menghapus splicing point
**FR-SPLIC-004**: Sistem harus dapat upload foto sambungan
**FR-SPLIC-005**: Sistem harus dapat menampilkan splicing point di peta
**FR-SPLIC-006**: Sistem harus dapat menghubungkan splicing point ke Joint Box

**Data Splicing Point:**

- ID Splicing [PK]
- ID Perusahaan [FK]
- ID Wilayah [FK]
- ID Joint Box [FK, nullable]
- Kode Splicing Point [Unique per Company]
- Longitude
- Latitude
- Foto Sambungan (path/url)
- Jumlah Core Disambung
- Metode Splicing [Enum: Fusion, Mechanical]
- Loss (dB, nullable)
- Keterangan
- Tanggal Splicing
- Teknisi
- Status
- Tanggal Dibuat
- Tanggal Diubah

#### 3.1.8 Modul Manajemen CPE (Customer Premises Equipment)

**FR-CPE-001**: Sistem harus dapat menambah data CPE baru
**FR-CPE-002**: Sistem harus dapat mengubah data CPE
**FR-CPE-003**: Sistem harus dapat menghapus data CPE
**FR-CPE-004**: Sistem harus dapat menghubungkan CPE ke perangkat aktif (ONT/Radio)
**FR-CPE-005**: Sistem harus dapat mencatat lokasi pelanggan
**FR-CPE-006**: Sistem harus dapat filter CPE berdasarkan status

**Data CPE:**

- ID CPE [PK]
- ID Perusahaan [FK]
- ID Wilayah [FK]
- ID Perangkat Aktif [FK, nullable] (ONT/Radio/dll yang connect ke customer)
- Kode CPE [Unique per Company]
- Nama Pelanggan/Lokasi
- Alamat Lengkap
- Longitude (nullable)
- Latitude (nullable)
- Tipe CPE [Enum: ONT, Router, Radio CPE, Modem]
- Merek
- Model
- Serial Number
- MAC Address (nullable)
- Status [Enum: Aktif, Tidak Aktif, Rusak]
- Tanggal Instalasi
- Foto Instalasi (path/url, nullable)
- Keterangan
- Tanggal Dibuat
- Tanggal Diubah

#### 3.1.9 Modul Manajemen User & Role

**FR-USER-001**: Sistem harus dapat menambah user baru
**FR-USER-002**: Sistem harus dapat mengubah data user
**FR-USER-003**: Sistem harus dapat menonaktifkan user (soft delete)
**FR-USER-004**: Sistem harus dapat assign role ke user
**FR-USER-005**: Sistem harus dapat setup permission per modul untuk role
**FR-USER-006**: Superadmin dapat akses semua data perusahaan
**FR-USER-007**: Company Admin hanya dapat akses data perusahaannya
**FR-USER-008**: Sistem harus dapat audit log aktivitas user

**Data User:**

- ID User [PK]
- Nama Lengkap
- Email [Unique]
- Password (hashed)
- ID Perusahaan [FK, nullable] (null untuk superadmin)
- Role [Enum: superadmin, company_admin]
- Avatar (path/url, nullable)
- Status Aktif
- Last Login
- Tanggal Dibuat
- Tanggal Diubah

**Data Role & Permission:**

- ID Role [PK]
- Nama Role
- Deskripsi
- Permissions [JSON] - array of module permissions

**Modul Permissions:**

- companies: create, read, update, delete
- areas: create, read, update, delete
- pops: create, read, update, delete
- servers: create, read, update, delete
- passive_devices: create, read, update, delete
- active_devices: create, read, update, delete
- splicing_points: create, read, update, delete
- cpes: create, read, update, delete
- users: create, read, update, delete

### 3.2 Kebutuhan Non-Fungsional

#### 3.2.1 Kebutuhan Performa

**NFR-PERF-001**: Initial page load harus cepat (server-side rendering capable jika diperlukan, namun default client-side hydration).
**NFR-PERF-002**: Navigasi antar halaman terasa instan (SPA feel) berkat Inertia Link prefetching.
**NFR-PERF-003**: Asset statis di-bundle dan di-minify menggunakan Vite.
**NFR-PERF-004**: Upload foto harus dapat menangani hingga 5 file simultan.
**NFR-PERF-005**: Sistem harus dapat menangani minimal 100 concurrent users.

#### 3.2.2 Kebutuhan Keamanan

**NFR-SEC-001**: Menggunakan mekanisme auth bawaan Laravel (Session-based) yang lebih aman dari XSS dibanding penyimpanan token di LocalStorage.
**NFR-SEC-002**: Proteksi CSRF otomatis pada setiap request non-GET.
**NFR-SEC-003**: Validasi input di sisi server (Laravel Request) dan client (Vue).
**NFR-SEC-004**: File upload harus divalidasi (tipe, ukuran, content).
**NFR-SEC-005**: SQL injection harus dicegah dengan Eloquent ORM.

#### 3.2.3 Kebutuhan Usability

**NFR-USA-001**: Interface modern dan responsif menggunakan Tailwind CSS.
**NFR-USA-002**: Feedback visual instan saat form submission (Inertia Form Helper processing state).
**NFR-USA-003**: Loading state harus ditampilkan untuk operasi async (Inertia progress indicators).

### 3.3 Kebutuhan Interface

#### 3.3.1 User Interface (Inertia + Vue 3)

**Design System:**

- **Framework**: Vue.js 3 (Composition API) via Inertia
- **Styling**: Tailwind CSS + Shadcn-vue
- **Routing**: Laravel Routes (`web.php`) + Ziggy (untuk generate URL di JS)
- **Icons**: Lucide Vue

**Project Structure (Standard Laravel + Inertia):**

```
root/
├── app/                 # Backend Logic (Models, Controllers, etc)
├── resources/
│   ├── css/
│   │   └── app.css      # Tailwind imports
│   ├── js/
│   │   ├── Components/  # Shared Vue Components (Shadcn-vue here)
│   │   │   ├── ui/      # Shadcn components (Button, Input, etc.)
│   │   │   └── ...
│   │   ├── Layouts/     # AppLayout, GuestLayout
│   │   ├── Pages/       # Inertia Pages (sesuai struktur Controller)
│   │   │   ├── Auth/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Company/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   └── Edit.vue
│   │   │   └── ...
│   │   ├── app.ts       # Entry point
│   │   └── types/       # TS Definitions
│   └── views/
│       └── app.blade.php # Root template
├── routes/
│   └── web.php          # Application Routes
├── vite.config.ts       # Vite Configuration
└── tailwind.config.js   # Tailwind Configuration
```

**Key Implementation Details:**

1. **Pages vs Components**:
   - `Pages/`: Komponen yang merepresentasikan satu halaman penuh (dikembalikan oleh Controller).
   - `Components/`: Reusable UI elements (Button, Card, Modal).

2. **Data Passing**:
   - Data dikirim dari Controller sebagai `props` ke Page Vue.
   - Tidak perlu `onMounted` fetch API manual untuk data awal halaman.

3. **Form Handling**:
   - Menggunakan `useForm` dari `@inertiajs/vue3`.
   - Contoh:

     ```typescript
     const form = useForm({
       name: "",
       email: "",
     });

     const submit = () => {
       form.post(route("companies.store"));
     };
     ```

#### 3.3.2 Application Interface (Routes)

Sistem tidak lagi memisahkan REST API publik secara ketat (kecuali diperlukan untuk mobile app di masa depan), melainkan menggunakan **Web Routes** yang mengembalikan respons Inertia.

**Routing Convention (`routes/web.php`):**

```php
use App\Http\Controllers\CompanyController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resource Controller untuk Company
    Route::resource('companies', CompanyController::class);

    // Resource Controller untuk modul lain
    Route::resource('pops', PopController::class);
    // dst...
});
```

**Controller Response:**

```php
public function index()
{
    return Inertia::render('Company/Index', [
        'companies' => Company::paginate(10),
        'filters' => Request::all('search', 'trashed'),
    ]);
}
```

### 3.4 Kebutuhan Data

#### 3.4.1 Database Schema (High Level)

**Core Tables:**

1. `users` - Data pengguna sistem
2. `companies` - Data perusahaan ISP
3. `infrastructure_areas` - Wilayah infrastruktur
4. `pops` - Point of Presence
5. `servers` - Data server
6. `passive_devices` - Perangkat pasif (polymorphic)
7. `active_devices` - Perangkat aktif
8. `splicing_points` - Titik sambungan fiber
9. `cpes` - Customer premises equipment
10. `roles` - Role user
11. `permissions` - Permission modul
12. `role_permissions` - Mapping role-permission
13. `user_roles` - Mapping user-role
14. `activity_logs` - Audit trail
15. `media_files` - File storage reference

**Lookup Tables:**

1. `provinces` - Data provinsi
2. `regencies` - Data kabupaten/kota
3. `districts` - Data kecamatan
4. `villages` - Data desa/kelurahan

**Relationship Tables:**

1. `device_connections` - Koneksi antar perangkat aktif

#### 3.4.2 Data Migration Strategy

- Gunakan Laravel migrations untuk version control database
- Seeder untuk data master
- Factory untuk testing data
- Soft deletes untuk data penting

---

## 4. Arsitektur Sistem

### 4.1 Arsitektur Aplikasi

**Pattern**: Modular Monolith dengan Inertia.js Adapter.

**Backend Structure (Laravel Modules - Optional but Recommended for large apps):**
Jika menggunakan `nwidart/laravel-modules` dengan Inertia:

```
Modules/
├── Company/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Resources/assets/js/Pages/ # View khusus modul ini
```

_Atau menggunakan struktur default Laravel jika skala belum terlalu besar._

### 4.2 Technology Stack

**Backend:**

- PHP 8.3+
- Laravel 12 (Starter Kit)
- Ziggy (Routing for JS)
- Intervention Image (Image processing)

**Frontend:**

- Vue.js 3 (Composition API, Script Setup)
- Inertia.js 2.0 (Client Adapter)
- Tailwind CSS 3.x/4.x
- Shadcn-vue (Radix Vue based)
- Lucide Vue (Icons)
- Leaflet/Mapbox (Maps)

**DevOps:**

- Docker (Development via Sail / Production via Docker Compose)
- Vite (Fast Build Tool)

---

## 5. Workflow & Use Cases

### 5.1 User Workflow (Inertia Approach)

**Workflow: Menambah Data Wilayah**

1. User klik link "Add Area" (`<Link href="/areas/create">`).
2. Inertia melakukan request XHR ke `/areas/create`.
3. Laravel Controller mengembalikan `Inertia::render('Area/Create')`.
4. Halaman form muncul tanpa full page reload.
5. User isi form dan klik Submit.
6. `form.post('/areas')` dieksekusi.
7. Laravel validasi request.
   - Jika error: Laravel redirect back with errors (Inertia otomatis tangkap dan tampilkan di form).
   - Jika sukses: Laravel simpan data dan redirect ke Index with flash message.
8. Inertia menangani redirect dan update tampilan list area.

---

## 6. Acceptance Criteria

### 6.1 Functional

- **AC-FUNC-001**: Login/Register berfungsi menggunakan auth starter kit.
- **AC-FUNC-002**: CRUD operasi berjalan mulus tanpa full reload (SPA feel).
- **AC-FUNC-003**: Validasi server-side muncul di form frontend secara real-time (setelah submit).
- **AC-FUNC-004**: Flash message (Success/Error) muncul menggunakan Toast component (Shadcn).

### 6.2 Performance

- **AC-PERF-001**: Navigasi halaman < 200ms.
- **AC-PERF-002**: Bundle JS teroptimasi (Code Splitting otomatis oleh Vite per halaman Inertia).

---

## 7. Implementation Plan

### 7.1 Development Phases

**Phase 1: Setup & Foundation**

- Install Laravel 12 + Starter Kit (Breeze/Jetstream) + Vue + Inertia.
- Config Tailwind & Shadcn-vue.
- Setup Database & Models dasar.

**Phase 2: Core Modules**

- Implementasi CRUD Company & User Management.
- Setup Layout Dashboard.

**Phase 3: Infrastructure Modules**

- Implementasi modul Wilayah, POP, Server.
- Integrasi Maps.

**Phase 4: Device Management**

- Implementasi modul Perangkat Pasif & Aktif.
- Logic topologi/relasi antar perangkat.

**Phase 5: Refinement**

- Testing, Bugfixing, Deployment.

---

## 8. Appendix

### 8.1 Glossary

- **Inertia.js**: Framework untuk membangun SPA klasik menggunakan routing dan controller server-side (Modern Monolith).
- **Ziggy**: Library untuk menggunakan route helper Laravel (`route('name')`) di dalam JavaScript.

### 8.2 Design Guidelines (Vue + Inertia)

- Gunakan `<Link>` component pengganti `<a>` tag untuk navigasi internal.
- Gunakan `useForm` untuk handling form submission, error handling, dan processing state.
- Manfaatkan `Layouts` persisten agar sidebar/header tidak re-render saat navigasi.
