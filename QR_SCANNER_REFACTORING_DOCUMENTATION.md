# QR Scanner Modular Implementation

Implementasi QR Scanner yang telah direfactor agar lebih modular dan dapat digunakan di berbagai tempat dalam aplikasi Filament/Livewire.

## Komponen yang Dibuat

### 1. Livewire Component (`app/Livewire/QrScanner.php`)
Komponen utama yang menangani logika QR scanning dengan berbagai mode:
- **full**: Mode halaman penuh (untuk QR Scanner page)
- **modal**: Mode modal (untuk form integration)  
- **inline**: Mode inline (untuk embedding di form)

### 2. QRCodeService Enhancement (`app/Services/QRCodeService.php`)
Ditambahkan method `processScannedCode()` untuk memproses data QR code yang di-scan.

### 3. Blade Component (`app/View/Components/QrScanner.php`)
Wrapper component untuk memudahkan penggunaan QR Scanner di berbagai tempat.

### 4. API Endpoint Internal
Endpoint baru untuk QR scanning di `routes/api.php`:
```
GET /api/v1/internal/devices/qr-scan/{assetCode}
```

## Cara Penggunaan

### 1. Di Halaman Penuh (QR Scanner Page)
```blade
<x-qr-scanner mode="full" />
```

### 2. Di Modal Form (seperti Device Assignment)
```php
// Di form schema Filament Resource
->suffixAction(
    Forms\Components\Actions\Action::make('scanQR')
        ->icon('heroicon-o-qr-code')
        ->tooltip('Scan QR Code')
        ->color('primary')
        ->modalHeading('QR Code Scanner')
        ->modalWidth('lg')
        ->modalContent(view('filament.components.qr-scanner-modal-content'))
        ->action(function () {
            // Modal action is handled by the QR scanner component
        })
)
```

### 3. Mode Inline
```blade
<x-qr-scanner 
    mode="inline" 
    :auto-start="true"
    target-input="device_id"
    :emit-events="['device-scanned']"
/>
```

### 4. Custom Event Handling
```blade
<div x-data="{}" x-on:device-selected.window="handleDeviceSelected($event.detail)">
    <x-qr-scanner mode="modal" />
</div>

<script>
function handleDeviceSelected(detail) {
    console.log('Device selected:', detail.device);
    console.log('Asset Code:', detail.assetCode);
    console.log('Device ID:', detail.deviceId);
}
</script>
```

## Fitur Utama

### 1. **Kompatibilitas Livewire SPA**
- Menggunakan Alpine.js untuk JavaScript handling
- Event cleanup pada navigasi
- Proper integration dengan Livewire lifecycle

### 2. **Auto Camera Management**
- Otomatis start/stop camera
- Cleanup saat navigasi atau modal ditutup
- Error handling yang proper

### 3. **Multiple Integration Methods**
- Direct device selection untuk form
- Custom events untuk handling advanced
- API integration untuk external use

### 4. **Responsive Design**
- Mobile-friendly camera interface
- Adaptive layout untuk berbagai screen size
- Dark mode support

## Event System

### Events yang Di-emit:
1. **device-selected**: Saat device berhasil di-scan
   ```javascript
   {
     deviceId: number,
     assetCode: string,
     device: object
   }
   ```

2. **scanner-success**: Saat scanning berhasil
3. **scanner-error**: Saat terjadi error
4. **scanner-start**: Saat camera mulai aktif
5. **scanner-stop**: Saat camera dihentikan
6. **scanner-reset**: Saat scanner di-reset

## Troubleshooting

### 1. Camera tidak bisa dibuka di Filament SPA
✅ **Solved**: Menggunakan Alpine.js dengan proper lifecycle management

### 2. Button tidak bisa diklik di modal
✅ **Solved**: Menggunakan Alpine.js event handling daripada vanilla JavaScript

### 3. Memory leak saat navigasi
✅ **Solved**: Proper cleanup dengan Livewire navigation events

## File Structure
```
app/
├── Livewire/
│   └── QrScanner.php
├── View/Components/
│   └── QrScanner.php
├── Services/
│   └── QRCodeService.php (enhanced)
└── Filament/
    └── Pages/
        └── QRScanner.php (updated)

resources/views/
├── livewire/
│   ├── qr-scanner.blade.php
│   └── partials/
│       ├── qr-scanner-camera.blade.php
│       ├── qr-scanner-result.blade.php
│       ├── qr-scanner-modal-content.blade.php
│       └── qr-scanner-inline.blade.php
├── components/
│   └── qr-scanner.blade.php
└── filament/
    ├── pages/
    │   └── qr-scanner.blade.php (updated)
    └── components/
        └── qr-scanner-modal-content.blade.php
```

## Dependencies
- `html5-qrcode@2.3.8`: Library untuk QR code scanning
- `Alpine.js`: Untuk reactive UI components
- `Livewire`: Untuk server-side interaction
- `Filament`: UI framework

## Usage Examples

### Example 1: Device Assignment Form
```php
// Dalam DeviceAssignmentResource.php
Select::make('device_id')
    ->suffixAction(
        Forms\Components\Actions\Action::make('scanQR')
            ->icon('heroicon-o-qr-code')
            ->modalContent(view('filament.components.qr-scanner-modal-content'))
            // ... other config
    )
```

### Example 2: Custom Form dengan QR Scanner
```blade
<form x-data="{ selectedDevice: null }" x-on:device-selected.window="selectedDevice = $event.detail">
    <input type="hidden" name="device_id" x-model="selectedDevice?.deviceId">
    
    <x-qr-scanner mode="inline" />
    
    <div x-show="selectedDevice">
        <p>Selected: <span x-text="selectedDevice?.assetCode"></span></p>
    </div>
</form>
```

### Example 3: Multiple QR Scanners di halaman yang sama
```blade
<!-- Scanner 1 for main device -->
<x-qr-scanner 
    mode="inline" 
    :emit-events="['main-device-selected']"
/>

<!-- Scanner 2 for backup device -->
<x-qr-scanner 
    mode="inline" 
    :emit-events="['backup-device-selected']"
/>

<script>
document.addEventListener('main-device-selected', function(e) {
    console.log('Main device:', e.detail);
});

document.addEventListener('backup-device-selected', function(e) {
    console.log('Backup device:', e.detail);
});
</script>
```

## Testing & Implementation Guide

### 1. Testing QR Scanner Page
1. Jalankan development server: `php artisan serve`
2. Login ke aplikasi Filament
3. Navigate ke **Device Management > QR Scanner**
4. Test camera functionality dan QR code scanning

### 2. Testing Form Integration
1. Navigate ke **Device Management > Device Assignments**
2. Click **New Assignment**
3. Pada field Device, click icon QR Code
4. Test scanning QR code di modal

### 3. Manual Testing Steps
```bash
# 1. Test API endpoint
curl -X GET "http://localhost:8000/api/v1/internal/devices/qr-scan/AST001"

# 2. Generate test QR code
# Format: briven-{asset_code}
# Example: briven-AST001

# 3. Test with mobile device camera
# - Open QR Scanner page
# - Use mobile browser to access localhost via network IP
# - Test camera permissions and scanning
```

## Benefits dari Refactoring

1. **Reusability**: Dapat digunakan di berbagai tempat
2. **Maintainability**: Kode terpusat dan mudah dirawat
3. **Consistency**: UI dan behavior yang konsisten
4. **Scalability**: Mudah ditambahkan fitur baru
5. **Performance**: Proper resource cleanup
6. **Developer Experience**: Easy to integrate dan well documented
