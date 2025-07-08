# Authentication & Permission System

## Overview
The inventory control system uses a role-based permission system with Spatie Laravel Permission package. Authentication is handled through a separate `auth` table, while user information is stored in the `users` table.

## Database Structure

### Users Table
Contains personnel information (no authentication data):
- `pn` (varchar 8) - Personnel Number (Primary Key)
- `name` (varchar 50) - Full Name
- `department_id` (varchar 4) - Department Reference
- `position` (varchar 100) - Job Position

### Auth Table
Contains authentication credentials and roles:
- `pn` (varchar 8) - Personnel Number (Primary Key, references users.pn)
- `password` (varchar 50) - Hashed Password
- `role` (enum) - Role: 'user', 'admin', 'superadmin'

## Roles & Permissions

### 👤 User Role
**Access Level**: Basic user
**Permissions**:
- `view own assignments` - Can view devices assigned to them
- `make requests` - Can make requests for devices

**Filament Access**: None (users access through mobile app/separate interface)

### 👨‍💼 Admin Role
**Access Level**: Device and assignment manager
**Permissions**:
- All User permissions +
- `manage devices` - Can CRUD devices
- `manage assignments` - Can CRUD device assignments
- `manage regular users` - Can manage users with 'user' role only
- `manage regular auth` - Can manage auth for regular users only

**Filament Access**:
- ✅ View/Create/Edit/Delete Devices
- ✅ View/Create/Edit/Delete Device Assignments
- ✅ View Users (read-only)
- ✅ Create/Edit/Delete Auth records (user role only)
- ❌ Master Data (Departments, Branches, Briboxes)

### 👑 Super Admin Role
**Access Level**: Full system administrator
**Permissions**: ALL permissions including:
- `setup master data` - Can manage departments, branches, briboxes
- `manage all users` - Can manage any user
- `manage all auth` - Can manage any authentication record
- `view audit logs` - Can view system audit logs
- `export data` - Can export system data

**Filament Access**: Full access to all resources and functions

## Test Accounts

### Production Admin Account
- **Username**: `ADM001`
- **Password**: `admin123`
- **Role**: SuperAdmin
- **Access**: Full system access

### Test Admin Account
- **Username**: `TEST01`
- **Password**: `test123`
- **Role**: Admin
- **Access**: Device management, regular user management

### Test User Account
- **Username**: `USER01`
- **Password**: `user123`
- **Role**: User
- **Access**: Mobile app only (no Filament access)

## Login Process

1. **URL**: `/login`
2. **Credentials**: Use Personnel Number (PN) and Password
3. **Session**: Authentication creates session with user data
4. **Redirect**: Successful login redirects to `/admin` (Filament dashboard)

## Permission Implementation

### Filament Resource Access Control
Each Filament resource implements permission checks:

```php
public static function canViewAny(): bool
{
    $auth = session('authenticated_user');
    if (!$auth) return false;
    
    $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
    return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
}
```

### Role-Based Restrictions
- **Admin users** cannot create/edit other admins or superadmins
- **Admin users** cannot access master data management
- **Regular users** have no Filament access (mobile app only)

## Security Features

1. **Password Hashing**: Automatic password hashing using Laravel's Hash facade
2. **Session Management**: Secure session-based authentication
3. **Permission Caching**: Spatie Permission package caches permissions for performance
4. **Guard Configuration**: Proper guard setup for string-based primary keys
5. **Role Inheritance**: Hierarchical permission system

## API Endpoints

### Authentication
- `POST /login` - Login with PN and password
- `POST /logout` - Logout and clear session
- `GET /login` - Show login form

### Admin Panel
- `GET /admin` - Filament dashboard (requires authentication)
- All admin routes require valid session

## Development Notes

### Adding New Permissions
1. Add permission to `PermissionSeeder.php`
2. Assign to appropriate roles
3. Run: `php artisan db:seed --class=PermissionSeeder`

### Creating New Users
1. Create user record in `users` table
2. Create auth record in `auth` table
3. Assign appropriate role using Spatie methods

### Troubleshooting
- Clear permission cache: `php artisan permission:cache-reset`
- Check current user permissions: Use Tinker with `$user->getAllPermissions()`
- Verify role assignments: `$user->getRoleNames()`

## Mobile App Integration

The system is designed to support a mobile app where regular users can:
- View their assigned devices
- Make requests for new devices
- Check device status
- Update device conditions

The mobile app should authenticate against the same `auth` table and respect the same permission system.
