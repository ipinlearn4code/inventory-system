# Copilot Context – MinIO File Download for Letter Documents

## Purpose
This controller is designed to **handle download requests for official letter documents** (such as assignment letters) stored in a MinIO object storage, using Laravel and the AWS S3 SDK.

## Background
Letter documents are stored in MinIO with a structured directory path. Users access the files via an HTTP endpoint. The download is streamed for performance reasons, especially for large files.

## MinIO Storage Path Structure
```txt
{letterType}/{assignmentId}/{formattedDate}/{safeLetterNumber}
