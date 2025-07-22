# QR Scanner Usage Examples

## Example 1: Basic Form Integration

```php
// Di dalam form resource Filament
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;

Select::make('device_id')
    ->label('Select Device')
    ->options(Device::pluck('asset_code', 'device_id'))
    ->searchable()
    ->suffixAction(
        Action::make('scanQR')
            ->icon('heroicon-o-qr-code')
            ->tooltip('Scan QR Code')
            ->color('primary')
            ->modalHeading('QR Code Scanner')
            ->modalWidth('lg')
            ->modalContent(view('filament.components.qr-scanner-modal-content'))
            ->extraModalAttributes([
                'x-on:device-selected.window' => '
                    if ($event.detail.deviceId) {
                        $wire.set("data.device_id", $event.detail.deviceId);
                        close();
                    }
                '
            ])
    )
```

## Example 2: Custom Livewire Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;

class CustomDeviceForm extends Component
{
    public $selectedDeviceId;
    public $deviceInfo = null;

    protected $listeners = ['device-selected' => 'onDeviceSelected'];

    public function onDeviceSelected($data)
    {
        $this->selectedDeviceId = $data['deviceId'];
        $this->deviceInfo = Device::find($data['deviceId']);
    }

    public function render()
    {
        return view('livewire.custom-device-form');
    }
}
```

```blade
{{-- resources/views/livewire/custom-device-form.blade.php --}}
<div>
    <div class="mb-4">
        <label>Device Selection</label>
        <x-qr-scanner mode="inline" />
    </div>
    
    @if($deviceInfo)
        <div class="p-4 bg-green-50 rounded-lg">
            <h3>Selected Device</h3>
            <p>Asset Code: {{ $deviceInfo->asset_code }}</p>
            <p>Brand: {{ $deviceInfo->brand }} {{ $deviceInfo->brand_name }}</p>
        </div>
    @endif
    
    <input type="hidden" wire:model="selectedDeviceId" name="device_id">
</div>
```

## Example 3: Multiple QR Scanners

```blade
<div x-data="multipleScanner()">
    <!-- Primary Device Scanner -->
    <div class="mb-6">
        <h3>Primary Device</h3>
        <x-qr-scanner 
            mode="inline" 
            :emit-events="['primary-device-selected']"
        />
        <div x-show="primaryDevice">
            <p>Selected: <span x-text="primaryDevice?.assetCode"></span></p>
        </div>
    </div>
    
    <!-- Backup Device Scanner -->
    <div class="mb-6">
        <h3>Backup Device</h3>
        <x-qr-scanner 
            mode="inline" 
            :emit-events="['backup-device-selected']"
        />
        <div x-show="backupDevice">
            <p>Selected: <span x-text="backupDevice?.assetCode"></span></p>
        </div>
    </div>
</div>

<script>
function multipleScanner() {
    return {
        primaryDevice: null,
        backupDevice: null,
        
        init() {
            this.$el.addEventListener('primary-device-selected', (e) => {
                this.primaryDevice = e.detail;
            });
            
            this.$el.addEventListener('backup-device-selected', (e) => {
                this.backupDevice = e.detail;
            });
        }
    }
}
</script>
```

## Example 4: QR Scanner dengan Validation

```php
// Di Livewire Component
class DeviceAssignmentForm extends Component
{
    public $deviceId;
    public $userId;
    public $deviceAvailable = true;
    
    protected $listeners = ['device-selected' => 'validateDevice'];
    
    public function validateDevice($data)
    {
        $device = Device::find($data['deviceId']);
        
        if (!$device) {
            $this->addError('device', 'Device not found');
            return;
        }
        
        if ($device->currentAssignment) {
            $this->deviceAvailable = false;
            $this->addError('device', 'Device is already assigned');
            return;
        }
        
        $this->deviceId = $data['deviceId'];
        $this->deviceAvailable = true;
        $this->resetErrorBag('device');
    }
}
```

## Example 5: API Integration

```javascript
// Frontend JavaScript untuk menggunakan QR Scanner
class QRScannerAPI {
    static async findDevice(assetCode) {
        try {
            const response = await fetch(`/api/v1/internal/devices/qr-scan/${assetCode}`);
            const result = await response.json();
            
            if (result.success) {
                return result.device;
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error finding device:', error);
            throw error;
        }
    }
    
    static async handleQRScan(qrData) {
        // Extract asset code from QR data
        const assetCode = qrData.startsWith('briven-') 
            ? qrData.substring(7) 
            : qrData;
            
        try {
            const device = await this.findDevice(assetCode);
            
            // Dispatch custom event
            window.dispatchEvent(new CustomEvent('device-found', {
                detail: { device, assetCode }
            }));
            
            return device;
        } catch (error) {
            window.dispatchEvent(new CustomEvent('device-error', {
                detail: { error: error.message, assetCode }
            }));
            
            throw error;
        }
    }
}

// Usage
document.addEventListener('device-found', (e) => {
    console.log('Device found:', e.detail.device);
    // Update form fields, show device info, etc.
});

document.addEventListener('device-error', (e) => {
    console.error('Device error:', e.detail.error);
    // Show error message to user
});
```

## Example 6: QR Scanner di Modal Custom

```php
// Di Filament Resource atau Page
public function scanQRAction(): Action
{
    return Action::make('scanQR')
        ->label('Scan QR Code')
        ->icon('heroicon-o-qr-code')
        ->color('primary')
        ->modalHeading('Device QR Scanner')
        ->modalDescription('Point your camera at a device QR code')
        ->modalWidth('lg')
        ->modalContent(view('components.qr-scanner-modal'))
        ->modalActions([])
        ->action(function () {
            // No action needed, handled by QR scanner component
        });
}
```

```blade
{{-- resources/views/components/qr-scanner-modal.blade.php --}}
<div 
    x-data="{ selectedDevice: null }"
    x-on:device-selected.window="
        selectedDevice = $event.detail;
        $dispatch('close-modal');
        $wire.set('data.device_id', $event.detail.deviceId);
    "
>
    <x-qr-scanner mode="modal" />
    
    <div x-show="selectedDevice" class="mt-4 p-3 bg-green-50 rounded">
        <p class="text-sm text-green-800">
            Device selected: <span x-text="selectedDevice?.assetCode"></span>
        </p>
    </div>
</div>
```

## Troubleshooting Common Issues

### 1. Camera not working in modals
**Problem**: Camera tidak bisa diakses dalam modal Filament
**Solution**: Pastikan modal memiliki proper z-index dan tidak ada konflik dengan Livewire SPA

```css
/* Add to your CSS if needed */
.fi-modal {
    z-index: 9999;
}

#qr-reader video {
    max-width: 100%;
    height: auto;
}
```

### 2. Events not firing
**Problem**: Event device-selected tidak terdeteksi
**Solution**: 
- Pastikan Alpine.js sudah loaded
- Gunakan `x-on:device-selected.window` bukan `x-on:device-selected`
- Check console untuk error JavaScript

### 3. Device not found
**Problem**: QR code ter-scan tapi device tidak ditemukan
**Solution**:
- Verify QR code format: `briven-{asset_code}`
- Check database untuk memastikan device exists
- Verify API endpoint `/api/v1/internal/devices/qr-scan/{assetCode}`

### 4. Memory leaks
**Problem**: Camera tidak stop saat navigasi
**Solution**: QR Scanner component sudah handle cleanup otomatis via Livewire navigation events

### 5. Multiple scanners conflict
**Problem**: Multiple QR scanner di halaman yang sama saling bentrok
**Solution**: Gunakan unique wire:key dan emit events yang berbeda

```blade
<x-qr-scanner 
    mode="inline" 
    :emit-events="['scanner1-device-selected']"
    wire:key="scanner-1"
/>

<x-qr-scanner 
    mode="inline" 
    :emit-events="['scanner2-device-selected']"
    wire:key="scanner-2"
/>
```
