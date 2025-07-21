<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Sticker - {{ $device->asset_code }}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .toolbar {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .print-button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-button:hover {
            background: #2563eb;
        }
        
        .back-button {
            background: #6b7280;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        
        .back-button:hover {
            background: #4b5563;
        }
        
        .sticker-container {
            background: white;
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .sticker {
            width: 10cm;
            height: 5cm;
            padding: 10px;
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid #e5e7eb;
            position: relative;
        }
        
        .qr-section {
            flex: 0 0 auto;
            margin-right: 15px;
        }
        
        .qr-code {
            width: 3.5cm;
            height: 3.5cm;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }
        
        .qr-code img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .info-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #374151;
        }
        
        .asset-code {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 6px;
        }
        
        .brand-name {
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
        }
        
        .serial-number {
            font-family: monospace;
            font-size: 11px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }
        
        .condition {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            text-align: center;
            width: fit-content;
        }
        
        .condition.baik {
            background: #d1fae5;
            color: #065f46;
        }
        
        .condition.rusak {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .condition.perlu-pengecekan {
            background: #fef3c7;
            color: #92400e;
        }
        
        .specs {
            font-size: 9px;
            color: #9ca3af;
            line-height: 1.2;
        }
        
        .briven-logo {
            position: absolute;
            top: 5px;
            right: 8px;
            font-size: 10px;
            color: #3b82f6;
            font-weight: bold;
        }
        
        .assignment-info {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button class="print-button" onclick="window.print()">🖨️ Print Sticker</button>
        <a href="javascript:history.back()" class="back-button">← Back to Filament</a>
        <span style="color: #6b7280; font-size: 14px;">Asset Code: {{ $device->asset_code }}</span>
    </div>
    
    <div class="sticker-container">
        <div class="sticker">
            <div class="briven-logo">BRIVEN</div>
            
            <div class="qr-section">
                <div class="qr-code">
                    <img src="{{ $qrCodeDataUrl }}" alt="QR Code for {{ $device->asset_code }}">
                </div>
            </div>
            
            <div class="info-section">
                <div class="asset-code">{{ $device->asset_code }}</div>
                <div class="brand-name">{{ $device->brand }} {{ $device->brand_name }}</div>
                <div class="serial-number">{{ $device->serial_number }}</div>
                <div class="condition {{ strtolower(str_replace(' ', '-', $device->condition)) }}">
                    {{ $device->condition }}
                </div>
                
                @if($device->spec1 || $device->spec2 || $device->spec3)
                <div class="specs">
                    @if($device->spec1) {{ $device->spec1 }}<br>@endif
                    @if($device->spec2) {{ $device->spec2 }}<br>@endif
                    @if($device->spec3) {{ $device->spec3 }}<br>@endif
                    @if($device->spec4) {{ $device->spec4 }}<br>@endif
                    @if($device->spec5) {{ $device->spec5 }}@endif
                </div>
                @endif
                
                @if($device->currentAssignment)
                <div class="assignment-info">
                    📍 {{ $device->currentAssignment->branch->unit_name ?? 'Unknown Branch' }}<br>
                    👤 {{ $device->currentAssignment->user->name ?? 'Unknown User' }}
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus print dialog when page loads (for better UX)
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Uncomment to auto-open print dialog
            // setTimeout(() => window.print(), 500);
        });
    </script>
</body>
</html>
