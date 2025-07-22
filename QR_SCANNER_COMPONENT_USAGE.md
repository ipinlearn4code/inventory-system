# QR Code Scanner Component Usage Examples

Komponen `QrCodeScanner` ini adalah wrapper untuk library `fadlee/filament-qrcode-field` dengan customizable button interface.

## Setup

1. Pastikan library sudah terinstall:
```bash
composer require fadlee/filament-qrcode-field
```

2. Publish assets:
```bash
php artisan filament:assets
```

## Import

```php
use App\Filament\Forms\Components\QrCodeScanner;
```

## Usage Examples

### 1. Button dengan Text dan Icon (Default)

```php
QrCodeScanner::make('qr_scanner')
    ->label('Scan Device QR Code')
    ->asButton('📱 Scan QR Code', 'primary', 'md')
    ->withIcon('heroicon-o-qr-code')
    ->live()
    ->afterStateUpdated(function (string $state = null, Forms\Set $set) {
        // Handle scanned data
        if ($state) {
            // Process QR code data
            $set('some_field', $state);
        }
    })
```

### 2. Icon-Only Button

```php
QrCodeScanner::make('qr_scanner')
    ->label('QR Scanner')
    ->asIconButton('heroicon-o-qr-code', 'success', 'lg')
    ->live()
    ->afterStateUpdated(function (string $state = null, Forms\Set $set) {
        // Handle scanned data
    })
```

### 3. Outlined Button

```php
QrCodeScanner::make('qr_scanner')
    ->label('Scan Code')
    ->asButton('Scan QR Code', 'warning', 'md', true) // true = outlined
    ->withIcon('heroicon-o-camera')
    ->live()
    ->afterStateUpdated(function (string $state = null, Forms\Set $set) {
        // Handle scanned data
    })
```

### 4. Clickable Text Link

```php
QrCodeScanner::make('qr_scanner')
    ->label('QR Scanner')
    ->asClickableText('📱 Click here to scan QR code')
    ->live()
    ->afterStateUpdated(function (string $state = null, Forms\Set $set) {
        // Handle scanned data
    })
```

### 5. Berbagai Variasi Button

```php
// Small danger button
QrCodeScanner::make('emergency_scanner')
    ->asButton('Emergency Scan', 'danger', 'sm')
    ->withIcon('heroicon-o-exclamation-triangle')

// Large success button  
QrCodeScanner::make('main_scanner')
    ->asButton('Start Scanning', 'success', 'lg')
    ->withIcon('heroicon-o-check-circle')

// Outlined secondary button
QrCodeScanner::make('backup_scanner')
    ->asButton('Backup Scan', 'secondary', 'md', true)
    ->withIcon('heroicon-o-document-duplicate')
```

## Available Methods

### `asButton(string $text, string $color, string $size, bool $outlined = false)`
Membuat button dengan text.

**Parameters:**
- `$text`: Text button
- `$color`: 'primary', 'secondary', 'success', 'warning', 'danger'  
- `$size`: 'sm', 'md', 'lg'
- `$outlined`: Apakah menggunakan style outlined

### `asIconButton(string $icon, string $color, string $size, bool $outlined = false)`
Membuat button icon saja.

**Parameters:**
- `$icon`: Nama class Heroicon (e.g., 'heroicon-o-qr-code')
- `$color`: 'primary', 'secondary', 'success', 'warning', 'danger'
- `$size`: 'sm', 'md', 'lg'  
- `$outlined`: Apakah menggunakan style outlined

### `asClickableText(string $text)`
Membuat clickable text link.

**Parameters:**
- `$text`: Text yang bisa diklik

### `withIcon(string $icon)`
Menambah icon ke button (saat menggunakan `asButton`).

**Parameters:**
- `$icon`: Nama class Heroicon

## How It Works

1. **Extends Library Fadlee**: Komponen ini extend dari `QrCodeInput` library fadlee, jadi semua fungsionalitas QR scanning tetap sama
2. **Custom UI Only**: Yang kita custom hanya tampilan button/interface-nya saja
3. **Uses Existing Modal**: Modal dan JavaScript scanning menggunakan yang sudah ada dari library fadlee
4. **Filament Integration**: Terintegrasi penuh dengan form system Filament

## Features

- ✅ **Library Fadlee Integration**: Menggunakan modal dan JS scanning yang sudah ada
- ✅ **Custom Button Interface**: Button bisa dikustomisasi sesuai kebutuhan  
- ✅ **Filament Native**: Menggunakan komponen button Filament asli
- ✅ **Camera Integration**: Akses kamera otomatis dengan preferensi rear camera
- ✅ **State Management**: Terintegrasi dengan form state Filament
- ✅ **ZXing Library**: Menggunakan ZXing untuk scanning yang reliable
- ✅ **Live Updates**: Support untuk live() dan afterStateUpdated()

## Browser Compatibility

- Chrome/Chromium: Full support
- Firefox: Full support  
- Safari: Full support (iOS 11+)
- Edge: Full support

## Implementation Notes

1. Library fadlee sudah menyediakan modal dan JavaScript scanning
2. Komponen ini hanya customize tampilan trigger button-nya
3. Modal akan muncul otomatis saat button diklik
4. Scanned value akan otomatis di-set ke field state
5. Support untuk semua method dari parent QrCodeInput
