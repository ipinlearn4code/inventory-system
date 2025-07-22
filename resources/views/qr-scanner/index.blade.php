<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Code Scanner - Briven Inventory</title>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .header h1 {
            color: #3b82f6;
            margin: 0 0 10px 0;
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
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .back-button:hover {
            background: #4b5563;
        }
        
        .scanner-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        #qr-reader {
            width: 100%;
            margin: 20px 0;
        }
        
        .result-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none;
        }
        
        .result-container.show {
            display: block;
        }
        
        .result-container.error {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
        }
        
        .result-container.success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
        }
        
        .device-info {
            margin-top: 15px;
        }
        
        .device-info h3 {
            margin: 0 0 10px 0;
            color: #1f2937;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        
        .info-item {
            background: #f9fafb;
            padding: 10px;
            border-radius: 4px;
        }
        
        .info-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
        }
        
        .info-value {
            color: #1f2937;
            font-size: 14px;
        }
        
        .assignment-info {
            margin-top: 15px;
            padding: 15px;
            background: #f0f9ff;
            border-radius: 4px;
            border-left: 4px solid #3b82f6;
        }
        
        .assignment-info h4 {
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        
        .specs-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .specs-list li {
            padding: 4px 0;
            font-size: 12px;
            color: #6b7280;
        }
        
        .condition-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .condition-baik {
            background: #d1fae5;
            color: #065f46;
        }
        
        .condition-rusak {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .condition-perlu-pengecekan {
            background: #fef3c7;
            color: #92400e;
        }
        
        .scan-again-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .scan-again-btn:hover {
            background: #2563eb;
        }
        
        .instructions {
            background: #f0f9ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }
        
        .instructions h3 {
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin: 5px 0;
            color: #374151;
        }
        
        @media (max-width: 640px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="javascript:history.back()" class="back-button">← Back</a>
            <h1>🔍 QR Code Scanner</h1>
            <p>Scan QR codes on asset stickers to view device information</p>
        </div>
        
        <div class="instructions">
            <h3>How to use:</h3>
            <ul>
                <li>Allow camera access when prompted</li>
                <li>Point your camera at the QR code on the device sticker</li>
                <li>Wait for the scanner to automatically detect the code</li>
                <li>View device information instantly</li>
            </ul>
        </div>
        
        <div class="scanner-container">
            <h3>Scanner</h3>
            <div id="qr-reader"></div>
            <div id="scanner-status">Starting camera...</div>
        </div>
        
        <div id="result-container" class="result-container">
            <div id="result-content"></div>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let isScanning = false;
        
        function startScanner() {
            const qrCodeReader = document.getElementById('qr-reader');
            const statusDiv = document.getElementById('scanner-status');
            
            html5QrCode = new Html5Qrcode("qr-reader");
            
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    statusDiv.textContent = "Camera ready - point at QR code";
                    
                    // Start scanning
                    html5QrCode.start(
                        { facingMode: "environment" }, // Use back camera
                        {
                            fps: 10,
                            qrbox: { width: 250, height: 250 }
                        },
                        onScanSuccess,
                        onScanFailure
                    ).catch(err => {
                        console.error('Error starting scanner:', err);
                        statusDiv.textContent = "Error starting camera: " + err;
                    });
                } else {
                    statusDiv.textContent = "No cameras found";
                }
            }).catch(err => {
                console.error('Error getting cameras:', err);
                statusDiv.textContent = "Error accessing camera: " + err;
            });
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            if (isScanning) return; // Prevent multiple simultaneous scans
            
            isScanning = true;
            console.log('QR Code detected:', decodedText);
            
            // Stop scanning temporarily
            html5QrCode.pause();
            
            // Send to backend
            fetch('/qr-scanner/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    qr_data: decodedText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showDeviceInfo(data.device);
                } else {
                    showError(data.error || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error occurred');
            })
            .finally(() => {
                setTimeout(() => {
                    isScanning = false;
                    html5QrCode.resume();
                }, 2000);
            });
        }
        
        function onScanFailure(error) {
            // Silent failure - this is normal when no QR code is detected
        }
        
        function showDeviceInfo(device) {
            const resultContainer = document.getElementById('result-container');
            const resultContent = document.getElementById('result-content');
            
            let assignmentHtml = '';
            if (device.assignment) {
                assignmentHtml = `
                    <div class="assignment-info">
                        <h4>📍 Current Assignment</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">User</div>
                                <div class="info-value">${device.assignment.user_name}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Branch</div>
                                <div class="info-value">${device.assignment.branch_name}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Assigned Date</div>
                                <div class="info-value">${device.assignment.assigned_date}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Always show status regardless of assignment
            statusHtml += `
                <div class="assignment-info">
                    <h4>Device Status</h4>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">${device.status}</div>
                    </div>
                </div>
            `;
            }
            
            let specsHtml = '';
            if (device.specs.some(spec => spec)) {
                specsHtml = `
                    <div class="info-item">
                        <div class="info-label">Specifications</div>
                        <ul class="specs-list">
                            ${device.specs.filter(spec => spec).map(spec => `<li>${spec}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            resultContent.innerHTML = `
                <div class="device-info">
                    <h3>✅ Device Found</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Asset Code</div>
                            <div class="info-value">${device.asset_code}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Brand</div>
                            <div class="info-value">${device.brand}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Model/Series</div>
                            <div class="info-value">${device.brand_name}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Serial Number</div>
                            <div class="info-value">${device.serial_number}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Condition</div>
                            <div class="info-value">
                                <span class="condition-badge condition-${device.condition.toLowerCase().replace(/\s+/g, '-')}">${device.condition}</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Category</div>
                            <div class="info-value">${device.category}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Type</div>
                            <div class="info-value">${device.type}</div>
                        </div>
                        ${specsHtml}
                    </div>
                    ${assignmentHtml}
                </div>
                <button class="scan-again-btn" onclick="clearResult()">Scan Another QR Code</button>
            `;
            
            resultContainer.className = 'result-container success show';
        }
        
        function showError(message) {
            const resultContainer = document.getElementById('result-container');
            const resultContent = document.getElementById('result-content');
            
            resultContent.innerHTML = `
                <h3>❌ Error</h3>
                <p>${message}</p>
                <button class="scan-again-btn" onclick="clearResult()">Try Again</button>
            `;
            
            resultContainer.className = 'result-container error show';
        }
        
        function clearResult() {
            const resultContainer = document.getElementById('result-container');
            resultContainer.className = 'result-container';
        }
        
        // Start scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startScanner();
        });
        
        // Cleanup when page unloads
        window.addEventListener('beforeunload', function() {
            if (html5QrCode) {
                html5QrCode.stop();
            }
        });
    </script>
</body>
</html> -->
