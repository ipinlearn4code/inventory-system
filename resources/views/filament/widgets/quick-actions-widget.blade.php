<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            ⚡ Akses Cepat
        </x-slot>
        
        <div class="grid gap-3">
            @foreach($this->getQuickActions() as $action)
                <a 
                    href="{{ $action['url'] }}" 
                    @if(isset($action['onclick']))
                        onclick="{{ $action['onclick'] }}; return false;"
                    @endif
                    class="flex items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                >
                    <div class="flex-shrink-0 mr-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ 
                            $action['color'] === 'success' ? 'bg-green-100 dark:bg-green-900' : 
                            ($action['color'] === 'info' ? 'bg-blue-100 dark:bg-blue-900' : 
                            ($action['color'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900' : 
                            ($action['color'] === 'danger' ? 'bg-red-100 dark:bg-red-900' : 'bg-gray-100 dark:bg-gray-900')))
                        }}">
                            <x-filament::icon 
                                :icon="$action['icon']" 
                                class="w-5 h-5 {{ 
                                    $action['color'] === 'success' ? 'text-green-600 dark:text-green-400' : 
                                    ($action['color'] === 'info' ? 'text-blue-600 dark:text-blue-400' : 
                                    ($action['color'] === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 
                                    ($action['color'] === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400')))
                                }}"
                            />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                            {{ $action['label'] }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $action['description'] }}
                        </p>
                    </div>
                    
                    <div class="flex-shrink-0 ml-2">
                        <x-filament::icon 
                            icon="heroicon-m-arrow-right" 
                            class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors"
                        />
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
