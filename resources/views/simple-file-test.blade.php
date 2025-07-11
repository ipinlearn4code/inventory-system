<!DOCTYPE html>
<html>
<head>
    <title>Simple File Upload Test</title>
</head>
<body>
    <h1>Simple File Upload Test</h1>
    
    <h2>Basic Upload (No AJAX)</h2>
    <form action="/test-upload/basic" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="test_file" accept=".pdf" required>
        <br><br>
        <button type="submit">Upload File</button>
    </form>
    
    <hr>
    
    <h2>Debug Information</h2>
    <p>Routes Available:</p>
    <ul>
        <li><a href="/test-upload">GET /test-upload</a> (This page)</li>
        <li>POST /test-upload/basic (Basic upload test)</li>
        <li>POST /test-upload/minio (MinIO upload test)</li>
    </ul>
    
    <p>CSRF Token: {{ csrf_token() }}</p>
</body>
</html>
