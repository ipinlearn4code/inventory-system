
# Assignment Letter File Handling Revised Plan

## ✅ Current Status
- ✅ File saving logic works correctly.
- ✅ Database stores the correct `file_path`.

---

## 🛠️ Task Instructions

### 1. Change `file_path` Format
**Old Format:**
```

return/2/2025-07-24/dd/01k0x5pnvw89j06a20ky5d6b8s.pdf

```

**New Format:**
```

{assignment_id}/{letter_type}/{original_filename.ext}

```

✅ **Example:**
```

6/assignment/filenameisfilename.pdf

````

---

Note : Use Original Filename Instead of UUID

* Slugify if needed, preserve the file extension.

---

### 2. Avoid Overwriting Files with Same Name

If the file name already exists in the destination directory, append a suffix to avoid collision.

✅ **Examples:**

* `surat_penugasan.pdf`
* `surat_penugasan-1.pdf`
* `surat_penugasan-2.pdf`

---

### 3. Ensure Sync with MinIO on Update/Delete

#### ✅ On Update:

* Do save update :

  * Check if a file already exists for the current `assignment_letter`.
  * If yes, download to laravel storage as a temp
  * then delete it from MinIO:
  * if delete succes, save new file to minio
  * if save file succes, delete file on temp then change file_path on db,
  * if save new file not succes, reupload old file to path (rollback)

  Show status success/fail/etc use filament notification

#### ✅ On Delete:

* When an `AssignmentLetter` is deleted:

  * Also remove its file from MinIO.

---

## 🔄 Apply Changes To:
- api (crud)
- web (crud)
- all files that use minio storage