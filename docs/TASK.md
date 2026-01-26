# Checklist Task Implementasi Sistem Pendataan Infrastruktur ISP (Inertia.js Monolith)

Berikut checklist task implementasi yang disusun secara bertahap dan terstruktur, disesuaikan dengan arsitektur **Laravel Modern Monolith** (Laravel 12 + Inertia.js + Vue 3).

---

## 🚀 Phase 1: Foundation & Setup (Week 1-2)

### 1.1 Environment Setup

- [x] Install Docker & Docker Compose
- [x] Setup Docker containers (Nginx, PHP-FPM, MySQL, Redis, MailHog)
- [x] Konfigurasi docker-compose.yml
- [x] Setup Nginx configuration
- [x] Test Docker environment berjalan dengan baik

### 1.2 Backend & Framework Setup (Laravel + Inertia)

- [x] Install Laravel 12 dengan PHP 8.3+
- [x] **Install Laravel Starter Kit (Breeze/Jetstream)** dengan stack: **Vue + Inertia**
  - _Note: Ini akan otomatis setup Inertia, Vue, Tailwind, dan Ziggy_
- [x] Setup environment variables (.env)
- [x] Konfigurasi database connection
- [x] Install Laravel Modules (nWidart) - _Optional, sesuaikan dengan preferensi struktur folder_
- [x] Install dependencies tambahan: Intervention Image, Telescope, Horizon
- [x] Setup logging configuration
- [x] Setup queue configuration dengan Redis
- [x] Test koneksi database dan Redis

### 1.3 Frontend Setup (Vue.js + Inertia)

- [x] Verify Vue.js 3 + Vite setup (dari Starter Kit)
- [x] Setup TypeScript configuration
- [x] Install Pinia untuk client-side state management (global UI state)
- [x] Setup **Shadcn-vue** component library
  - [ ] Install dependencies (radix-vue, clsx, tailwind-merge)
  - [x] Setup utils.ts
  - [ ] Add Base Components (Button, Input, Card, Table, Dropdown, Toast, etc.)
- [x] Install dan konfigurasi TailwindCSS (verify config dari Starter Kit)
- [x] Setup **Lucide Vue** untuk icons
- [ ] Setup project structure (Pages, Components, Layouts, Composables)
- [ ] Konfigurasi Vite untuk auto-import components (optional)
- [ ] Test hot module replacement (HMR)

### 1.4 Database Design

- [x] Design database schema (ER Diagram)
- [x] Buat migration untuk lookup tables (provinces, regencies, districts, villages)
- [x] Buat seeder untuk lookup data
- [x] Buat migration untuk core tables (users, companies, roles, permissions)
- [x] Setup foreign keys dan indexes
- [x] Test migrations berjalan dengan baik
- [x] Seed initial data (superadmin user, basic roles)

### 1.5 Authentication System (Session Based)

- [x] **Backend**: Verify Auth scaffolding dari Starter Kit (Login, Register, Password Reset)
- [x] **Backend**: Setup middleware `HandleInertiaRequests` untuk share auth user global data
- [ ] **Backend**: Implement rate limiting untuk login
- [ ] **Frontend**: Customize Login page (`Pages/Auth/Login.vue`) dengan UI baru
- [ ] **Frontend**: Setup Persistent Layouts (`Layouts/GuestLayout.vue`, `Layouts/AppLayout.vue`)
- [ ] **Frontend**: Implement role-based redirection setelah login
- [ ] Test authentication flow end-to-end

### 1.6 Base UI Layout

- [x] **Frontend**: Update `AppLayout.vue` dengan Sidebar dan Header
- [x] **Frontend**: Buat `Sidebar.vue` component dengan navigasi aktif (menggunakan `page.url`)
- [x] **Frontend**: Buat `Header.vue` component dengan user dropdown
- [x] **Frontend**: Buat `Breadcrumb.vue` component
- [x] **Frontend**: Setup Flash Message handling (Toast) via Inertia Shared Props
- [x] **Frontend**: Implement dark mode toggle (Shadcn theme)
- [x] **Frontend**: Test responsive layout (desktop, tablet, mobile)

---

## 📦 Phase 2: Core Modules (Week 3-5)

### 2.1 Company Management Module

#### Backend

- [x] Generate Laravel Module: Company (atau gunakan folder `app/Http/Controllers`)
- [x] Buat migration untuk `companies` table
- [x] Buat Company model dengan relationships
- [x] Buat CompanyService (Business Logic)
- [x] Buat **CompanyController** (Web Resource Controller):
  - [x] `index()`: Return `Inertia::render('Company/Index')` dengan paginated data
  - [x] `create()`: Return `Inertia::render('Company/Create')`
  - [x] `store()`: Validate -> Save -> Redirect `to_route('companies.index')` with flash
  - [x] `edit()`: Return `Inertia::render('Company/Edit')`
  - [x] `update()`: Validate -> Update -> Redirect
  - [x] `destroy()`: Delete -> Redirect
- [x] Buat Form Request untuk validation
- [x] Implement file upload untuk logo
- [x] Implement company data scoping (Global Scope / Trait)
- [x] Write Feature Tests (Inertia Testing)

#### Frontend

- [x] Buat folder `resources/js/Pages/Company`
- [x] Buat `Company/Index.vue`:
  - [x] Terima props `companies` dan `filters`
  - [x] Implement DataTable dengan search & pagination (Inertia Link)
- [x] Buat `Company/Create.vue`:
  - [x] Gunakan `useForm` dari `@inertiajs/vue3`
  - [x] Implement form layout dengan Shadcn
- [x] Buat `Company/Edit.vue`:
  - [x] Bind form data dari props
- [x] Buat `Company/Show.vue` (Detail View)
- [x] Implement logo upload preview
- [x] Setup routes di `routes/web.php`
- [x] Add menu item ke Sidebar
- [x] Test CRUD operations end-to-end

### 2.2 Infrastructure Area Module

#### Backend

- [x] Buat migration untuk `infrastructure_areas` table
- [x] Buat InfrastructureArea model (Self-referencing hierarchy)
- [x] Buat InfrastructureAreaController:
  - [x] Implement CRUD operations dengan Inertia responses
  - [x] Implement logic hierarchy tree
- [x] Buat Form Request validation
- [x] Implement company scoping
- [x] Write Tests

#### Frontend

- [x] Buat folder `resources/js/Pages/Area` (Created in Modules)
- [x] Buat `Area/Index.vue` dengan Tree View component
- [x] Buat `Area/Create.vue` & `Area/Edit.vue`
- [x] Implement cascading dropdown (Provinsi -> Desa) menggunakan partial reloads / API calls
- [x] Buat `Area/Show.vue` dengan stats dashboard (Placeholder)
- [x] Setup routes di `routes/web.php`
- [x] Add menu item ke Sidebar

### 2.3 User & Role Management Module

#### Backend

- [x] Setup `spatie/laravel-permission` (atau custom implementation)
- [x] Buat UserController:
  - [x] CRUD operations dengan Inertia
  - [x] Assign role logic
- [ ] Buat RoleController (Optional, jika ingin dynamic roles)
- [x] Seed initial roles & permissions
- [ ] Implement avatar upload

#### Frontend

- [x] Buat folder `resources/js/Pages/User`
- [x] Buat `User/Index.vue`, `Create.vue`, `Edit.vue`
- [x] Implement Role Selector component
- [x] Implement Permission Matrix component (jika perlu)
- [x] Setup routes
- [x] Test user management flow

### 2.4 Dashboard Module

#### Backend

- [ ] Buat DashboardController:
  - [ ] `index()`: Aggregate stats, recent activities, map data -> Return Inertia view
- [ ] Implement caching untuk statistics query

#### Frontend

- [x] Buat `resources/js/Pages/Dashboard.vue`
- [x] Buat komponen Dashboard:
  - [x] `StatsCard.vue`
  - [x] `ActivityList.vue`
  - [x] `MapOverview.vue` (Leaflet integration)
- [ ] Install Chart.js / ApexCharts
- [x] Setup route `/dashboard`

---

## 🏗️ Phase 3: Infrastructure Modules (Week 6-9)

### 3.1 POP Management Module

#### Backend

- [x] Buat migration `pops` table
- [x] Buat Pop model & controller
- [x] Implement CRUD dengan Inertia
- [ ] Implement Multiple File Upload (Photos)
- [x] Form Request untuk Geo Coordinates validation

#### Frontend

- [x] Buat `Pages/Pop` CRUD views
- [x] Buat `PopForm.vue` dengan **LocationPicker** (Map)
- [ ] Buat `PhotoUpload.vue` component (support multiple, preview)
- [ ] Implement Photo Gallery di Detail View
- [x] Setup routes & sidebar menu

### 3.2 Server Management Module

#### Backend

- [x] Buat migration `servers` table
- [x] Buat Server model & controller
- [ ] Implement logic categorised photos (Rack, Room, etc.)

#### Frontend

- [x] Buat `Pages/Server` CRUD views
- [x] Implement Server Filter (by function: OLT, NOC, etc.)
- [ ] Implement Tabbed interface di Detail View (Overview, Photos, Devices)

### 3.3 Passive Device Module

#### Backend

- [x] Buat migration `passive_devices` (Polymorphic)
- [x] Buat Models: `Odf`, `Tiang`, `Odp`, `Kabel`, etc.
- [x] Buat PassiveDeviceController:
  - [x] Handle dynamic input fields berdasarkan `device_type`
  - [x] Store specs sebagai JSON column

#### Frontend

- [x] Buat `Pages/PassiveDevice` views
- [x] Implement **Dynamic Form** component:
  - [x] Switch inputs berdasarkan tipe device yang dipilih
  - [x] Reuse `LocationPicker`
- [x] Implement Type Filtering di Index page

### 3.4 Active Device Module

#### Backend

- [x] Buat migration `active_devices`
- [x] Buat migration `device_connections`
- [x] Buat Models: `Router`, `Switch`, `Olt`, etc.
- [x] Buat ActiveDeviceController
- [x] Implement Topology Logic (Connections)

#### Frontend

- [x] Buat `Pages/ActiveDevice` views
- [x] Implement **Topology Visualizer** (menggunakan library graph/tree)
- [x] Implement Connection Manager interface

---

## 🔧 Phase 4: Advanced Modules (Week 10-11)

### 4.1 Splicing Point Module

#### Backend

- [ ] CRUD Splicing Points
- [ ] Relasi ke Joint Box
- [ ] Photo documentation upload

#### Frontend

- [ ] Views untuk Splicing Points
- [ ] Visualisasi sambungan core (Optional: diagram sederhana)

### 4.2 CPE Module

- [x] CRUD CPE
- [x] Relasi ke Active Device (Uplink)
- [x] Frontend views (Index, Create, Edit, Show)
- [x] Integration with Sidebar

### 4.3 Map Integration (GIS)

#### Frontend

- [x] Buat `Pages/Map/Index.vue` (Full screen map)
- [x] Implement Clustering (Leaflet.markercluster)
- [x] Filter layers (POPs, ODP, Fiber Routes)
- [ ] Search location / asset
- [ ] Detail popup on click

---

## 🎨 Phase 5: Enhancement & Testing (Week 12-13)

### 5.1 Performance Optimization

- [ ] **Backend**: Eager loading relationships (N+1 problem)
- [ ] **Backend**: Cache heavy queries
- [ ] **Frontend**: Implement **Code Splitting** (Inertia default supports this via dynamic imports)
- [ ] **Frontend**: Lazy load heavy components (Maps, Charts)
- [ ] **Frontend**: Optimize Image loading

### 5.2 Security Hardening

- [ ] **Backend**: Review Policies/Gates (Authorization)
- [ ] **Backend**: File upload validation (Mime types, size)
- [ ] **Frontend**: Ensure output escaping (Vue default)

### 5.3 Testing

- [ ] **Backend**: PHPUnit/Pest Tests
  - [ ] Test Authentication
  - [ ] Test CRUD Permissions
  - [ ] Test Data Scoping
- [ ] **Frontend**: Vitest / Cypress
  - [ ] Test Critical Flows (Create POP, Add Device)
  - [ ] Test Form Validations

### 5.4 Documentation

- [ ] API Docs (jika ada external API)
- [ ] User Manual (Wiki/PDF)
- [ ] Developer Docs (Setup guide)

---

## 🚢 Phase 6: Deployment & Training (Week 14)

### 6.1 Production Environment

- [ ] VPS Setup (Ubuntu/Debian)
- [ ] Docker Production Setup (Optimized images)
- [ ] SSL / Domain Setup
- [ ] CI/CD Pipeline (GitHub Actions -> Deploy)

### 6.2 Training & Handover

- [ ] User Training Sessions
- [ ] Handover Credentials & Documentation
- [ ] Post-launch Support plan
