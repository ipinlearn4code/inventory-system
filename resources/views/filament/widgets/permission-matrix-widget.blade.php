<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-shield-check class="h-6 w-6" />
                Permission Matrix
            </div>
        </x-slot>
        
        <x-slot name="description">
            Overview of all roles and their assigned permissions. SuperAdmins can manage roles and permissions directly from here.
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-4 font-medium text-gray-700 dark:text-gray-300 w-1/3">
                            Permission
                        </th>
                        @foreach($this->getRoles() as $role)
                            <th class="text-center py-3 px-4 font-medium text-gray-700 dark:text-gray-300">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    {{ $role === 'superadmin' ? 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900 dark:text-red-300' : '' }}
                                    {{ $role === 'admin' ? 'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                    {{ $role === 'user' ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900 dark:text-green-300' : '' }}
                                    {{ !in_array($role, ['superadmin', 'admin', 'user']) ? 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-900 dark:text-gray-300' : '' }}
                                ">
                                    {{ ucfirst($role) }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($this->getPermissionMatrix() as $permissionData)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
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
                                        ], key: $role->id . '-' . $permission->id)
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('filament.admin.resources.role-management.index') }}" 
               class="inline-flex items-center gap-2 rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                <x-heroicon-s-shield-check class="h-4 w-4" />
                Manage Roles
            </a>
            
            <a href="{{ route('filament.admin.resources.permission-management.index') }}" 
               class="inline-flex items-center gap-2 rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
                <x-heroicon-s-key class="h-4 w-4" />
                Manage Permissions
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
