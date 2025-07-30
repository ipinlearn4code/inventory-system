<x-filament-widgets::widget>
    <x-filament::section>
        @if (filled($heading = $this->getHeading()))
            <x-slot name="heading">
                {{ $heading }}
            </x-slot>
        @endif

        <div class="relative">
            <canvas 
                id="{{ $chartId ?? 'chart-' . rand() }}"
                class="w-full"
                style="max-height: 300px;"
                x-data="{
                    chart: null,
                    chartId: '{{ $chartId ?? 'chart-' . rand() }}',
                    chartData: @js($chartData ?? []),
                    chartType: '{{ $chartType ?? 'line' }}',
                    chartOptions: @js($chartOptions ?? [])
                }"
                x-init="
                    const initChart = async () => {
                        // Wait for global asset manager to load Chart.js
                        if (window.GlobalAssetManager) {
                            await window.GlobalAssetManager.loadChartJS();
                        }
                        
                        if (typeof Chart !== 'undefined' && !chart) {
                            const ctx = document.getElementById(chartId);
                            if (ctx) {
                                chart = new Chart(ctx, {
                                    type: chartType,
                                    data: chartData,
                                    options: chartOptions
                                });
                            }
                        }
                    };
                    
                    // Listen for global assets ready event
                    document.addEventListener('globalAssetsReady', initChart);
                    
                    // Try immediate initialization if assets are already loaded
                    initChart();
                "
                x-destroy="
                    if (chart) {
                        chart.destroy();
                        chart = null;
                    }
                "
            ></canvas>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
