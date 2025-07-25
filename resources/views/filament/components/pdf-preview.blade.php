<div class="fi-pdf-preview-container">
    @if($previewData['hasFile'])
        <div class="space-y-3 sm:space-y-4">
            <!-- File Info Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 sm:p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $previewData['fileName'] }}">
                            {{ \Illuminate\Support\Str::limit($previewData['fileName'], 30) }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $previewData['fileSize'] ?? 'PDF Document' }}
                        </p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Preview Button -->
                    <button 
                        type="button"
                        onclick="window.open('{{ $previewData['previewUrl'] }}', '_blank')"
                        class="fi-btn-pdf-action fi-btn-pdf-primary"
                        title="Open PDF in new tab"
                    >
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span class="hidden sm:inline ml-2">Full View</span>
                    </button>
                    
                    <!-- Download Button -->
                    <a 
                        href="{{ $previewData['downloadUrl'] }}" 
                        download="{{ $previewData['fileName'] }}"
                        class="fi-btn-pdf-action fi-btn-pdf-secondary"
                        title="Download PDF"
                    >
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="hidden sm:inline ml-2">Download</span>
                    </a>
                    
                    <!-- Print Button -->
                    <button 
                        type="button"
                        onclick="printPdf('{{ $previewData['previewUrl'] }}')"
                        class="fi-btn-pdf-action fi-btn-pdf-secondary"
                        title="Print PDF"
                    >
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        <span class="hidden sm:inline ml-2">Print</span>
                    </button>
                </div>
            </div>
            
            <!-- PDF Preview Frame -->
            <div class="fi-pdf-preview-frame">
                <div class="fi-pdf-preview-aspect">
                    <iframe 
                        src="{{ $previewData['previewUrl'] }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH" 
                        class="fi-pdf-preview-iframe"
                        title="PDF Preview"
                        loading="lazy"
                    ></iframe>
                </div>
                
                <!-- Loading Indicator -->
                <!-- <div class="fi-pdf-preview-loading" id="pdf-loading">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-blue-600"></div>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Loading PDF preview...</p>
                    </div>
                </div> -->
                <!-- Loading Indicator error, didnt want to dissappear so overlaying ppreview -->
            </div>
        </div>
    @else
        <div class="fi-pdf-preview-empty">
            <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $previewData['message'] }}</h3>
            <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">Upload a PDF file to see preview</p>
        </div>
    @endif
</div>

<script>
// PDF Preview functionality
function printPdf(url) {
    const printWindow = window.open(url + '#toolbar=1&navpanes=0&scrollbar=0&view=FitH&print=true', '_blank');
    
    if (printWindow) {
        printWindow.addEventListener('load', function() {
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        });
    } else {
        window.print();
    }
}

// Initialize PDF preview
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.querySelector('.fi-pdf-preview-iframe');
    const loadingDiv = document.getElementById('pdf-loading');
    
    if (iframe && loadingDiv) {
        iframe.addEventListener('load', function() {
            loadingDiv.style.display = 'none';
        });
        
        // Hide loading after timeout
        setTimeout(() => {
            if (loadingDiv) {
                loadingDiv.style.display = 'none';
            }
        }, 5000);
    }
});
</script>


