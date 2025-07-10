<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-filament::icon icon="heroicon-m-bolt" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <span class="text-lg font-semibold text-blue-900 dark:text-blue-100">Aksi Cepat</span>
            </div>
        </x-slot>
        
        <div class="grid gap-4">
            @foreach($this->getQuickActions() as $action)
                <a 
                    href="{{ $action['url'] }}" 
                    @if(isset($action['onclick']))
                        onclick="{{ $action['onclick'] }}; return false;"
                    @endif
                    class="flex items-center p-4 rounded-xl border-2 {{ 
                        $action['color'] === 'primary' ? 'border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 dark:border-blue-700 dark:from-blue-900 dark:to-blue-800' : 
                        ($action['color'] === 'info' ? 'border-cyan-200 bg-gradient-to-r from-cyan-50 to-cyan-100 hover:from-cyan-100 hover:to-cyan-200 dark:border-cyan-700 dark:from-cyan-900 dark:to-cyan-800' : 
                        ($action['color'] === 'warning' ? 'border-yellow-200 bg-gradient-to-r from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 dark:border-yellow-700 dark:from-yellow-900 dark:to-yellow-800' : 
                        ($action['color'] === 'success' ? 'border-green-200 bg-gradient-to-r from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 dark:border-green-700 dark:from-green-900 dark:to-green-800' : 
                        'border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-gray-100 hover:to-gray-200 dark:border-gray-700 dark:from-gray-900 dark:to-gray-800')))
                    }} transition-all duration-200 group shadow-sm hover:shadow-md"
                >
                    <div class="flex-shrink-0 mr-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ 
                            $action['color'] === 'primary' ? 'bg-blue-200 dark:bg-blue-800' : 
                            ($action['color'] === 'info' ? 'bg-cyan-200 dark:bg-cyan-800' : 
                            ($action['color'] === 'warning' ? 'bg-yellow-200 dark:bg-yellow-800' : 
                            ($action['color'] === 'success' ? 'bg-green-200 dark:bg-green-800' : 'bg-gray-200 dark:bg-gray-800')))
                        }}">
                            <x-filament::icon 
                                :icon="$action['icon']" 
                                class="w-6 h-6 {{ 
                                    $action['color'] === 'primary' ? 'text-blue-700 dark:text-blue-300' : 
                                    ($action['color'] === 'info' ? 'text-cyan-700 dark:text-cyan-300' : 
                                    ($action['color'] === 'warning' ? 'text-yellow-700 dark:text-yellow-300' : 
                                    ($action['color'] === 'success' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-gray-300')))
                                }}"
                            />
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold {{ 
                            $action['color'] === 'primary' ? 'text-blue-900 dark:text-blue-100' : 
                            ($action['color'] === 'info' ? 'text-cyan-900 dark:text-cyan-100' : 
                            ($action['color'] === 'warning' ? 'text-yellow-900 dark:text-yellow-100' : 
                            ($action['color'] === 'success' ? 'text-green-900 dark:text-green-100' : 'text-gray-900 dark:text-gray-100')))
                        }}">
                            {{ $action['label'] }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            {{ $action['description'] }}
                        </p>
                    </div>
                    
                    <div class="flex-shrink-0 ml-3">
                        <x-filament::icon 
                            icon="heroicon-m-arrow-right" 
                            class="w-5 h-5 {{ 
                                $action['color'] === 'primary' ? 'text-blue-500 group-hover:text-blue-700' : 
                                ($action['color'] === 'info' ? 'text-cyan-500 group-hover:text-cyan-700' : 
                                ($action['color'] === 'warning' ? 'text-yellow-500 group-hover:text-yellow-700' : 
                                ($action['color'] === 'success' ? 'text-green-500 group-hover:text-green-700' : 'text-gray-500 group-hover:text-gray-700')))
                            }} transition-colors duration-200"
                        />
                    </div>
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
