# File Upload Testing Guide

## Testing Steps

### Step 1: Test Basic File Upload
1. Navigate to: http://127.0.0.1:8000/test-upload
2. Use the test PDF file: `test-document.pdf` (128KB)
3. Try "Test Basic Upload" first
4. Check the response and any error messages

### Step 2: Test Assignment Letter Creation
1. Login to the application: http://127.0.0.1:8000/login
2. Navigate to Assignment Letters
3. Click "Create Assignment Letter"
4. Fill in the form:
   - Select any device assignment
   - Letter type: Assignment Letter
   - Letter number: TEST-001
   - Letter date: today
   - Upload the test PDF file
5. Submit and check for errors

### Step 3: Check Logs
If there are errors, check the Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### Step 4: Test MinIO Upload
Once basic upload works, test MinIO upload:
1. Go back to: http://127.0.0.1:8000/test-upload
2. Try "Test MinIO Upload"
3. Check if it works

### Debugging Checklist

1. **File Upload Permissions**:
   - Check if `storage/app/public/temp-uploads` exists and is writable
   - Verify storage link: `php artisan storage:link`

2. **File Validation**:
   - File size: max 5MB (5120KB)
   - File type: Only PDF
   - Check MIME type validation

3. **MinIO Connection**:
   - Bucket exists: `assignment-letter`
   - MinIO server running on port 9000
   - Credentials match .env file

### Current Status
- ✅ MinIO connection working
- ✅ Bucket created successfully
- 🧪 Testing basic file upload (current step)

### Expected File Path Structure
Local: `storage/app/public/temp-uploads/filename.pdf`
MinIO: `assignment/1/2025-07-11/test-001/filename.pdf`
