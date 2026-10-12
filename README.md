# FleetBe — Fleet & Asset Management Backend API

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-14%2B-336791?style=for-the-badge&logo=postgresql)](https://postgresql.org)
[![Docker Ready](https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker)](https://docker.com)
[![Railway](https://img.shields.io/badge/Railway-Deployable-0B0D0E?style=for-the-badge&logo=railway)](https://railway.app)

**FleetBe** is a RESTful API backend designed for enterprise vehicle fleet operations, driver management, inventory logistics (fuels & spare parts), maintenance scheduling, and resource request workflows.

---

## 📚 Table of Contents

- [Key Features](#-key-features)
- [Interactive API Documentation](#-interactive-api-documentation)
- [Quick Start](#-quick-start)
- [API Overview](#-api-overview)
- [Documentation Index](#-documentation-index)

---

## 🚀 Key Features

* **Multi-Tenant Organizations:** Hierarchical modeling organizing all vehicles, drivers, inventory, and staff under individual organizations (`/api/v1/organizations`).
* **Dual-Guard Authentication:** Secure access control powered by Laravel Sanctum with separate authentication flows for corporate staff (`users`) and vehicle operators (`drivers`).
* **Vehicle & Asset Lifecycle:** Full tracking of vehicle specifications, manufacturer info, condition, operational status, license plates, asset barcodes, and security tags.
* **Vehicle Assignment Workflow:** Driver checkout/check-in tracking with initial and final odometer readings, assigned staff logs, and release controls.
* **Resource & Inventory Logistics:**
  * Multi-fuel management (Petrol, Diesel, Gas) with depot reserve tracking.
  * Spare parts inventory tracking with unit metrics and safety stock thresholds.
  * Polymorphic multi-depot storage modeling (`storages`).
* **Scheduled & Ad-Hoc Maintenance:** Preventative and corrective maintenance procedure logging.
* **Polymorphic Request & Approval Pipeline:** Unified request submission and approval engine for fuel dispensing, spare part replacements, and repair work orders.
* **Vendor & Supplier Directory:** Vendor directory mapping fuel depots, parts distributors, and maintenance centers.

---

## 📖 Interactive API Documentation

FleetBe provides three interactive documentation interfaces:

| Interface | URL Path | Description |
|---|---|---|
| **Swagger UI** | `/swagger` *(or `/docs/swagger`, `/api/documentation`)* | Classic Swagger UI by SmartBear with full "Try it out" console and Bearer token authorization. |
| **Scalar UI** | `/scalar` | Modern, clean interactive API documentation powered by `@scalar/api-reference`. |
| **Scribe UI** | `/docs` | Comprehensive multi-language API documentation with request examples. |
| **Raw OpenAPI Spec** | `/docs.openapi` | Standard OpenAPI 3.0 specification in YAML format. |

---

## ⚡ Quick Start

### Option A: Docker Compose (Recommended)

1. Initialize environment file:
   ```bash
   cp .env.example .env
   ```
2. Build and launch containers:
   ```bash
   docker compose up --build
   ```
3. Access API & Documentation:
   * **Swagger UI:** `http://localhost:8080/swagger`
   * **API Base:** `http://localhost:8080/api/v1`
   * **Health Status:** `http://localhost:8080/api/v1/status`

### Option B: Local Machine Setup

1. Install Composer dependencies:
   ```bash
   composer install
   ```
2. Generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Run migrations and seed default organization:
   ```bash
   php artisan migrate
   php artisan db:seed --class=DatabaseSeeder
   ```
4. Start local development server:
   ```bash
   php artisan serve --port=8080
   ```

---

## 📡 API Overview

All API endpoints are versioned under `/api/v1`:

| Area | Route Group | Description |
|---|---|---|
| **Health** | `GET /v1/status` | System health check |
| **Organizations** | `/v1/organizations` | List, create, and view tenant organizations |
| **Auth** | `/v1/auth/*` | User registration, login, logout, password resets |
| **Drivers** | `/v1/driver/*` | Driver registration, login, password changes, profiles |
| **Vehicles** | `/v1/vehicles` | Vehicle CRUD operations and filtering |
| **Assignments** | `/v1/vehicle-assignments` | Check out, track, and release vehicle assignments |
| **Fuels** | `/v1/fuel` | Fuel depot reserves and allocation tracking |
| **Spare Parts** | `/v1/sparepart` | Spare parts catalog and warehouse inventory |
| **Maintenance** | `/v1/maintenance` | Maintenance service schedules and types |
| **Suppliers** | `/v1/suppliers` | Vendor directory with linked services |
| **Requests** | `/v1/requests` | Resource request submission, approval, and rejection |

---

## 📁 Documentation Index

Detailed specifications are available in the [`docs/`](docs/) directory:
* [Architecture Guide](docs/ARCHITECTURE.md)
* [Database Schema Specification](docs/DATABASE_SCHEMA.md)
* [API Reference Guide](docs/API_REFERENCE.md)
* [Audit & Security Report](docs/AUDIT_AND_SECURITY.md)
* [Deployment Guide](docs/DEPLOYMENT.md)

---

## 📄 License

The FleetBe backend is licensed under the [MIT License](LICENSE).
