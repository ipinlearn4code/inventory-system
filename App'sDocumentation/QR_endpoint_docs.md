Create a **Laravel API endpoint**:

### `GET /api/v1/devices/scan/{qr_code}`

This endpoint retrieves device details and assignment history based on a scanned QR code with the prefix `briven-`. It is designed for mobile clients to verify and track devices.

---

### ✅ Expected JSON Response

```json
{
    "data": {
        "device": {
            "id": 22,
            "asset_code": "ABC12345",
            "name": "Laptop Lenovo ThinkPad X1",
            "type": "Laptop high end",
            "serial_number": "SN12345678",
            "dev_date": "2024-01-10",
            "status": "digunakan",
            "condition": "baik",
            "assigned_to": {
                "id": 4,
                "name": "John Doe",
                "department": "IT Support",
                "position": "Front Officer",
                "pn": "IT265478",
                "branch": "Jakarta HQ",
                "branch_code": "BRC263739"
            },
            "spec1": "Ram 16GB",
            "spec2": "Intel i7 9650H",
            "spec3": "Nvidia Quadro T1000",
            "spec4": "",
            "spec5": ""
        },
        "history": [
            {
                "assignment_id": 2,
                "action": "returned",
                "user": "John Doe",
                "approver": "Admin IT",
                "date": "2025-08-01",
                "note": "Device returned after project"
            },
            {
                "assignment_id": 2,
                "action": "assigned",
                "user": "John Doe",
                "approver": "Admin IT",
                "date": "2025-07-10",
                "note": "Assigned for project A"
            }
        ]
    }
}
```

---

### 🧠 Logic Rules & Data Mapping

1. **Device Lookup**:
   - Find the device by `qr_code` (must start with `briven-`) in the `devices` table.
   - `brand_name`: Combine `bribox.category.name`, `brand`, and `brand_name`.

2. **Assigned To**:
   - Retrieve the active assignment (where `returned_at IS NULL`) from the `device_assignments` table.

3. **History**:
   - Get all device assignments related to the device.
   - For each assignment:
     - **Action**: `"returned"` if `returned_at` exists, `"assigned"` otherwise.
     - **Date**: Use `returned_at` or `assigned_at` based on the action.
     - **Approver**: Taken from the related `assignment_letter.approved_by`.
     - **Note**: From the assignment record.

---

### ⚙️ Technical Requirements

- Use Eloquent relationships and eager loading (`with()`) to avoid N+1 queries.
- Use Laravel API Resources (`DeviceResource`) to shape the output cleanly.
- Return `404` if the device is not found by the given QR code.
- Use parameterized queries or Eloquent ORM to prevent SQL injection vulnerabilities.
- Validate and sanitize the `qr_code` input before querying the database.
---
