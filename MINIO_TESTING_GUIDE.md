# MinIO Integration and Quick Assignment Feature - Testing Guide

## Testing MinIO Integration

1. **Ensure MinIO is running locally**:
   ```bash
   # If you're using Docker, you can start MinIO with:
   docker run -p 9000:9000 -p 9001:9001 \
     -e "MINIO_ROOT_USER=minioadmin" \
     -e "MINIO_ROOT_PASSWORD=minioadmin123" \
     -v minio-data:/data \
     minio/minio server /data --console-address ":9001"
   ```

2. **Access MinIO Console**:
   - Open a browser and navigate to `http://localhost:9001`
   - Login with credentials:
     - Username: `minioadmin`
     - Password: `minioadmin123`
   - Create a bucket named `assignment-letter` if it doesn't exist

3. **Verify Laravel Configuration**:
   - Ensure the `.env` file contains the correct MinIO settings:
     ```
     MINIO_ACCESS_KEY=minioadmin
     MINIO_SECRET_KEY=minioadmin123
     MINIO_ENDPOINT=http://localhost:9000
     MINIO_URL=http://localhost:9000
     MINIO_BUCKET=assignment-letter
     FILESYSTEM_DISK=minio
     ```

4. **Test File Upload Independently** (Optional):
   ```php
   // Run this in Tinker (php artisan tinker)
   $file = new \Illuminate\Http\UploadedFile(
       storage_path('app/test.pdf'), 
       'test.pdf', 
       'application/pdf', 
       null, 
       true
   );
   app(\App\Services\MinioStorageService::class)->storeAssignmentLetterFile($file, 'assignment', 1, '2025-07-11', 'TEST-001');
   ```

## Testing Quick Assignment Feature

1. **Navigate** to the Admin Dashboard

2. **Access Quick Assignment**:
   - Click on "Device Management" in the sidebar
   - Select "Quick Assignment"

3. **Step 1 - Device Assignment**:
   - Select a User from the dropdown
   - Select a Device from the dropdown (only available devices will be shown)
   - Set the Assignment Date (defaults to today)
   - Add optional Notes
   - Click "Next"

4. **Step 2 - Assignment Letter**:
   - Enter a Letter Number (e.g., "ASSIGN-2025-001")
   - Set the Letter Date (defaults to today)
   - Verify the "Are you the approver?" toggle is on
   - Upload a test file (PDF, image, or Word document)
   - Click "Create Assignment and Letter"

5. **Verify Success**:
   - A success notification should appear
   - You should be redirected to the Device Assignments list
   - The new assignment should appear in the list

6. **Check MinIO Storage**:
   - Access MinIO Console at `http://localhost:9001`
   - Navigate to the `assignment-letter` bucket
   - Verify the file exists in the expected directory structure:
     ```
     assignment/{assignment-id}/{date}/{letter-number}/{filename}
     ```

7. **Test Assignment Letter Download**:
   - Go to Assignment Letters list
   - Find the newly created letter
   - Click on the download icon
   - The file should download or open in a new tab

## Troubleshooting

If you encounter any issues, check the Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Common issues and solutions:

1. **Connection Error**: Make sure MinIO is running and accessible at `http://localhost:9000`

2. **Permission Error**: Check that the MinIO user has write permissions to the bucket

3. **Database Error**: Verify that all required fields are being passed correctly to the models
