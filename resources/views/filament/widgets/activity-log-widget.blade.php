<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-filament::icon icon="heroicon-m-clock" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <span class="text-lg font-semibold text-blue-900 dark:text-blue-100">Log Aktivitas Sistem</span>
            </div>
        </x-slot>
        
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @foreach($this->getRecentActivities() as $activity)
                <div class="flex items-start space-x-3 p-4 rounded-xl border-2 {{ 
                    $activity['color'] === 'success' ? 'border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 dark:border-green-700 dark:from-green-900 dark:to-emerald-900' : 
                    ($activity['color'] === 'info' ? 'border-blue-200 bg-gradient-to-r from-blue-50 to-cyan-50 dark:border-blue-700 dark:from-blue-900 dark:to-cyan-900' : 
                    ($activity['color'] === 'warning' ? 'border-yellow-200 bg-gradient-to-r from-yellow-50 to-amber-50 dark:border-yellow-700 dark:from-yellow-900 dark:to-amber-900' : 
                    'border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50 dark:border-gray-700 dark:from-gray-900 dark:to-slate-900'))
                }} transition-all duration-200 hover:shadow-md">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ 
                            $activity['color'] === 'success' ? 'bg-green-200 dark:bg-green-800' : 
                            ($activity['color'] === 'info' ? 'bg-blue-200 dark:bg-blue-800' : 
                            ($activity['color'] === 'warning' ? 'bg-yellow-200 dark:bg-yellow-800' : 'bg-gray-200 dark:bg-gray-800'))
                        }}">
                            <x-filament::icon 
                                :icon="$activity['icon']" 
                                class="w-5 h-5 {{ 
                                    $activity['color'] === 'success' ? 'text-green-700 dark:text-green-300' : 
                                    ($activity['color'] === 'info' ? 'text-blue-700 dark:text-blue-300' : 
                                    ($activity['color'] === 'warning' ? 'text-yellow-700 dark:text-yellow-300' : 'text-gray-700 dark:text-gray-300'))
                                }}"
                            />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold {{ 
                            $activity['color'] === 'success' ? 'text-green-900 dark:text-green-100' : 
                            ($activity['color'] === 'info' ? 'text-blue-900 dark:text-blue-100' : 
                            ($activity['color'] === 'warning' ? 'text-yellow-900 dark:text-yellow-100' : 'text-gray-900 dark:text-gray-100'))
                        }}">
                            {{ $activity['title'] }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $activity['description'] }}
                        </p>
                        <div class="flex items-center mt-2 text-xs text-gray-500">
                            <span class="font-medium">{{ $activity['user'] }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if(empty($this->getRecentActivities()))
                <div class="text-center py-12 text-gray-500">
                    <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mx-auto mb-4">
                        <x-filament::icon icon="heroicon-m-clock" class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                    <p class="text-lg font-medium text-gray-600 dark:text-gray-400">Belum ada aktivitas terbaru</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">Aktivitas sistem akan ditampilkan di sini</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
