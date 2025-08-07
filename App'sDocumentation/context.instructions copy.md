# 📦 Import CSV dengan Logika Retry & Resolusi Referensi Dinamis

## 📋 Gambaran Umum

Dokumen ini menjelaskan arsitektur dan logika pseudocode untuk mengimplementasikan **importer data CSV yang kuat** di Laravel. Fitur-fitur yang didukung meliputi:

* Pembuatan data referensi secara dinamis (main\_branch, branch, department, user, dll.)
* Logika retry untuk menangani baris yang belum siap dimasukkan
* Cache sementara di memori untuk optimisasi pencarian
* Pelacakan progres untuk debugging atau umpan balik pengguna

---

## 🌟 Tujuan

* Memungkinkan admin mengunggah file CSV mentah yang belum dinormalisasi
* Secara otomatis menormalkan dan memetakan data ke tabel relasional yang terstruktur
* Menangani data referensi yang belum lengkap (buat otomatis jika blm ada)
* Menghindari parsing data secara berulang-ulang
* Mencegah loop retry yang tak berakhir

---

## 🧐 Konsep Utama

### ✅ Penanganan Baris dengan Retry

Setiap baris CSV diproses satu per satu. Jika sebuah data referensi (contoh: branch, department) belum ada:

* Data referensi tersebut akan dibuat
* Baris tersebut **diantrikan ulang** untuk dicoba lagi di iterasi berikutnya

Jika baris gagal terlalu banyak, akan ditandai dan dilewati.

### ✅ Cache Referensi di Memori

Untuk menghindari query database berulang, gunakan cache sementara:

## 🧳 Struktur Data

### 📦 Objek Baris

Setiap baris disimpan sementara sebagai objek:

```php
$row = [
  'raw' => [...],         // isi asli dari baris CSV
  'retry_count' => 0,     // berapa kali sudah dicoba
  'max_retry' => 3,       // batas percobaan
]
```

---

## 🧰 Implementasi Laravel (Saran)
* Selalu cek dulu apa ada hal serupa yang sudah diterapkan. misal : upload file. kalo sudah ada, gunakan sistem yang bisa digunakan seperti form atau semacamnya jika perlu.
* Cek apa saja yang sudah diterapkan. seperti livewire, filament, dll dan pastikan semua algoritma baru bisa digunakan tanpa mengganggu algoritma lama.
* Gunakan `FormRequest` untuk validasi file CSV
* Gunakan `Service Class` di Laravel untuk logika import
* Gunakan `DB::transaction()` per baris atau per device + assignment
* Log error ke file log atau tabel `import_logs`

---

## 📈 Pelacakan Progres

Bisa dibuat variabel progres seperti ini:

```php
$stats = [
  'total_rows' => N,
  'imported' => X,
  'retried' => Y,
  'failed' => Z
];
```

Tampilkan ini ke UI selama proses setelah proses selesai.

---

## 🚨 Penanganan Gagal

* Baris yang melebihi batas retry akan disimpan ke log `retry_failed`
* ekspor baris gagal ke CSV baru agar admin bisa review



## ✅ Contoh Alur: Satu Baris

csv formated :
```text
id,merk,type,sn,type_dev,idribox,idasset,bcs,nama_kanca,bc,nama_uker,pn,nama,idbag,jabatan,userid,spec1,spec2,spec3,dev_date,spec5,spec6,kondisi,fungsi,peruntukan,create_date,create_by,update_date,update_by
1,HP,EliteBook,SN00001,Laptop,A1,AS001,BCS01,Kanca A,BC01,Uker A,PN001,Andi,IT01,Manager,USR001,Intel i5,8GB RAM,256GB SSD,2025-01-15,Windows 10,Office 2019,Baik,digunakan,Karyawan,2025-01-16 08:00:00,admin,2025-01-17 09:00:00,admin
```
```text
Baris [
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

## kamus konversi nama kolom
```text
Briboxes tabel
    "idribox" => "bribox_id",
    add "bribox_category_id" ref from: > briboxes_category.bribox_category_id

Briboxex_category
    "type_dev" => "category_name",


Devices table
    "merk" => "brand",
    "type" => "brand_name",
    "sn" => "serial_number",
    "idasset" => "asset_code",
    add "bribox_id" ref from: > briboxes.bribox_id
    "spec1" => "spec1",
    "spec2" => "spec2",
    "spec3" => "spec3",
    "spec5" => "spec4",
    "spec6" => "spect",
    "kondisi" => "condition",
    "fungsi" => "status",
    "dev_date" => "2025-01-15",


    "peruntukan" => "Karyawan",

branch table
    "bc" => "branch_code",
    "nama_uker" => "unit_name",

main branch_table
    "bcs" => "main_branch_code",
    "nama_kanca" => "main_branch_name",

users table
    "pn" => "pn",
    "nama" => "name",
    "idbag" => "department_id",
    "jabatan" => "position",
    "userid" => "user_id",
```
## contoh alur
```
→ Cek "type_dev" => "Laptop" → tidak ada → buat baru → simpan ke cache
→ Cek idbribox "A1" → tidak ada → buat baru, dengan foreign ke category → simpan ke cache
→ Buat device → berhasil

→ jika ditemukan data terkait tabel branch, atau user, dan statusnya digunakan
→ Cek main_branch_code "BCS01" → Tidak ditemukan → buat baru → simpan ke cache
→ Cek branch_code "BC01" → Tidak ditemukan → buat baru → simpan ke cache
→ Cek user dengan PN
→ Ditemukan → lanjutkan

→ Buat assignment → berhasil
→ jika tidak ditemukan data terkait tabel branch, atau user, tapi statusnya digunakan, maka data direturn bersama error data lainnya
```
Note : Data yang error direturn dalam format csv, ketika selesai import muncul tombol download untuk download data error

---

## 📚 Pengembangan Selanjutnya (kerjakan setelah mvp selesai atau sudah diperintahkan)

* Support job async untuk file besar
* Kirim ringkasan hasil via email ke admin
* Tampilkan progres bar di UI saat import
* Validasi & preview sebelum import dilakukan

---

## 📍 Catatan Tambahan

* Alur ini efisien untuk dataset kecil hingga menengah (\~10 ribu baris)
* Untuk file sangat besar, pertimbangkan pemrosesan bertahap
* Selalu siapkan logika preventif. erorr handling, pencegahan loop, kebocoran cache, dll
