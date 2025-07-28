## Device Assignment API - Update Endpoint

### PATCH /api/v1/device-assignments/{id}

Updates a device assignment and optionally its associated letter.

#### Request Parameters

##### URL Parameters:
- `id` (required) - The ID of the device assignment to update

##### Body Parameters:
```json
{
    "status": "string", // optional - One of: 'Digunakan', 'Tidak Digunakan', 'Cadangan'
    "notes": "string", // optional - Max 500 characters
    "returned_date": "date", // optional - Must be >= assigned_date
    "letter_number": "string", // optional - Max 50 characters
    "letter_date": "date", // optional
    "letter_file": "file" // optional - PDF file, max 10MB
}
```

#### Response

##### Success Response (200 OK)
```json
{
    "data": {
        "assignmentId": "integer",
        "status": "string",
        "returnedDate": "date|null",
        "notes": "string|null",
        "assignmentLetter": { // Only included if letter data was updated
            "assignmentLetterId": "integer",
            "assignmentType": "string",
            "letterNumber": "string",
            "letterDate": "date",
            "fileUrl": "string|null"
        }
    }
}
```

##### Error Responses

###### 400 Bad Request
```json
{
    "message": "string",
    "errorCode": "string" // One of: ERR_ASSIGNMENT_UPDATE_FAILED, ERR_DUPLICATE_LETTER_NUMBER
}
```

###### Common Error Codes:
- `ERR_ASSIGNMENT_UPDATE_FAILED`: Generic update failure
- `ERR_DUPLICATE_LETTER_NUMBER`: Letter number already exists

#### Notes

- When updating the letter file, the system includes rollback protection to prevent data loss
- The endpoint supports partial updates - only include the fields you want to change
- File uploads are stored in MinIO with appropriate path structuring
- All actions are logged through the InventoryLogService

#### Example Usage

```bash
# Update assignment with new status and notes
curl -X PATCH http://your-api/api/v1/device-assignments/123 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Tidak Digunakan",
    "notes": "Device returned with minor scratches"
  }'

# Update assignment with new letter file
curl -X PATCH http://your-api/api/v1/device-assignments/123 \
  -F "letter_number=ASG/2025/001" \
  -F "letter_date=2025-07-28" \
  -F "letter_file=@new_letter.pdf"
```

#### Security

- Requires authentication
- Authorization checks are performed to ensure user has permission to update assignments
- File uploads are validated for MIME type and size restrictions
