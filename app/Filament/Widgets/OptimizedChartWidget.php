<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

abstract class OptimizedChartWidget extends ChartWidget
{
    protected static string $view = 'filament.widgets.optimized-chart-widget';
    
    /**
     * Get the chart configuration.
     * Override this method in child classes instead of getData().
     */
    abstract protected function getChartData(): array;
    
    /**
     * Get chart type (line, bar, pie, doughnut, etc.)
     */
    abstract protected function getChartType(): string;
    
    /**
     * Get chart options. Override in child classes for customization.
     */
    protected function getChartOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
    
    /**
     * Final getData method that formats data for the view
     */
    final protected function getData(): array
    {
        return $this->getChartData();
    }
    
    /**
     * Final getType method
     */
    final protected function getType(): string
    {
        return $this->getChartType();
    }
    
    /**
     * Final getOptions method
     */
    final protected function getOptions(): array
    {
        return $this->getChartOptions();
    }
    
    /**
     * Get the view data for the widget
     */
    public function getViewData(): array
    {
        return [
            'chartId' => 'chart-' . static::class . '-' . $this->getId(),
            'chartData' => $this->getData(),
            'chartType' => $this->getType(),
            'chartOptions' => $this->getOptions(),
            'heading' => static::$heading,
        ];
    }
}
