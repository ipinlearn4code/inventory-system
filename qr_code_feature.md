# QR Code & Asset Sticker Feature Plan for Laravel + Filament

---

## Objective
Enable QR code generation for each device/asset and display it in a printable sticker format.

---

## ✅ Step-by-Step Plan

### 1. Prepare Asset Data
Ensure all necessary device data is present in the `Device` model, and that `asset_code` will be used as the main identifier for generating QR codes. Other useful fields include:
- `brand_name`, `serial_number`, `condition`, and any relevant specifications.

### 2. Add QR Code Generator
Install and configure a Laravel package to generate QR codes (e.g., `simple-qrcode`).
- QR will typically be generated using `asset_code` with a prefix or suffix of `briven` (e.g., `briven-asset_code` or `asset_code-briven`).

### 3. Create Sticker View and Customization Page
- Create a new route that accepts a device ID or asset code.
- Design a Blade view to display a sticker layout:
  - QR code on the left
  - Asset information on the right (brand, asset code, serial number, condition)
- Style the view to match real-world sticker dimensions (e.g., 5cm x 10cm).

### 4. Add Action Button in Filament
In the Filament admin panel (resource list or detail view), add an action button:
- Label: "View Sticker" or "Print QR"
- It links to the route from Step 3, opening the sticker preview in a new tab.

### 5. (Optional) Add Export to PDF
- Use a package like `barryvdh/laravel-dompdf` to convert the sticker view to a downloadable PDF.
- Create a route that generates the PDF version of the sticker.

### 6. (Optional) Mass Printing Feature
- In the Filament table view, add a bulk action (e.g., "Print Selected Stickers").
- Allow users to select multiple records and generate one PDF containing all stickers (one per page or in a grid).

### 7. (Optional) Sticker Design Enhancements
- Use HTML + CSS + JavaScript if needed to enhance the visual layout of the sticker.
- Include logo, brand colors, borders, or custom fonts as needed.

### 8. (Optional) QR Code Reader Integration
- Add a QR code reader interface in the web app that uses the phone or device camera.
- Use a JavaScript-based library like `html5-qrcode` or `instascan` to scan QR codes from the browser.
- Once scanned, extract the `asset_code` (with `briven` identifier) and redirect or display the asset information.
- Ideal for mobile staff scanning physical stickers in the field.

---

## 🏁 Final Output
From the Filament admin, users can:
- View a QR code for each asset
- Preview the sticker layout
- Customize the layout
- Download or print the sticker
- Perform mass printing if needed