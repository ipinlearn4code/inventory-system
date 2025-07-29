<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Stiker QR Code</title>
    <style>
        @page {
            size: A4 portrait;
        }

        body {
            margin: 0;
            padding: 1cm;
            background: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        table.sticker-table {
            border-collapse: separate;
            border-spacing: 0.5cm 0.5cm;
            width: 100%;
        }

        td.sticker-cell {
            width: 4cm;
            height: 6cm;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.4cm;
            vertical-align: top;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .sticker-qr-container {
            text-align: center;
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
            text-align: justify;
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
            /* text-align: right; */
            color: #333;
            /* white-space: nowrap; */
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .sticker-error-icon {
            font-size: 16pt;
            color: red;
            text-align: center;
        }

        .sticker-error-message {
            font-size: 8pt;
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>
    <table class="sticker-table">
        @php
            $columnsPerRow = 3; // jumlah kolom per baris
            $total = count($stickers);
        @endphp

        @for ($i = 0; $i < $total; $i += $columnsPerRow)
            <tr>
                @for ($j = 0; $j < $columnsPerRow; $j++)
                    @php
                        $index = $i + $j;
                    @endphp
                    @if ($index < $total)
                        @php $stickerData = $stickers[$index]; @endphp
                        <td class="sticker-cell">
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
                        </td>
                    @else
                        <td class="sticker-cell"></td> <!-- Kosongkan jika tidak ada data -->
                    @endif
                @endfor
            </tr>
        @endfor
    </table>
</body>

</html>
