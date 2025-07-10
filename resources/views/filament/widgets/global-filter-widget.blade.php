<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <x-filament::icon icon="heroicon-m-funnel" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <span class="text-lg font-semibold text-blue-900 dark:text-blue-100">Filter Global Dashboard</span>
            </div>
        </x-slot>
        
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950 dark:to-indigo-950 p-6 rounded-xl border-2 border-blue-200 dark:border-blue-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-blue-900 dark:text-blue-100">
                        🏢 Kantor Cabang Utama
                    </label>
                    <select 
                        wire:model.live="mainBranchId"
                        class="block w-full rounded-xl border-2 border-blue-300 dark:border-blue-600 bg-white dark:bg-blue-900 text-blue-900 dark:text-blue-100 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                    >
                        @foreach($this->getMainBranchOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-blue-900 dark:text-blue-100">
                        🏪 Kantor Cabang
                    </label>
                    <select 
                        wire:model.live="branchId"
                        class="block w-full rounded-xl border-2 border-blue-300 dark:border-blue-600 bg-white dark:bg-blue-900 text-blue-900 dark:text-blue-100 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-25 transition-all duration-200"
                    >
                        @foreach($this->getBranchOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-blue-100 dark:bg-blue-800 rounded-lg">
                <p class="text-xs text-blue-700 dark:text-blue-300 flex items-center">
                    <x-filament::icon icon="heroicon-m-information-circle" class="w-4 h-4 mr-1" />
                    Filter ini akan mempengaruhi semua widget dashboard di bawah
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
