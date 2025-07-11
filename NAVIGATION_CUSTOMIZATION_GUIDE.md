# Sidebar Navigation Customization Guide

## 1. Navigation Group Configuration

The navigation groups are now defined in `app/Providers/AppServiceProvider.php` using the advanced approach:

```php
Filament::serving(
    function () {
        Filament::registerNavigationGroups([
            NavigationGroup::make()
                ->label('Dashboard')
                ->icon('heroicon-s-squares-2x2'),
            NavigationGroup::make()
                ->label('Inventory Management')
                ->collapsed()
                ->icon('heroicon-s-archive-box'),
            NavigationGroup::make()
                ->label('Device Management')
                ->collapsed()
                ->icon('heroicon-s-device-phone-mobile'),
            NavigationGroup::make()
                ->label('Master Data')
                ->collapsed()
                ->icon('heroicon-s-table-cells'),
            NavigationGroup::make()
                ->label('User Management')
                ->collapsed()
                ->icon('heroicon-s-users'),
            NavigationGroup::make()
                ->label('Permission Management')
                ->collapsed()
                ->icon('heroicon-s-lock-closed'),
        ]);
    }
);
```

### Navigation Group Features:
- **`->label('Name')`** - Sets the group display name
- **`->icon('heroicon-s-icon')`** - Sets the group icon (solid versions)
- **`->collapsed()`** - Makes the group collapsible/collapsed by default
- **Order** - Groups appear in the order they're defined

## 2. Individual Navigation Item Customization

### In each Resource or Page file, you can customize:

```php
protected static ?string $navigationGroup = 'Your Group Name';
protected static ?int $navigationSort = 1; // Lower numbers appear first
protected static ?string $navigationIcon = 'heroicon-o-icon-name';
protected static ?string $navigationLabel = 'Custom Display Name';
```

## 3. Current Navigation Structure

### Dashboard Group
- Dashboard (sort: not set)

### Inventory Management Group
- DeviceResource (sort: not set)
- DeviceAssignmentResource (sort: 2)

### Device Management Group  
- QuickAssignment (sort: 3)
- AssignmentLetterResource (sort: 4)

### Master Data Group
- MainBranchResource (sort: 1)
- BranchResource (sort: not set)
- DepartmentResource (sort: not set)
- BriboxResource (sort: not set)
- BriboxesCategoryResource (sort: 4)

### User Management Group
- UserResource (sort: not set)
- AuthResource (sort: not set)

### Permission Management Group
- RoleManagementResource (sort: 1)
- PermissionManagementResource (sort: 2)
- PermissionMatrix (sort: 3)

## 4. To Change Individual Item Order

Edit the specific resource file, for example in `app/Filament/Resources/DeviceResource.php`:

```php
class DeviceResource extends Resource
{
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 1; // This will make it appear first in the group
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Devices'; // Custom label
    
    // ... rest of the resource
}
```

## 5. Available Icons

Use Heroicons v2 outline icons:
- `heroicon-o-squares-2x2` (Dashboard)
- `heroicon-o-archive-box` (Inventory)
- `heroicon-o-device-phone-mobile` (Devices)
- `heroicon-o-table-cells` (Master Data)
- `heroicon-o-users` (Users)
- `heroicon-o-lock-closed` (Permissions)
- `heroicon-o-chart-bar` (Reports)
- `heroicon-o-cog-6-tooth` (Settings)

## 6. Files to Edit for Navigation Changes

### Main Configuration:
- `app/Providers/AppServiceProvider.php` - Group configuration with icons and collapsed state
- `app/Providers/Filament/AdminPanelProvider.php` - Panel configuration (groups removed from here)

### Individual Resources:
- `app/Filament/Resources/DeviceResource.php`
- `app/Filament/Resources/DeviceAssignmentResource.php`
- `app/Filament/Resources/AssignmentLetterResource.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/AuthResource.php`
- `app/Filament/Resources/MainBranchResource.php`
- `app/Filament/Resources/BranchResource.php`
- `app/Filament/Resources/DepartmentResource.php`
- `app/Filament/Resources/BriboxResource.php`
- `app/Filament/Resources/BriboxesCategoryResource.php`
- `app/Filament/Resources/RoleManagementResource.php`
- `app/Filament/Resources/PermissionManagementResource.php`

### Pages:
- `app/Filament/Pages/QuickAssignment.php`
- `app/Filament/Pages/PermissionMatrix.php`
- `app/Filament/Pages/Dashboard.php`

## 7. Example: Reordering Device Management Group

To make QuickAssignment appear first in Device Management:

```php
// In app/Filament/Pages/QuickAssignment.php
protected static ?int $navigationSort = 1;

// In app/Filament/Resources/AssignmentLetterResource.php  
protected static ?int $navigationSort = 2;
```
