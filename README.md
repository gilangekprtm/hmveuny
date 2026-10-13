# HMVE Website

Website HMVE dibuat dengan Laravel. Secara lokal, data konten dan akun disimpan
sebagai JSON di `storage/app/data/`; aplikasi tidak memerlukan database untuk
dijalankan.

## Menjalankan aplikasi

1. Salin `.env.example` menjadi `.env` jika belum ada.
2. Jalankan `composer install` dan `npm install`.
3. Jalankan `php artisan key:generate`.
4. Jalankan `npm run build`, lalu `php artisan serve`.

Session dan cache lokal menggunakan penyimpanan file, sementara antrean
menggunakan driver sinkron. Tidak perlu menjalankan migrasi database.

## Data JSON

File `storage/app/data/*.json` menyimpan koleksi `users`, `pembinas`,
`pimpinans`, `units`, `anggotas`, `site_contents`, dan `publications`. Setiap
file berisi daftar objek JSON dengan `id` unik. Data yang dikelola melalui
control panel langsung ditulis ke file-file tersebut saat menggunakan driver
lokal. Untuk deployment, driver GitHub dapat membaca dan menyimpan koleksi itu
ke repo privat melalui GitHub Contents API.

## Deployment ke Vercel

Vercel menjalankan aplikasi Laravel melalui runtime PHP komunitas
`vercel-php`. Hubungkan repo source website ke proyek Vercel, buat Vercel Blob
Store publik untuk foto website, lalu tambahkan environment variables berikut
di pengaturan proyek Vercel:

| Variable | Nilai |
| --- | --- |
| `APP_KEY` | Kunci hasil `php artisan key:generate --show`; simpan hanya di Vercel |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | URL deployment website |
| `SESSION_DRIVER` | `cookie` |
| `CACHE_STORE` | `array` |
| `LOG_CHANNEL` | `stderr` |
| `JSON_DATA_DRIVER` | `github` |
| `GITHUB_DATA_REPOSITORY` | `pemilik/nama-repo-data` |
| `GITHUB_DATA_TOKEN` | Fine-grained token dengan izin Contents read/write untuk repo data saja |
| `GITHUB_DATA_BRANCH` | Branch repo data, biasanya `main` |
| `GITHUB_DATA_DIRECTORY` | Folder JSON dalam repo data, biasanya `data` |
| `UPLOADS_DRIVER` | `vercel` |
| `VERCEL_BLOB_INTERNAL_TOKEN` | Secret acak untuk komunikasi internal endpoint delete; jangan commit |

Vercel menyediakan `VERCEL_URL` otomatis. Buat `BLOB_READ_WRITE_TOKEN` dengan
menghubungkan Blob Store ke proyek Vercel. Jangan menaruh token GitHub atau Blob
di variabel `VITE_*`, karena variabel tersebut dapat masuk ke JavaScript browser.

Repo data privat harus berisi file `data/users.json`, `data/pembinas.json`,
`data/pimpinans.json`, `data/units.json`, `data/anggotas.json`,
`data/site_contents.json`, dan `data/publications.json`. Semua perubahan dari
panel admin akan menjadi commit baru di repo itu. Karena riwayat Git menyimpan
versi lama, batasi akses repo tersebut dan jangan gunakan kredensial yang pernah
terekspos.

Pembuatan repo, pemberian token, pembuatan Blob Store, dan deploy pertama tetap
dilakukan dari akun GitHub/Vercel Anda. Jangan mengirim nilai token atau
`APP_KEY` melalui chat.

Akun yang mendaftar melalui halaman registrasi mendapat akses anggota biasa
(`level_akses: 1`). Untuk memberi akses control panel, ubah akun yang tepercaya
di `users.json` menjadi `"level_akses": 2`.

### Menyalin data dari database lama

Jika situs sebelumnya menggunakan database, jalankan perintah ini sekali
sebelum menonaktifkan konfigurasi koneksi database:

```sh
php artisan data:import-database
```

Perintah ini menyalin tabel yang tersedia ke koleksi JSON yang masih kosong
tanpa menghapus atau mengubah database asal. Periksa hasil salinan dan backup
file JSON sebelum menghapus database lama.
