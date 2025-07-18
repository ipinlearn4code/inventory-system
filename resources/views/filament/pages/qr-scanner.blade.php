<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">QR Code Scanner</h2>
                    <p class="text-sm text-gray-600">Scan QR codes on asset stickers to quickly view device information</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-medium text-blue-900 mb-2">How to use the QR Scanner:</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• Click "Open QR Scanner" to launch the scanner in a new tab</li>
                        <li>• Allow camera access when prompted</li>
                        <li>• Point your camera at the QR code on any asset sticker</li>
                        <li>• View device information instantly</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Features</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>✅ Real-time QR code scanning</li>
                            <li>✅ Device information display</li>
                            <li>✅ Assignment status</li>
                            <li>✅ Mobile-friendly interface</li>
                        </ul>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Compatible Devices</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>📱 Mobile phones</li>
                            <li>💻 Tablets</li>
                            <li>🖥️ Desktop computers with webcam</li>
                            <li>📹 Any device with camera access</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('qr-scanner.index') }}" 
                   target="_blank"
                   class="flex items-center justify-center p-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Open Scanner
                </a>
                
                <a href="{{ route('filament.admin.resources.devices.index') }}" 
                   class="flex items-center justify-center p-4 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    View Devices
                </a>
                
                <a href="{{ route('filament.admin.resources.device-assignments.index') }}" 
                   class="flex items-center justify-center p-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    View Assignments
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
