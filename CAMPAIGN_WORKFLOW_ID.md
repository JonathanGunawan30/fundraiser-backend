# Alur Kerja & Logika Bisnis Modul Campaign

Dokumen ini menjelaskan alur kerja, aturan bisnis, dan integrasi teknis untuk modul **Campaign** di aplikasi Fundraiser.

## 1. Status Siklus Hidup & Transisi

Sebuah campaign memiliki dua jenis status: `status` (publik) dan `verified_status` (internal admin).

| Verified Status | Campaign Status | Deskripsi |
|-----------------|-----------------|-----------|
| `pending`       | `pending`       | Status awal saat campaign baru dibuat oleh user. Belum muncul di halaman publik utama. |
| `approved`      | `active`        | Admin menyetujui campaign. Campaign muncul di publik dan siap menerima donasi. |
| `rejected`      | `suspended`     | Admin menolak campaign. Tidak muncul di publik. |

---

## 2. Alur Pembuatan (Create)

1.  **Validasi Input**:
    *   `goal_amount` minimal Rp 10.000.
    *   `deadline` harus di masa depan (minimal besok).
    *   `cover_image` wajib diunggah.
2.  **Pemrosesan File (R2)**:
    *   `cover_image` disimpan ke Cloudflare R2 folder `campaigns/covers/` dengan nama file **UUID**.
    *   `images` (gallery) diproses secara batch ke folder `campaigns/gallery/`.
3.  **Transaksi Database**:
    *   Data campaign disimpan.
    *   Relasi **Tags** disinkronisasi (Many-to-Many).
    *   Data galeri disimpan ke tabel `campaign_images`.
    *   Jika salah satu gagal, semua file di R2 dan record DB dibatalkan (Rollback).

---

## 3. Aturan Bisnis Produksi (Production Rules)

Untuk menjaga integritas data keuangan dan kepercayaan donatur, aturan berikut diterapkan secara ketat di `CampaignService`:

### A. Proteksi Perubahan Dana (Update)
*   **Aturan**: User dilarang mengubah `goal_amount` menjadi lebih kecil dari `collected_amount` (dana yang sudah terkumpul).
*   **Response**: `422 Unprocessable Entity`.

### B. Kebijakan Penghapusan (Delete)
*   **Aturan**: Campaign yang **sudah menerima donasi** (`collected_amount > 0`) **TIDAK BOLEH DIHAPUS**.
*   **Alasan**: Untuk keperluan audit finansial. Data donasi harus selalu memiliki referensi ke target campaign-nya.
*   **Response**: `403 Forbidden`.
*   **Rekomendasi**: Jika campaign bermasalah tapi sudah ada dana, Admin harus menggunakan fitur `suspend` atau `complete`.

---

## 4. Manajemen Gambar (R2 Auto-Cleanup)

Sistem secara otomatis mengelola pembersihan storage agar tidak ada file sampah (orphan files):

1.  **Update Cover**: Saat cover baru diunggah, file cover lama di Cloudflare R2 otomatis dihapus.
2.  **Update Gallery**: Menggunakan strategi *Replace*. Semua foto gallery lama dihapus dari R2 dan database, digantikan dengan kumpulan foto yang baru.
3.  **Delete Campaign**: Jika campaign dihapus (hanya jika `collected_amount == 0`), semua file terkait (cover & gallery) akan dibersihkan dari R2.

---

## 5. Relasi

Model Campaign adalah pusat dari aplikasi dan berelasi dengan beberapa tabel lain:

*   **`user`**: Pemilik/pembuat campaign.
*   **`category`**: Kategori utama campaign.
*   **`tags`**: Label deskriptif (Many-to-Many).
*   **`images`**: Galeri foto pendukung.
*   **`updates`**: Update progres berkala yang diposting oleh pembuat.
*   **`donations`**: Semua catatan donasi yang masuk ke campaign ini.
*   **`withdrawals`**: Pengajuan penarikan dana oleh pembuat.

---

## 6. Verifikasi Admin

Admin memiliki kontrol penuh melalui endpoint `/verify`:
*   `approved`: Menandai campaign sah dan layak tayang.
*   `rejected`: Menandai campaign melanggar aturan atau data tidak lengkap.
*   Sistem mencatat `verified_by` (ID Admin), `verified_at`, dan mengubah `status` secara otomatis.
