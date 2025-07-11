<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-700 rounded-lg flex items-center justify-center shadow-sm">
                    <x-filament::icon icon="heroicon-o-shield-check" class="w-5 h-5 text-white" />
                </div>
                <span class="text-lg font-bold text-primary-800 dark:text-primary-200">Permission Matrix</span>
            </div>
        </x-slot>
        
        <x-slot name="description">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Overview of all roles and their assigned permissions. SuperAdmins can manage roles and permissions directly from here.
            </p>
        </x-slot>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <th class="text-left py-3 px-4 font-semibold text-gray-800 dark:text-gray-200 w-1/3">
                            Permission
                        </th>
                        @foreach($this->getRoles() as $role)
                            <th class="text-center py-3 px-4 font-semibold text-gray-800 dark:text-gray-200">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                    {{ $role === 'superadmin' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' : '' }}
                                    {{ $role === 'admin' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' : '' }}
                                    {{ $role === 'user' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' : '' }}
                                    {{ !in_array($role, ['superadmin', 'admin', 'user']) ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200' : '' }}
                                ">
                                    {{ ucfirst($role) }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($this->getPermissionMatrix() as $permissionData)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
                            <td class="py-3 px-4 font-medium text-gray-900 dark:text-gray-100">
                                {{ $permissionData['permission'] }}
                            </td>
                            @foreach($permissionData['roles'] as $roleName => $hasPermission)
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                                        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionData['permission'])->first();
                                    @endphp
                                    @if($role && $permission)
                                        @livewire('permission-toggle', [
                                            'roleId' => $role->id,
                                            'permissionId' => $permission->id,
                                            'hasPermission' => $hasPermission
                                        ])
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <button 
               onclick="window.location.reload()" 
               class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                <x-heroicon-s-arrow-path class="h-4 w-4" />
                Refresh Matrix
            </button>
            
            <a href="{{ route('filament.admin.pages.dashboard') }}" 
               class="inline-flex items-center gap-2 rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                <x-heroicon-s-home class="h-4 w-4" />
                Back to Dashboard
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
