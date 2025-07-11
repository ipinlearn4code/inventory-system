# Storage Directory Setup

To ensure that file uploads work correctly, make sure to run:

```bash
# Create symbolic link from public/storage to storage/app/public
php artisan storage:link

# Create required directories with proper permissions
mkdir -p storage/app/public/temp-uploads
chmod -R 775 storage/app/public
```

For Windows systems:
```powershell
# Create symbolic link
php artisan storage:link

# Create required directory
New-Item -Path "storage/app/public/temp-uploads" -ItemType Directory -Force
```

This ensures that:
1. The `public/storage` directory is properly linked to `storage/app/public`
2. The temporary upload directory exists
3. Permissions are properly set for file uploads
