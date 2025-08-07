---
applyTo: '**'
---
**# 📦 Importing CSV with Retry Logic & Dynamic Reference Resolution**

---

**## 📋 Overview**

This document outlines the architecture and pseudocode logic for implementing a **robust CSV data importer** in Laravel. Supported features include:

* Dynamic creation of reference data (main\_branch, branch, department, user, etc.)
* Retry logic to handle rows not ready for insertion
* In-memory cache for optimized lookup
* Progress tracking for debugging or user feedback

---

**## 🌟 Goals**

* Allow admins to upload raw, non-normalized CSV files
* Automatically normalize and map data to structured relational tables
* Handle incomplete reference data (auto-create if missing)
* Avoid redundant data parsing
* Prevent infinite retry loops

---

**## 🧐 Core Concepts**

### ✅ Row Handling with Retry

Each CSV row is processed one by one. If a reference data (e.g., branch, department) is missing:

* The reference data will be created
* The row will be **re-queued** for retry in the next iteration

If the row fails too many times, it will be marked and skipped.

### ✅ In-Memory Reference Cache

To avoid repetitive database queries, use an in-memory temporary cache.

---

**## 🧳 Data Structure**

### 📦 Row Object

Each row is temporarily stored as an object:

```php
$row = [
  'raw' => [...],         // original CSV row content
  'retry_count' => 0,     // number of retry attempts
  'max_retry' => 3        // retry limit
];
```

---

**## 🧰 Laravel Implementation (Suggestions)**

* Always check for existing implementations (e.g., file uploads). If available, reuse existing components like forms.
* Review currently used tools such as Livewire, Filament, etc., and ensure new logic integrates smoothly.
* Use `FormRequest` for CSV file validation
* Use a `Service Class` in Laravel for import logic
* Use `DB::transaction()` per row or per device + assignment
* Log errors to a file or the `import_logs` table

---

**## 📈 Progress Tracking**

Progress can be tracked with a variable like:

```php
$stats = [
  'total_rows' => N,
  'imported' => X,
  'retried' => Y,
  'failed' => Z
];
```

Display this in the UI during and after the import process.

---

**## 🚨 Failure Handling**

* Rows exceeding retry limits will be logged in `retry_failed`
* Export failed rows to a new CSV for admin review

---

**## ✅ Example Flow: One Row**

### CSV Format Example

```text
id,merk,type,sn,type_dev,idribox,idasset,bcs,nama_kanca,bc,nama_uker,pn,nama,idbag,jabatan,userid,spec1,spec2,spec3,dev_date,spec5,spec6,kondisi,fungsi,peruntukan,create_date,create_by,update_date,update_by
1,HP,EliteBook,SN00001,Laptop,A1,AS001,BCS01,Kanca A,BC01,Uker A,PN001,Andi,IT01,Manager,USR001,Intel i5,8GB RAM,256GB SSD,2025-01-15,Windows 10,Office 2019,Baik,digunakan,Karyawan,2025-01-16 08:00:00,admin,2025-01-17 09:00:00,admin
```

### Parsed Row Structure

```php
Row = [
  "merk" => "HP",
  "type" => "EliteBook",
  "sn" => "SN00001",
  "type_dev" => "Laptop",
  "idribox" => "A1",
  "idasset" => "AS001",
  "bcs" => "BCS01",
  "nama_kanca" => "Kanca A",
  "bc" => "BC01",
  "nama_uker" => "Uker A",
  "pn" => "PN001",
  "nama" => "Andi",
  "idbag" => "IT01",
  "jabatan" => "Manager",
  "userid" => "USR001",
  "spec1" => "Intel i5",
  "spec2" => "8GB RAM",
  "spec3" => "256GB SSD",
  "dev_date" => "2025-01-15",
  "spec5" => "Windows 10",
  "spec6" => "Office 2019",
  "kondisi" => "Baik",
  "fungsi" => "digunakan",
  "peruntukan" => "Karyawan",
  "create_date" => "2025-01-16 08:00:00",
  "create_by" => "admin",
  "update_date" => "2025-01-17 09:00:00",
  "update_by" => "admin"
];
```

---

**## 🗂️ Column Name Mapping Dictionary (Appendix)**

### Briboxes Table

* `idribox` => `bribox_id`

### Briboxes\_category

* `type_dev` => `category_name`

### Devices Table

* `merk` => `brand`
* `type` => `brand_name`
* `sn` => `serial_number`
* `idasset` => `asset_code`
* `spec1` => `spec1`
* `spec2` => `spec2`
* `spec3` => `spec3`
* `spec5` => `spec4`
* `spec6` => `spect`
* `kondisi` => `condition`
* `fungsi` => `status`
* `dev_date` => `dev_date`
* `peruntukan` => `Karyawan`

### Branch Table

* `bc` => `branch_code`
* `nama_uker` => `unit_name`

### Main Branch Table

* `bcs` => `main_branch_code`
* `nama_kanca` => `main_branch_name`

### Users Table

* `pn` => `pn`
* `nama` => `name`
* `idbag` => `department_id`
* `jabatan` => `position`
* `userid` => `user_id`

---

Note: the column mapping above only represents the **table and field name mapping**. To understand how these entities relate to each other (e.g., foreign keys or model relations), please refer to the **Laravel Eloquent model relationships** defined in the codebase.

\---

**## 🔁 Sample Flow**

```text
→ Check "type_dev" = "Laptop" → not found → create new → save to cache
→ Check idbribox = "A1" → not found → create new with foreign key to category → save to cache
→ Create device → success

→ Check main_branch_code = "BCS01" → not found → create new → save to cache
→ Check branch_code = "BC01" → not found → create new → save to cache
→ Check user by PN → found → proceed

→ Create assignment → success
→ If required data is missing but status is in use → return with other error data
```

> ⚠️ Note: Errors are returned in CSV format. After import, a download button will appear for failed rows.

---

**## 📚 Future Development (After MVP or Upon Request)**

* Support async jobs for large files
* Send summary result via email to admin
* Show progress bar in UI during import
* Validate & preview before actual import

---

**## 📍 Additional Notes**

* This flow is efficient for small to medium datasets (\~10,000 rows)
* For very large files, consider batch processing
* Always prepare defensive logic: error handling, retry prevention, cache leaks, etc.
