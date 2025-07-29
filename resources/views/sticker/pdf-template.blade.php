<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Code Stickers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .stickers-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            page-break-inside: avoid;
        }
        
        .sticker {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            page-break-inside: avoid;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .qr-code-container {
            margin-bottom: 12px;
        }
        
        .qr-code {
            width: 120px;
            height: 120px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin: 0 auto;
        }
        
        .error-icon {
            width: 60px;
            height: 60px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #dc2626;
            font-size: 24px;
        }
        
        .asset-code {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .device-info {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.4;
            text-align: left;
        }
        
        .info-row {
            margin-bottom: 4px;
        }
        
        .info-label {
            font-weight: 600;
            display: inline-block;
            width: 70px;
        }
        
        .info-value {
            word-break: break-word;
        }
        
        .error-message {
            color: #dc2626;
            font-size: 10px;
            margin-top: 8px;
        }
        
        /* Page break handling */
        @media print {
            .stickers-grid {
                page-break-inside: avoid;
            }
            
            .sticker {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
        
        /* Ensure 3 stickers per row on standard paper */
        @page {
            size: A4;
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="stickers-grid">
        @foreach ($stickers as $stickerData)
            <div class="sticker">
                @if ($stickerData['error'])
                    <div class="qr-code-container">
                        <div class="error-icon">
                            ⚠
                        </div>
                        <div class="error-message">
                            Error generating QR code: {{ $stickerData['error'] }}
                        </div>
                    </div>
                @else
                    <div class="qr-code-container">
                        <img src="{{ $stickerData['qrCodeDataUrl'] }}" 
                             alt="QR Code for {{ $stickerData['device']->asset_code }}"
                             class="qr-code">
                    </div>
                @endif

                <div>
                    <div class="asset-code">
                        {{ $stickerData['device']->asset_code }}
                    </div>
                    
                    <div class="device-info">
                        <div class="info-row">
                            <span class="info-label">Asset Code:</span>
                            <span class="info-value">{{ $stickerData['device']->asset_code }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Category:</span>
                            <span class="info-value">{{ $stickerData['device']->bribox->category->category_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Device:</span>
                            <span class="info-value">{{ $stickerData['device']->brand }} {{ $stickerData['device']->brand_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">SN:</span>
                            <span class="info-value">{{ $stickerData['device']->serial_number }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
