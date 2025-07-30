// Global Asset Manager - Prevents duplicate script loading
window.GlobalAssetManager = {
    loadedScripts: new Set(),
    loadingPromises: new Map(),
    
    /**
     * Load a script only once
     */
    loadScript(src, id = null) {
        const scriptId = id || src;
        
        // If already loaded, return resolved promise
        if (this.loadedScripts.has(scriptId)) {
            return Promise.resolve();
        }
        
        // If currently loading, return existing promise
        if (this.loadingPromises.has(scriptId)) {
            return this.loadingPromises.get(scriptId);
        }
        
        // Create new loading promise
        const promise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            if (id) script.id = id;
            
            script.onload = () => {
                this.loadedScripts.add(scriptId);
                this.loadingPromises.delete(scriptId);
                resolve();
            };
            
            script.onerror = () => {
                this.loadingPromises.delete(scriptId);
                reject(new Error(`Failed to load script: ${src}`));
            };
            
            document.head.appendChild(script);
        });
        
        this.loadingPromises.set(scriptId, promise);
        return promise;
    },
    
    /**
     * Load Chart.js if not already loaded
     */
    async loadChartJS() {
        if (typeof Chart === 'undefined') {
            await this.loadScript('https://cdn.jsdelivr.net/npm/chart.js', 'chart-js');
        }
        return Promise.resolve();
    },
    
    /**
     * Load HTML5 QR Code scanner if not already loaded
     */
    async loadQRScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            await this.loadScript('https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js', 'html5-qrcode');
        }
        return Promise.resolve();
    },
    
    /**
     * Initialize all required assets
     */
    async initializeAll() {
        const promises = [
            this.loadChartJS(),
            this.loadQRScanner()
        ];
        
        await Promise.all(promises);
        
        // Dispatch custom event when all assets are loaded
        document.dispatchEvent(new CustomEvent('globalAssetsReady'));
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.GlobalAssetManager.initializeAll();
});
