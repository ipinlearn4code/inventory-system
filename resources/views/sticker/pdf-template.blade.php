<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Stiker QR Code</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 1cm;
    }

    .sticker-grid {
      display: grid;
      grid-template-columns: repeat(4, 4cm);
      gap: 0.5cm;
      justify-content: center;
    }

    .sticker-item {
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
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
      page-break-inside: avoid;
    }

    .sticker-qr-container {
      width: 100%;
      display: flex;
      justify-content: center;
      margin-bottom: 0.3cm;
    }

    .sticker-qr {
      width: 2cm;
      height: 2cm;
      object-fit: contain;
    }

    .sticker-code {
      font-weight: bold;
      text-align: center;
      margin-bottom: 0.3cm;
      font-size: 10pt;
    }

    .sticker-info {
      font-size: 8pt;
      width: 100%;
    }

    .sticker-info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.1cm;
    }

    .sticker-label {
      font-weight: 600;
      color: #333;
    }

    .sticker-value {
      text-align: right;
      color: #333;
      white-space: nowrap;
      /* overflow: hidden; */
      text-overflow: ellipsis;
      max-width: 100%;
    }

    .sticker-error-icon {
      font-size: 16pt;
      color: red;
    }

    .sticker-error-message {
      font-size: 8pt;
      color: red;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="sticker-grid">
    @foreach ($stickers as $stickerData)
      <div class="sticker-item">
        @if ($stickerData['error'])
          <div class="sticker-qr-container">
            <div class="sticker-error-icon">⚠</div>
          </div>
          <div class="sticker-error-message">
            Error generating QR code: {{ $stickerData['error'] }}
          </div>
        @else
          <div class="sticker-qr-container">
            <img src="{{ $stickerData['qrCodeDataUrl'] }}"
              alt="QR Code for {{ $stickerData['device']->asset_code }}" class="sticker-qr">
          </div>
        @endif

        <div class="sticker-code">
          {{ $stickerData['device']->asset_code }}
        </div>

        <div class="sticker-info">
          <div class="sticker-info-row">
            <span class="sticker-label">Asset Code:</span>
            <span class="sticker-value">{{ $stickerData['device']->asset_code }}</span>
          </div>
          <div class="sticker-info-row">
            <span class="sticker-label">Category:</span>
            <span class="sticker-value">
              {{ $stickerData['device']->bribox->category->category_name ?? 'N/A' }}
            </span>
          </div>
          <div class="sticker-info-row">
            <span class="sticker-label">Device:</span>
            <span class="sticker-value">
              {{ $stickerData['device']->brand }} {{ $stickerData['device']->brand_name }}
            </span>
          </div>
          <div class="sticker-info-row">
            <span class="sticker-label">SN:</span>
            <span class="sticker-value">{{ $stickerData['device']->serial_number }}</span>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</body>
</html>
