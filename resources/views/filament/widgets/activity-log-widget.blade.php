<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📖 Log Aktivitas Sistem
        </x-slot>
        
        <div class="space-y-4">
            @foreach($this->getRecentActivities() as $activity)
                <div class="flex items-start space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="flex-shrink-0">
                        <x-filament::icon 
                            :icon="$activity['icon']" 
                            class="w-5 h-5 {{ 
                                $activity['color'] === 'success' ? 'text-green-500' : 
                                ($activity['color'] === 'info' ? 'text-blue-500' : 
                                ($activity['color'] === 'warning' ? 'text-yellow-500' : 'text-gray-500'))
                            }}"
                        />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $activity['title'] }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $activity['description'] }}
                        </p>
                        <div class="flex items-center mt-1 text-xs text-gray-500">
                            <span class="font-medium">{{ $activity['user'] }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if(empty($this->getRecentActivities()))
                <div class="text-center py-8 text-gray-500">
                    <x-filament::icon icon="heroicon-m-clock" class="w-8 h-8 mx-auto mb-2 opacity-50" />
                    <p>Belum ada aktivitas terbaru</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
