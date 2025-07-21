# 📝 Project Summary: Inventory Control System

## Purpose
A comprehensive Laravel-based inventory management system designed to track and manage IT devices/equipment across multiple branches and departments. It solves the problem of asset tracking, device assignment management, condition monitoring, and provides role-based access control for organizations managing large inventories of devices with QR code integration for efficient identification and tracking.

## Key Features
- **Device Inventory Management**: Complete CRUD operations with asset code generation
- **QR Code Integration**: Generation and scanning for device identification (with "briven-" prefix)
- **Multi-Branch Organization**: Support for multiple branches and departments
- **Role-Based Authentication**: Three-tier access system (User, Admin, SuperAdmin)
- **Device Assignment Tracking**: Track device assignments with assignment letters
- **RESTful API**: Complete mobile app integration with Sanctum authentication
- **Administrative Dashboard**: Analytics widgets and comprehensive reporting
- **File Storage Integration**: MinIO/S3 support for document management
- **Device Condition Monitoring**: Real-time status tracking and reporting
- **Audit Trail**: Complete activity logging and change tracking
- **Bulk Operations**: Bulk QR code sticker generation and device management

## Technologies Used

### Backend (PHP)
- **Language**: PHP 8.2+
- **Framework**: Laravel 12.0
- **Admin Panel**: Filament 3.3
- **Authentication**: Laravel Sanctum 4.1 + Custom session-based auth
- **Permissions**: Spatie Laravel Permission 6.20
- **QR Code Generation**: Endroid QR Code 6.0
- **File Storage**: League Flysystem AWS S3 v3 3.29
- **Development Tools**: Laravel Tinker, Laravel Pail, Laravel Telescope

### Frontend Dependencies

#### Core Framework & Build Tools
- **Vite**: 6.2.4 (Module bundler and dev server)
- **Laravel Vite Plugin**: 1.2.0 (Laravel-Vite integration)
- **Axios**: 1.8.2 (HTTP client for API requests)

#### CSS Framework & Styling
- **TailwindCSS**: 3.4.17 (Utility-first CSS framework)
- **@tailwindcss/forms**: 0.5.10 (Form styling plugin)
- **@tailwindcss/typography**: 0.5.16 (Typography plugin)
- **PostCSS**: 8.5.6 with PostCSS Nesting 13.0.2
- **Autoprefixer**: 10.4.21
- **Custom Filament Theme**: Corporate blue theme with custom scrollbars

#### JavaScript Libraries & CDN Dependencies
- **Alpine.js**: Integrated in Filament for reactive components
  - Used extensively in QR scanner modals (`x-data`, `@click`, `x-init`)
  - User menu dropdowns and interactive elements
- **Livewire**: Server-side reactive components
  - `PermissionToggle.php` - Role permission management
  - `StorageStatusAlert.php` - Storage monitoring alerts
  - Wire directives (`wire:click`, `wire:model.live`) throughout admin interface
- **html5-qrcode**: 2.3.8 (via CDN) - JavaScript QR code scanner library
- **TailwindCSS CDN**: Used in login page for standalone styling

#### Development & Build Process
- **Concurrently**: 9.0.1 (Running multiple dev processes simultaneously)
- **Vite HMR**: Hot module replacement for development
- **Laravel Mix**: Asset compilation and optimization

### Database & Storage
- **Database**: MySQL
- **Storage**: MinIO/AWS S3 integration
- **File Systems**: Local and cloud storage support

## Architecture / How It Works

### System Architecture
The system follows Laravel's MVC architecture enhanced with:
- **Filament Admin Panel**: Provides comprehensive admin interface
- **Multi-layered Authentication**: Custom session + Sanctum token-based API
- **Role-Based Access Control**: Using Spatie permissions package

### Core Data Models
- **Users**: Organized by departments and branches with role assignments
- **Devices**: Unique asset codes with QR code generation
- **DeviceAssignments**: Tracking device allocation and returns
- **Bribox Categories**: Device classification system
- **Branches & Departments**: Organizational structure
- **InventoryLog**: Audit trail for all operations

### QR Code System
- **Generation**: Server-side using Endroid QR Code library
- **Format**: "briven-{asset_code}" prefix for company identification
- **Scanning**: Browser-based using html5-qrcode library with Alpine.js integration
- **Stickers**: Printable sticker generation for physical labeling

### API Architecture
- **RESTful API**: `/api/v1/` endpoints with Sanctum authentication
- **Mobile App Support**: Complete CRUD operations for mobile clients
- **Role-Based Endpoints**: Different access levels for users, admins, and superadmins
- **Real-time Updates**: Livewire components for instant UI updates

## Usage / How to Run It

### Development Environment
```bash
# Start development server (currently running)
php artisan serve --host=0.0.0.0 --port=8000

# Development with all services
composer run dev  # Runs server, queue, logs, and vite concurrently

# Asset compilation
npm run dev        # Development mode with HMR
npm run build      # Production build
```

### Access Points
- **Admin Panel**: `http://localhost:8000/admin`
- **Login Page**: `http://localhost:8000/login`
- **API Base**: `http://localhost:8000/api/v1/`
- **QR Code Generation**: `/qr-code/generate/{assetCode}`
- **QR Scanner**: `/qr-scanner/`

### Authentication Levels
| Role | Access Level | Capabilities |
|------|-------------|--------------|
| SuperAdmin | Full system access | All operations, user management, system config |
| Admin | Device & user management | CRUD operations, reporting, assignments |
| User | Mobile app only | View assigned devices, report issues |

### API Endpoints
- **Authentication**: `POST /api/v1/auth/login`, `/refresh`, `/logout`
- **User Operations**: `GET /api/v1/user/devices`, `/profile`, `/history`
- **Admin Operations**: `GET /api/v1/admin/dashboard`, `/devices`
- **Device Management**: Full CRUD operations with role-based access

## Development Features
- **Hot Module Replacement**: Vite HMR for instant development feedback
- **Multi-process Development**: Concurrent server, queue, logs, and asset compilation
- **Custom Themes**: Corporate branding with Filament customization
- **Responsive Design**: Mobile-first approach with TailwindCSS
- **Real-time Components**: Livewire for dynamic user interfaces
- **Interactive Elements**: Alpine.js for client-side reactivity

## File Structure Highlights
```
├── app/
│   ├── Filament/          # Admin panel resources & widgets
│   ├── Http/Controllers/  # API and web controllers
│   ├── Livewire/         # Reactive components
│   ├── Models/           # Eloquent models
│   └── Services/         # Business logic (QR Code service)
├── resources/
│   ├── css/              # TailwindCSS and custom themes
│   ├── js/               # Alpine.js and Axios setup
│   └── views/            # Blade templates with QR components
├── routes/
│   ├── api.php           # RESTful API routes
│   └── web.php           # Web and QR code routes
└── config/               # Laravel and package configurations
```

This inventory system represents a modern, full-stack solution combining Laravel's robust backend capabilities with contemporary frontend technologies to deliver a comprehensive asset management platform suitable for enterprise environments.
