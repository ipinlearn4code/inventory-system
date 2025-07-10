<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center shadow-sm">
                    <x-filament::icon icon="heroicon-m-funnel" class="w-5 h-5 text-primary-600" />
                </div>
                <span class="text-lg font-bold text-primary-800 dark:text-primary-200">BRI Global Filter</span>
            </div>
        </x-slot>
        
        <div class="bg-white dark:bg-gray-800 p-5 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center space-x-2">
                        <x-filament::icon icon="heroicon-m-building-office-2" class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <span>Main Branch Office</span>
                    </label>
                    <select 
                        wire:model.live="mainBranchId"
                        class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 py-2 px-3"
                    >
                        @foreach($this->getMainBranchOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 flex items-center space-x-2">
                        <x-filament::icon icon="heroicon-m-building-storefront" class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <span>Branch Office</span>
                    </label>
                    <select 
                        wire:model.live="branchId"
                        class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-600 focus:ring-1 focus:ring-primary-600 py-2 px-3"
                    >
                        @foreach($this->getBranchOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-warning-50 dark:bg-warning-900 rounded-md border border-warning-200 dark:border-warning-800">
                <p class="text-xs text-warning-700 dark:text-warning-300 flex items-center">
                    <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-4 h-4 mr-1 flex-shrink-0" />
                    <span>This filter affects all dashboard widgets below to provide accurate data for the selected branch</span>
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
