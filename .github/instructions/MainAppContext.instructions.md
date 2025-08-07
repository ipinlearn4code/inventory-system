---
applyTo: '**'
---
#  Project Summary: Inventory Control System

## Purpose

A comprehensive inventory management platform built with Laravel 12, aimed at efficiently tracking IT devices and equipment across branches and departments. It includes role-based access, QR code integration, assignment tracking, and monitoring tools tailored for enterprise-level use.

## Key Features

* **Device Inventory Management**: Full CRUD with auto asset code
* **QR Code Integration**: Generation and scanning (prefix: `briven-`)
* **Multi-Branch Support**: Departments and branch-based segregation
* **Role-Based Access**: SuperAdmin, Admin, User
* **Device Assignment**: Letters and logs for allocation and return
* **RESTful API**: Mobile app-ready, Sanctum-based
* **Admin Dashboard**: Analytics and reporting
* **File Storage**: MinIO/AWS S3 for document support
* **Device Condition Monitoring**: Real-time tracking
* **Audit Logging**: Complete activity records
* **Bulk Operations**: Sticker generation, bulk device management

## Technologies Used

### Backend

* **Laravel 12**
* **Filament 3.3** (Admin Panel)
* **Sanctum + Custom Auth**
* **Spatie Permissions**
* **Endroid QR Code**
* **AWS S3 via Flysystem**

### Frontend

* **Vite**, **Axios**
* **TailwindCSS** + Plugins (forms, typography)
* **Alpine.js** (Reactive UI)
* **Livewire** (Real-time components)
* **html5-qrcode** (QR scanning)
* **Heroicons** (Icon library)
* **Filament Forms** (Custom components)


### Dev Environment
* **PHP 8.3**
* **Node.js 18+**
* **MySQL 8+**
* **MinIO** (for local S3 emulation)
* **Docker**

### Dev Tools

* **Vite HMR**
* **Concurrently**
* **Laravel Tinker, Pail, Telescope**

### Database & Storage

* **MySQL**, **MinIO / AWS S3**

## System Architecture

### Structure

* **MVC with Filament Enhancements**
* **Role-Based Access (Spatie)**
* **Sanctum API Auth + Session Auth**

### Models

* **Users**: Departments, branches, roles
* **Devices**: Asset codes, QR codes
* **DeviceAssignments**: Allocation logs
* **Bribox Categories**
* **Branches & Departments**
* **InventoryLog**: Audit records

### QR Code Workflow

* Generated with prefix `briven-`
* Scanned via browser + Alpine.js
* Supports printable sticker generation

### API

* **Base URL**: `/api/v1/`
* Auth, device, user, admin endpoints
* Role-specific access control
* Mobile support (full CRUD)

## Usage

### Dev Commands

```bash
php artisan serve --host=0.0.0.0 --port=8000
composer run dev
npm run dev     # Dev with HMR
npm run build   # Production
```

### URLs

* **Admin**: `/admin`
* **Login**: `/login`
* **API Base**: `/api/v1/`
* **QR Code Generator**: `/qr-code/generate/{assetCode}`
* **QR Scanner**: `/qr-scanner/`

### Roles & Access

| Role       | Access             | Capabilities                         |
| ---------- | ------------------ | ------------------------------------ |
| SuperAdmin | Full               | All functions, users, configs        |
| Admin      | Device/User manage | CRUD, assignments, reports           |
| User       | Mobile-only        | View assigned devices, report issues |

### API Samples

* `POST /api/v1/auth/login`
* `GET /api/v1/user/devices`
* `GET /api/v1/admin/dashboard`

## Dev Features

* Hot Module Reloading
* Multi-process Dev Environment
* Custom Filament Theme (Corporate)
* Responsive Design (Tailwind)
* Alpine + Livewire Interactivity

## File Structure

```
├── app/
│   ├── Console/Commands          # Artisan Commands
│   ├── Contracts/                # Interfaces and contracts
│   ├── Filament/                 # Admin resources
│   ├── Http/Controllers/         # API/Web
│   ├── Livewire/                 # Components
│   ├── Models/                   # Eloquent Models
│   ├── Providers/                # Service Providers
│   ├── Repositories/             # Data access
│   └── Services/                 # QR, others
├── resources/
│   ├── css/, js/, views/  # Assets + Blade
├── routes/
│   ├── api.php, web.php   # Routes
└── config/                # Configs
```

This system combines Laravel 12's power with modern frontend and admin tooling to deliver a robust, enterprise-ready inventory tracking solution.
