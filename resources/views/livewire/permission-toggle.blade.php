<button 
    wire:click="togglePermission" 
    class="flex items-center justify-center w-6 h-6 rounded-full transition-colors duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1"
    title="{{ $hasPermission ? 'Click to revoke' : 'Click to grant' }} permission"
>
    @if($hasPermission)
        <x-heroicon-s-check-circle class="h-6 w-6 text-green-500 hover:text-green-600" />
    @else
        <x-heroicon-s-x-circle class="h-6 w-6 text-gray-300 hover:text-gray-400 dark:text-gray-600 dark:hover:text-gray-500" />
    @endif
</button>
