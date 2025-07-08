<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center">
                    <x-heroicon-o-shield-check class="h-8 w-8 text-primary-600 mr-3" />
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                            System Permission Matrix
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage role-based permissions for the inventory control system. System-defined permissions cannot be deleted or added.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permission Matrix Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-2/5">
                                    Permission Name
                                </th>
                                @foreach($this->getRoles() as $roleKey => $roleName)
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        <div class="flex flex-col items-center space-y-1">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                {{ $roleKey === 'superadmin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                                {{ $roleKey === 'admin' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                {{ $roleKey === 'user' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            ">
                                                {{ $roleName }}
                                            </span>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getPermissionMatrix() as $permission)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $permission['name'] }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $permission['description'] }}
                                            </div>
                                        </div>
                                    </td>
                                    @foreach($permission['roles'] as $roleKey => $roleData)
                                        <td class="px-6 py-4 text-center">
                                            @if($roleData['disabled'])
                                                <!-- SuperAdmin - Always has permission, disabled -->
                                                <div class="flex items-center justify-center">
                                                    <x-heroicon-s-check-circle class="h-6 w-6 text-green-500" />
                                                    <span class="ml-1 text-xs text-gray-400">(Always)</span>
                                                </div>
                                            @else
                                                <!-- Toggleable checkbox for other roles -->
                                                <button 
                                                    wire:click="togglePermission({{ $roleData['role_id'] }}, {{ $permission['id'] }}, {{ $roleData['has_permission'] ? 'true' : 'false' }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                                                    title="{{ $roleData['has_permission'] ? 'Click to revoke permission' : 'Click to grant permission' }}"
                                                >
                                                    @if($roleData['has_permission'])
                                                        <x-heroicon-s-check-circle class="h-6 w-6 text-green-500 hover:text-green-600" />
                                                    @else
                                                        <x-heroicon-s-x-circle class="h-6 w-6 text-gray-300 hover:text-gray-400 dark:text-gray-600 dark:hover:text-gray-500" />
                                                    @endif
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Legend and Notes -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Legend</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center space-x-3">
                        <x-heroicon-s-check-circle class="h-5 w-5 text-green-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Permission Granted</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <x-heroicon-s-x-circle class="h-5 w-5 text-gray-300 dark:text-gray-600" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Permission Denied</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <x-heroicon-s-check-circle class="h-5 w-5 text-green-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">(Always) - Cannot be modified</span>
                    </div>
                </div>
                
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Notes:</h5>
                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <li>• <strong>SuperAdmin</strong> always has all permissions and cannot be modified</li>
                        <li>• <strong>System permissions</strong> are predefined and cannot be added or deleted</li>
                        <li>• <strong>Click checkboxes</strong> to toggle permissions for Admin and User roles</li>
                        <li>• Changes are applied immediately and affect all users with that role</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
