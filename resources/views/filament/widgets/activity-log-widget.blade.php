<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-700 rounded-lg flex items-center justify-center shadow-sm">
                    <x-filament::icon icon="heroicon-m-clock" class="w-5 h-5 text-white" />
                </div>
                <span class="text-lg font-bold text-primary-800 dark:text-primary-200">Inventory System Activity Log</span>
            </div>
        </x-slot>
        
        <div class="space-y-3 max-h-96 overflow-y-auto p-4 bg-white rounded-lg border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
            @foreach($this->getRecentActivities() as $activity)
                <div class="activity-item flex items-start p-3 rounded-lg {{ 
                    $activity['color'] === 'success' ? 'border-l-success-600' : 
                    ($activity['color'] === 'info' ? 'border-l-primary-600' : 
                    ($activity['color'] === 'warning' ? 'border-l-warning-600' : 'border-l-gray-500'))
                }}">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ 
                            $activity['color'] === 'success' ? 'bg-success-600 text-white' : 
                            ($activity['color'] === 'info' ? 'bg-primary-600 text-white' : 
                            ($activity['color'] === 'warning' ? 'bg-warning-600 text-white' : 'bg-gray-600 text-white'))
                        }}">
                            <x-filament::icon 
                                :icon="$activity['icon']" 
                                class="w-5 h-5"
                            />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0 ml-3">
                        <p class="text-sm font-bold {{ 
                            $activity['color'] === 'success' ? 'text-success-900 dark:text-success-100' : 
                            ($activity['color'] === 'info' ? 'text-primary-900 dark:text-primary-100' : 
                            ($activity['color'] === 'warning' ? 'text-warning-900 dark:text-warning-100' : 'text-gray-900 dark:text-gray-100'))
                        }}">
                            {{ $activity['title'] }}
                        </p>
                        <p class="text-xs text-gray-700 dark:text-gray-300 mt-1">
                            {{ $activity['description'] }}
                        </p>
                        <div class="flex items-center mt-2 text-xs text-gray-600 dark:text-gray-400">
                            <span class="font-medium bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $activity['user'] }}</span>
                            <span class="mx-2 text-gray-400">•</span>
                            <span>{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if(empty($this->getRecentActivities()))
                <div class="text-center py-12 text-gray-500">
                    <div class="w-16 h-16 bg-primary-100 dark:bg-primary-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-filament::icon icon="heroicon-m-clock" class="w-8 h-8 text-primary-600 dark:text-primary-300" />
                    </div>
                    <p class="text-lg font-bold text-gray-700 dark:text-gray-300">No recent activity</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">BRI system activities will be displayed here</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
