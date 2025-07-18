<div class="pdf-preview-container">
    @if($previewData['hasFile'])
        <div class="space-y-4">
            <!-- File Info Header -->
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $previewData['fileName'] }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $previewData['fileSize'] ?? 'PDF Document' }}
                        </p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                    <!-- Preview Button -->
                    <button 
                        type="button"
                        onclick="window.open('{{ $previewData['previewUrl'] }}', '_blank')"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        title="Open PDF in new tab"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View
                    </button>
                    
                    <!-- Download Button -->
                    <a 
                        href="{{ $previewData['downloadUrl'] }}" 
                        download="{{ $previewData['fileName'] }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        title="Download PDF"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download
                    </a>
                    
                    <!-- Print Button -->
                    <button 
                        type="button"
                        onclick="printPdf('{{ $previewData['previewUrl'] }}')"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        title="Print PDF"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>
            
            <!-- PDF Preview Frame -->
            <div class="relative bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                <div class="aspect-[4/3] relative">
                    <iframe 
                        src="{{ $previewData['previewUrl'] }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" 
                        class="absolute inset-0 w-full h-full border-0"
                        title="PDF Preview"
                        loading="lazy"
                    ></iframe>
                </div>
                
                <!-- Fallback for iframe loading -->
                <div class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700" id="pdf-loading">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Loading PDF preview...</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $previewData['message'] }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload a PDF file to see preview</p>
        </div>
    @endif
</div>

<script>
// Function to print PDF
function printPdf(url) {
    const printWindow = window.open(url + '#toolbar=1&navpanes=0&scrollbar=0&view=FitH&print=true', '_blank');
    
    if (printWindow) {
        printWindow.addEventListener('load', function() {
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        });
    } else {
        // Fallback: direct print
        window.print();
    }
}

// Hide loading overlay when iframe loads
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.querySelector('iframe[title="PDF Preview"]');
    const loadingDiv = document.getElementById('pdf-loading');
    
    if (iframe && loadingDiv) {
        iframe.addEventListener('load', function() {
            loadingDiv.style.display = 'none';
        });
        
        // Fallback: hide loading after 5 seconds
        setTimeout(() => {
            if (loadingDiv) {
                loadingDiv.style.display = 'none';
            }
        }, 5000);
    }
});
</script>

<style>
.pdf-preview-container {
    @apply w-full max-w-4xl mx-auto;
}

.pdf-preview-container iframe {
    border: none;
    background: white;
}

.pdf-preview-container button:hover,
.pdf-preview-container a:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
}

.pdf-preview-container button:active,
.pdf-preview-container a:active {
    transform: translateY(0);
}
</style>
