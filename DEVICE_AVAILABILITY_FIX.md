# Device Availability Fix

## Issue Fixed
We fixed a SQL error that was occurring when the application tried to query devices with:
```sql
select * from `devices` where `current_assignment_id` is null and `status` = available
```

The issue was that the `devices` table doesn't have a `current_assignment_id` column. The database design uses a relationship approach where device availability is determined by checking if a device has any active assignments (where `returned_date` is null) in the `device_assignments` table.

## Solution Implemented

1. Added a `scopeAvailable` method to the `Device` model to standardize how we check for available devices:
   ```php
   public function scopeAvailable($query)
   {
       return $query->whereDoesntHave('currentAssignment');
   }
   ```

2. Updated all places in the code that were checking for available devices to use this new scope:
   - `QuickAssignment` page now uses `Device::available()` instead of `Device::whereDoesntHave('currentAssignment')`
   - `DeviceAssignmentResource` form now uses the same approach

## Benefits
- Standardized approach to checking device availability
- No more SQL errors related to non-existent columns
- Code is more maintainable and follows the "Don't Repeat Yourself" principle
- Consistent with the existing database schema design

## Testing
To verify the fix:
1. Try creating a new device assignment through the DeviceAssignmentResource
2. Try using the QuickAssignment feature
3. Confirm that only devices without active assignments are shown in the dropdown menus
4. Verify that no SQL errors occur during these operations

## Next Steps
If there are any other places in the codebase that might be using incorrect queries to check device availability, they should be updated to use the new `available()` scope.
