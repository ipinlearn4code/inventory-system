# Inventory System API Documentation

## Overview
This document provides comprehensive documentation for the Inventory System API, which manages devices, device assignments, users, and related data for a corporate asset management system.

## Architecture
The API follows SOLID principles and clean architecture patterns:

### Clean Architecture Implementation
- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic and orchestrate operations
- **Repositories**: Handle data access and database operations  
- **Contracts**: Define interfaces for dependency injection
- **Models**: Represent data structures and relationships

### Key Design Patterns
- **Repository Pattern**: Abstraction layer for data access
- **Service Layer**: Business logic separation
- **Dependency Injection**: Loose coupling between components
- **Single Responsibility**: Each class has one clear purpose

## API Structure

### Base URL
```
https://api.yourdomain.com/api/v1
```

### Version
Current API version: **v1**

### Response Format
All API responses follow a consistent JSON structure:

#### Success Response
```json
{
  "data": {
    // Response data here
  },
  "meta": {
    // Pagination or additional metadata (when applicable)
  }
}
```

#### Error Response
```json
{
  "message": "Error description",
  "errorCode": "ERR_CODE_IDENTIFIER",
  "errors": {
    // Validation errors (when applicable)
  }
}
```

## Authentication
All API endpoints require authentication using Bearer tokens.

### Request Header
```
Authorization: Bearer {your-token-here}
```

### Authentication Flow
1. Login via `/api/auth/login` to receive a token
2. Include the token in the Authorization header for all subsequent requests
3. Token expires after 24 hours
4. Use `/api/auth/refresh` to get a new token

## API Modules

### 1. Device Management
**Base URL**: `/api/v1/devices`

Manages the core device inventory including creation, updates, and deletion.

**Key Features:**
- Device CRUD operations
- Search and filtering
- Device condition tracking
- Assignment status monitoring

**Documentation**: [DeviceEndpoints.md](./DeviceEndpoints.md)

### 2. Device Assignments
**Base URL**: `/api/v1/device-assignments`

Handles the assignment of devices to users, tracking usage, and returns.

**Key Features:**
- Assign devices to users
- Track assignment history
- Device return processing
- Assignment validation rules

**Documentation**: [DeviceAssignmentEndpoints.md](./DeviceAssignmentEndpoints.md)

### 3. Dashboard & Analytics
**Base URL**: `/api/v1/dashboard`

Provides KPIs, charts, and analytics data for administrative dashboards.

**Key Features:**
- System-wide statistics
- Device condition analytics
- Branch-based reporting
- Activity monitoring

**Documentation**: [DashboardEndpoints.md](./DashboardEndpoints.md)

### 4. User Operations
**Base URL**: `/api/v1/user`

User-facing endpoints for mobile and web applications.

**Key Features:**
- Personal device listings
- Device detail views
- Issue reporting
- Assignment history

**Documentation**: [UserEndpoints.md](./UserEndpoints.md)

### 5. Metadata & Reference Data
**Base URL**: `/api/v1/metadata`

Provides reference data like users, branches, and categories.

**Key Features:**
- User directory
- Branch listings
- Device categories
- Form options

**Documentation**: [MetadataEndpoints.md](./MetadataEndpoints.md)

### 6. Form Options (Legacy)
**Base URL**: `/api/form-options`

Legacy endpoint for form validation and dropdown options.

**Documentation**: [FormOptionsEndpoints.md](./FormOptionsEndpoints.md)

### 7. Storage & File Management
**Base URL**: `/api/storage`

Handles file uploads and downloads using MinIO storage.

**Documentation**: [StorageEndpoints.md](./StorageEndpoints.md)

## Common Request/Response Patterns

### Pagination
Most list endpoints support pagination:

**Request Parameters:**
- `page`: Page number (default: 1)
- `perPage`: Items per page (default: 20, max: 100)

**Response Meta:**
```json
{
  "meta": {
    "currentPage": 1,
    "lastPage": 5,
    "total": 95
  }
}
```

### Filtering
Many endpoints support filtering:

**Common Filters:**
- `search`: Text search across relevant fields
- `branchId`: Filter by branch
- `status`: Filter by status values
- `condition`: Filter by condition values

### Sorting
Default sorting is typically by creation date (newest first). Some endpoints support custom sorting.

## Error Handling

### HTTP Status Codes
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

### Common Error Codes
- `ERR_INVALID_CREDENTIALS`: Authentication failed
- `ERR_UNAUTHORIZED_ACCESS`: Insufficient permissions
- `ERR_VALIDATION_FAILED`: Request validation errors
- `ERR_RESOURCE_NOT_FOUND`: Requested resource doesn't exist
- `ERR_BUSINESS_RULE_VIOLATION`: Business logic constraint violated

## Rate Limiting
- **Limit**: 1000 requests per hour per authenticated user
- **Headers**: Rate limit status included in response headers
- **Exceeded**: Returns 429 status code with retry information

## Security Considerations

### Input Validation
- All inputs are validated using Laravel's validation rules
- SQL injection protection via Eloquent ORM
- XSS protection through output escaping
- CSRF protection for web forms

### Data Privacy
- Users can only access their own data (user endpoints)
- Admin endpoints require appropriate permissions
- Sensitive data is excluded from API responses
- Audit logging for all data modifications

### Authentication Security
- Tokens expire automatically
- Rate limiting prevents brute force attacks
- HTTPS required for all API communication
- Refresh tokens for extended sessions

## Development Guidelines

### API Versioning
- Version included in URL path (`/api/v1/`)
- Backward compatibility maintained within versions
- Deprecation notices provided before breaking changes
- Migration guides for major version updates

### Testing
- Unit tests for all service methods
- Integration tests for API endpoints
- Test data factories for consistent testing
- Automated testing in CI/CD pipeline

### Documentation
- OpenAPI/Swagger specification available
- Comprehensive endpoint documentation
- Code examples in multiple languages
- Postman collection for testing

## Integration Examples

### PHP/Laravel
```php
use Illuminate\Support\Facades\Http;

$response = Http::withToken($token)
    ->get('https://api.example.com/api/v1/devices', [
        'search' => 'laptop',
        'condition' => 'Baik'
    ]);

$devices = $response->json()['data'];
```

### JavaScript/Fetch
```javascript
const response = await fetch('/api/v1/devices?search=laptop', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();
const devices = data.data;
```

### cURL
```bash
curl -X GET "https://api.example.com/api/v1/devices?search=laptop" \
  -H "Authorization: Bearer your-token-here" \
  -H "Accept: application/json"
```

## Support & Contact

### Technical Support
- **Email**: tech-support@company.com
- **Documentation**: [API Documentation Portal]
- **Issue Tracking**: [GitHub Issues]

### API Status
- **Status Page**: [status.api.company.com]
- **Maintenance Window**: Sundays 2-4 AM UTC
- **SLA**: 99.9% uptime guarantee

## Changelog

### v1.1.0 (Current)
- Added user profile endpoints
- Enhanced filtering capabilities
- Improved error handling
- Performance optimizations

### v1.0.0
- Initial API release
- Core device management
- Basic assignment functionality
- Dashboard endpoints

---

**Note**: This documentation is automatically updated with each API release. For the most current information, please refer to the online documentation portal.
