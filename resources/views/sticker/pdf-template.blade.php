<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Stiker QR Code</title>
    <style>
        @page {
            margin: 1cm;
        }

        body {
            margin: 0;
            padding: 1cm;
            background: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        .stickers-grid {
            display: grid;
            grid-template-columns: repeat(3, 4cm);
            gap: 1cm;
            justify-content: center;
        }

        .sticker {
            width: 4cm;
            height: 6cm;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.4cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: start;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }

        .qr-code-container {
            width: 100%;
            display: flex;
            align-items: center;
            margin-bottom: 0.3cm;
        }

        .qr-code {
            width: 2.8cm;
            height: 2.8cm;
            object-fit: contain;
        }

        .asset-code {
            font-weight: bold;
            text-align: center;
            margin-bottom: 0.3cm;
            font-size: 12pt;
        }

        .device-info {
            font-size: 9pt;
            width: 100%;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.1cm;
        }

        .info-label {
            font-weight: 600;
            color: #333;
        }

        .info-value {
            text-align: right;
            color: #444;
        }

        .error-icon {
            font-size: 24pt;
            color: red;
            text-align: center;
        }

        .error-message {
            font-size: 9pt;
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="stickers-grid">
        @foreach ($stickers as $stickerData)
            <div class="sticker">
                @if ($stickerData['error'])
                    <div class="qr-code-container">
                        <div class="error-icon">⚠</div>
                    </div>
                    <div class="error-message">
                        Error generating QR code: {{ $stickerData['error'] }}
                    </div>
                @else
                    <div class="text-center">
                        <img src="{{ $stickerData['qrCodeDataUrl'] }}"
                             alt="QR Code for {{ $stickerData['device']->asset_code }}"
                             class="qr-code">
                    </div>
                @endif

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
                        <span class="info-value">
                            {{ $stickerData['device']->bribox->category->category_name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Device:</span>
                        <span class="info-value">
                            {{ $stickerData['device']->brand }} {{ $stickerData['device']->brand_name }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">SN:</span>
                        <span class="info-value">{{ $stickerData['device']->serial_number }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
