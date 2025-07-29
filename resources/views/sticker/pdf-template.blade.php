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
            padding: 0cm;
            /* background: #ffffffff; */
            font-family: Arial, sans-serif;
        }

        table.sticker-table {
            border-collapse: separate;
            /* border-spacing: 0.5cm 0.5cm; */
            width: 100%;
        }

        td.sticker-cell {
            width: 3.5cm;
            height: 5.9cm;
            background: white;
            border: 1px solid black;
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
            width: 70%;
            height: auto;
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

        .sticker-info-table {
            width: 100%;
            /* border-collapse: collapse; */
        }

        .sticker-info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            /* atau 'center' jika mau rata tengah vertikal */
            font-size: 8pt;
            margin-bottom: 0.1cm;
            width: 100%;
        }

        .sticker-pdf-label {
            font-size: 6pt;
            font-weight: bold;
            text-align: left;
            white-space: nowrap;
            width: fit-content;
        }

        .sticker-pdf-value {
            display: inline-block;
            width: 2cm;
            /* atau 100px dsb */
            overflow: hidden;
            /* text-overflow: ellipsis; */
            white-space: nowrap;
            text-align: right;
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



        /* Ukuran font */
        /* .text-xs { font-size: 7pt; }
        .text-sm { font-size: 8pt; }
        .text-md { font-size: 10pt; }
        .text-lg { font-size: 12pt; } */

        /* Warna teks */
        .text-dark {
            color: #333;
        }

        .text-gray {
            color: #777;
        }

        .text-red {
            color: red;
        }

        /* Gaya huruf */
        .text-bold {
            font-weight: bold;
        }

        .text-normal {
            font-weight: normal;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        /* Perataan */
        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        /* Display */
        .inline {
            display: inline;
        }

        .block {
            display: block;
        }

        /* Margin dan spacing dasar */
        .mb-1 {
            margin-bottom: 0.1cm;
        }

        .mb-2 {
            margin-bottom: 0.2cm;
        }

        .mt-1 {
            margin-top: 0.1cm;
        }

        .p-1 {
            padding: 0.1cm;
        }

        .p-2 {
            padding: 0.2cm;
        }
    </style>

</head>

<body>
    <table class="sticker-table">
        @php
            $columnsPerRow = 4; // jumlah kolom per baris
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
                                <table class="sticker-info-table">
                                    <tr>
                                        <td class="sticker-pdf-label">Asset Code:</td>
                                        <td class="sticker-pdf-value">{{ $stickerData['device']->asset_code }}</td>
                                    </tr>
                                    <tr>
                                        <td class="sticker-pdf-label">Category:</td>
                                        <td class="sticker-pdf-value">
                                            {{ $stickerData['device']->bribox->category->category_name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="sticker-pdf-label">Device:</td>
                                        <td class="sticker-pdf-value">
                                            {{ $stickerData['device']->brand }} {{ $stickerData['device']->brand_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="sticker-pdf-label">SN:</td>
                                        <td class="sticker-pdf-value">
                                            {{ $stickerData['device']->serial_number }}
                                        </td>
                                    </tr>
                                </table>
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