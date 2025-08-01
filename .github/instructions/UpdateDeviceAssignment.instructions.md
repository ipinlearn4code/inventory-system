---
applyTo: '*DeviceAssignmentController*'
---
# Instructions for Editing the `PATCH /assignments/{assignment}` Endpoint

## 📌 Endpoint Purpose

This endpoint allows updating certain fields of an existing assignment and its associated assignment letter. The update is done using `multipart/form-data`.

**Important:** This is a PATCH endpoint, intended for *partial updates only*. It must not create new records or modify immutable fields.

---

## ✅ Allowed Fields to Update

| Field           | Location           | Type       | Rules                                                                 |
|-----------------|--------------------|------------|-----------------------------------------------------------------------|
| `notes`         | Assignment          | string     | optional, nullable, max: 500                                          |
| `assigned_date` | Assignment          | date       | optional, must be before or equal to today                            |
| `letter_number` | AssignmentLetter    | string     | optional, max: 50                                                     |
| `letter_date`   | AssignmentLetter    | date       | optional                                                              |
| `letter_file`   | AssignmentLetter    | file (PDF) | optional, must be valid PDF, max size 10MB, replaces existing file    |

---

## ❌ Not Allowed to Update

- `device_id`
- `user_id`

If these fields are present in the request, they should be ignored or rejected with a validation error.

---

## 🔄 Validation Rules

Use Laravel's `sometimes` rule for all editable fields.

```php
$request->validate([
    'notes' => 'sometimes|nullable|string|max:500',
    'assigned_date' => 'sometimes|date|before_or_equal:today',
    'letter_number' => 'sometimes|string|max:50',
    'letter_date' => 'sometimes|date',
    'letter_file' => 'sometimes|file|mimes:pdf|max:10240',
]);
