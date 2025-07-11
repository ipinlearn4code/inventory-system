<!DOCTYPE html>
<html>
<head>
    <title>File Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        button { padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a8b; }
        .result { margin-top: 10px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>File Upload Test</h1>
    
    <div class="form-group">
        <h2>Step 0: Test API Upload (No Middleware)</h2>
        <form id="apiForm" action="/api/test-upload-api" method="POST" enctype="multipart/form-data">
            <input type="file" name="test_file" accept=".pdf" required>
            <br><br>
            <button type="submit">Test API Upload</button>
        </form>
        <div id="apiResult" class="result" style="display:none;"></div>
    </div>
    
    <div class="form-group">
        <h2>Step 1: Test Debug Upload (No CSRF)</h2>
        <form id="debugForm" action="/debug-file-upload" method="POST" enctype="multipart/form-data">
            <input type="file" name="test_file" accept=".pdf" required>
            <br><br>
            <button type="submit">Test Debug Upload</button>
        </form>
        <div id="debugResult" class="result" style="display:none;"></div>
    </div>
    
    <div class="form-group">
        <h2>Step 1: Test Basic File Upload (Local Storage)</h2>
        <form id="basicForm" action="/test-upload/basic" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="test_file" accept=".pdf" required>
            <br><br>
            <button type="submit">Test Basic Upload</button>
        </form>
        <div id="basicResult" class="result" style="display:none;"></div>
    </div>
    
    <div class="form-group">
        <h2>Step 2: Test MinIO Upload</h2>
        <form id="minioForm" action="/test-upload/minio" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="test_file" accept=".pdf" required>
            <br><br>
            <button type="submit">Test MinIO Upload</button>
        </form>
        <div id="minioResult" class="result" style="display:none;"></div>
    </div>
    
    <div class="form-group">
        <h2>Step 3: Test Regular Form Submission (Non-AJAX)</h2>
        <form action="/test-upload/basic" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="test_file" accept=".pdf" required>
            <br><br>
            <button type="submit">Test Regular Submit</button>
        </form>
    </div>
    
    <script>
        console.log('JavaScript loaded');
        
        // Handle AJAX forms
        document.getElementById('apiForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('API form submitted');
            await handleFormSubmit(e.target, 'apiResult');
        });
        
        document.getElementById('debugForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Debug form submitted');
            await handleFormSubmit(e.target, 'debugResult');
        });
        
        document.getElementById('basicForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Basic form submitted');
            await handleFormSubmit(e.target, 'basicResult');
        });
        
        document.getElementById('minioForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('MinIO form submitted');
            await handleFormSubmit(e.target, 'minioResult');
        });
        
        async function handleFormSubmit(form, resultId) {
            const resultDiv = document.getElementById(resultId);
            const formData = new FormData(form);
            
            try {
                resultDiv.innerHTML = 'Uploading...';
                resultDiv.className = 'result';
                resultDiv.style.display = 'block';
                
                console.log('Sending request to:', form.action);
                console.log('Form data:', Object.fromEntries(formData));
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    console.log('Response data:', result);
                    
                    if (result.success) {
                        resultDiv.innerHTML = 'Success: ' + result.message + '<br>Path: ' + result.path;
                        resultDiv.className = 'result success';
                    } else {
                        resultDiv.innerHTML = 'Error: ' + result.message;
                        resultDiv.className = 'result error';
                    }
                } else {
                    // Response is not JSON, probably HTML error page
                    const text = await response.text();
                    console.log('Non-JSON response:', text);
                    resultDiv.innerHTML = 'Server Error: Response is not JSON. Check console for details.';
                    resultDiv.className = 'result error';
                }
            } catch (error) {
                console.error('JavaScript error:', error);
                resultDiv.innerHTML = 'JavaScript Error: ' + error.message;
                resultDiv.className = 'result error';
            }
        }
    </script>
</body>
</html>
