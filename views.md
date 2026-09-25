# Inventaris View Netivo

Dokumen ini dibuat sebelum rencana reset UI besar-besaran. Isinya adalah peta fitur yang saat ini sudah tersedia di setiap view, sehingga implementasi ulang dapat mengganti tampilan tanpa kehilangan alur bisnis, route, authorization, form, dan state yang sudah berjalan.

> Status: inventaris awal, belum ada view atau CSS yang dihapus. Sumber referensi visual untuk implementasi berikutnya adalah `/home/geats/Project Pribadi/antre-in`.

## 1. Aturan migrasi UI

Implementasi ulang akan mengikuti pola Otika dari `antre-in`:

- Layout aplikasi: `.main-wrapper.main-wrapper-1`, `.navbar-bg`, `.main-navbar`, `.main-sidebar`, `.main-content`, `.section`, `.section-header`, `.section-body`, `.main-footer`.
- Bootstrap 4: `mr-*`, `ml-*`, `data-toggle`, `data-target`, `data-dismiss`; tidak memakai Bootstrap 5 atau Metronic.
- Navbar, sidebar, footer, card, table, modal, empty state, dan form mengikuti markup/spacing Otika yang sudah dipakai di `antre-in`.
- Create/edit master tetap menggunakan modal di halaman index; tidak membuat halaman `create` atau `edit` baru.
- DataTables digunakan hanya pada listing yang saat ini sudah menggunakannya atau memang membutuhkan pencarian, pagination, dan export.
- Semua asset Otika tetap memakai `asset()` dan `ASSET_URL`; upload aplikasi memakai URL storage.
- Setiap halaman baru wajib mempertahankan loading, empty, error, success, authorization, CSRF, validasi inline, dan responsive state.
- Icon utama mengikuti satu bahasa visual. Target migrasi adalah Feather seperti `antre-in`; Font Awesome hanya dipakai bila bundle/komponen Otika membutuhkan class legacy.

## 2. Struktur layout dan komponen bersama

### `resources/views/layouts/app.blade.php`

Layout untuk seluruh halaman authenticated.

- Header: toggle sidebar, fullscreen, notifikasi, profile dropdown, logout.
- Sidebar: logo Netivo, menu dashboard, menu admin/supervisor/customer berdasarkan role, menu profil.
- Main content: judul halaman, breadcrumb, flash message, konten view.
- Setting panel: tema terang/gelap, warna sidebar, warna tema, sidebar compact, sticky header.
- Footer: copyright Netivo dan keterangan sistem.
- Script: Otika global, plugin page, `scripts.js`, `custom.js`, modal stacking fix, preference script.

### `resources/views/layouts/auth.blade.php`

Layout tanpa sidebar untuk authentication page.

- Asset CSS/JS Otika.
- Loader.
- Slot content login.
- Flash message dan stack script.

### `resources/views/layouts/error.blade.php`

Layout utility/error page tanpa sidebar.

- Card centered.
- Empty/error state dengan kode error, pesan, dan tombol kembali ke dashboard/login.

### Components

| View | Peran | Dependency yang harus dipertahankan |
|---|---|---|
| `components/flash-message.blade.php` | Flash success/error/validation | Dipanggil layout atau halaman auth |
| `components/modal-fix.blade.php` | Memindahkan modal ke `body` dan memperbaiki stacking context | Bootstrap 4 modal, jQuery |
| `components/netivo-design-system.blade.php` | Override UI Netivo saat ini | Kandidat utama untuk dihapus saat reset dan diganti dengan override minimal seperti `antre-in` |
| `components/otika-font-fallback.blade.php` | Font Nunito dan Font Awesome melalui route same-origin | Route `otika.font` |
| `components/otika-preferences-script.blade.php` | Sinkronisasi setting panel dengan form/body class | `preferences.update`, selector setting panel |
| `dashboard/partials/stat-card.blade.php` | KPI card reusable | Input: `label`, `value`, `icon`, `color`, `empty` |
| `dashboard/partials/transactions.blade.php` | Tabel 10 transaksi terbaru | Input: `transactions`, link berdasarkan role |

## 3. Authentication dan utility pages

### `auth/login.blade.php`

- Route GET: `login` (`/login`), controller `AuthenticatedSessionController@create`.
- Route POST: `login.store` (`/login`), controller `AuthenticatedSessionController@store`.
- Role: guest.
- Isi: branding Netivo, headline/login panel, email, password, remember me.
- Fitur: CSRF, old input email/remember, validation error per field, throttle login, redirect ke dashboard role.
- State: error credentials, inactive user, throttled request, loading submit.
- Target UI: split-panel login seperti `antre-in`, tetapi copy/branding Netivo.

### `errors/403.blade.php`, `errors/404.blade.php`

- Layout: `layouts.error`.
- Isi: kode error, pesan error, tombol kembali.
- Target UI: utility card Otika dengan empty state yang konsisten.

### `welcome.blade.php`

- Halaman fallback/default Laravel.
- Tidak menjadi bagian utama aplikasi karena `/` langsung redirect ke `dashboard` atau `login`.
- Dapat dipertahankan minimal atau dihapus setelah dipastikan tidak dipakai oleh route/controller lain.

### `receipts/pdf.blade.php`

- Bukan halaman UI Otika; dipakai untuk output PDF/e-receipt.
- Route pemanggil: `admin.receipts.show`, `supervisor.receipts.show`, `customer.receipts.show`.
- Isi: perusahaan, customer, tagihan, layanan, periode, metode, jumlah bayar, status lunas.
- Harus dipisahkan dari reset UI web; styling print/PDF tidak boleh tercampur dengan CSS aplikasi.

## 4. Dashboard pages

### `dashboard/admin.blade.php`

- Route: `admin.dashboard` (`/admin/dashboard`), `DashboardController@admin`.
- Role: admin.
- KPI: total tagihan, lunas, belum bayar, terlambat, menunggu verifikasi, pendapatan hari ini, pendapatan bulan ini, payment pending, upgrade pending.
- Panel: ringkasan periode, payment pending, upgrade pending, 10 transaksi terbaru.
- Aksi: link ke daftar payment, daftar upgrade, dan transaksi.
- Partial: `dashboard/partials/stat-card`, `dashboard/partials/transactions`.
- State: KPI kosong menampilkan “Belum ada data”; empty state pending/payment/upgrade.

### `dashboard/supervisor.blade.php`

- Route: `supervisor.dashboard` (`/supervisor/dashboard`), `DashboardController@supervisor`.
- Role: supervisor.
- KPI: total tagihan, lunas, belum bayar, terlambat, menunggu verifikasi, pendapatan hari ini, pendapatan bulan ini.
- Panel: laporan harian yang menunggu review.
- Aksi: buka semua laporan atau detail laporan.
- State: empty state saat tidak ada laporan pending.

### `dashboard/customer.blade.php`

- Route: `customer.dashboard` (`/customer/dashboard`), `DashboardController@customer`.
- Role: customer.
- Alert: jumlah tagihan terlambat.
- Panel utama: tagihan periode berjalan dengan nomor, layanan, jumlah, jatuh tempo, status.
- Aksi: detail tagihan, bayar sekarang bila status belum bayar/terlambat.
- Panel layanan: layanan aktif, harga/kecepatan, status upgrade approved/pending.
- Panel tambahan: lima pembayaran terakhir dan notifikasi terbaru.
- State: tidak ada tagihan, layanan belum tersedia, tidak ada pembayaran/notifikasi.

### `dashboard/placeholder.blade.php`

- Fallback dashboard dengan `roleLabel`.
- Isi: welcome card dan pesan bahwa dashboard role belum memiliki data.
- Kandidat untuk dipertahankan sebagai fallback utility atau dihapus jika semua role selalu memakai dashboard spesifik.

## 5. Admin pages

Semua halaman admin menggunakan middleware `auth` + `role:admin`.

### Master data

#### `admin/services/index.blade.php`

- Route: `admin.services.index`, `.store`, `.update`, `.destroy`.
- Controller: `Admin\ServiceController`.
- Data: nama layanan, kecepatan Mbps, harga, deskripsi, status aktif, jumlah customer/tagihan.
- UI: table + modal create/edit/delete.
- Fitur: DataTables, status badge, edit, delete hanya jika belum dipakai.
- State: empty table, validasi modal, flash redirect.

#### `admin/payment-methods/index.blade.php`

- Route: `admin.payment-methods.index`, `.store`, `.update`, `.destroy`.
- Controller: `Admin\PaymentMethodController`.
- Data: tipe transfer/e-wallet, nama, nomor rekening/akun, atas nama, status, jumlah penggunaan.
- UI: table + modal create/edit/delete.
- Fitur: checkbox aktif pada edit, delete hanya jika belum memiliki payment, DataTables.

#### `admin/customers/index.blade.php`

- Route: `admin.customers.index`, `.store`, `.update`, `.destroy`, `.reset-password`.
- Controller: `Admin\CustomerController`.
- Data: nomor customer, nama, email, phone, layanan, status.
- UI: table + modal create, edit, reset password, delete.
- Create: nama, email, password awal, nomor HP, layanan, status, alamat.
- Edit: nama, email, nomor HP, layanan, status, alamat.
- Fitur: generate nomor customer, membuat billing awal, reset password, proteksi customer yang sudah memiliki tagihan, DataTables.

### Billing dan payment

#### `admin/bills/index.blade.php`

- Route: `admin.bills.index`, `admin.bills.generate`.
- Controller: `Admin\BillController`.
- Data: daftar tagihan dengan pencarian/filter yang disediakan controller.
- Aksi: generate tagihan periode dan buka detail.
- State: list kosong, filter/search, flash hasil generate.

#### `admin/bills/show.blade.php`

- Route: `admin.bills.show` (`/admin/bills/{bill}`).
- Controller: `Admin\BillController@show`.
- Isi: detail tagihan, customer, layanan, nominal, periode, tanggal jatuh tempo, status, daftar payment.
- Aksi: melihat detail payment/proof bila tersedia.

#### `admin/payments/index.blade.php`

- Route: `admin.payments.index`, query status.
- Controller: `Admin\PaymentController@index`.
- Data: payment customer, tagihan, jumlah, metode, tanggal, status.
- Fitur: filter status (`all`, `pending`, `confirmed`, `rejected`), buka detail, DataTables/empty state sesuai implementasi saat ini.

#### `admin/payments/show.blade.php`

- Route: `admin.payments.show`, `admin.payments.proof`, `admin.payments.confirm`, `admin.payments.reject`.
- Controller: `Admin\PaymentController` + `PaymentProofController`.
- Isi: detail customer/tagihan/payment, bukti pembayaran, status, receipt.
- Aksi: konfirmasi atau tolak payment; reject membutuhkan alasan; buka bukti dan e-receipt.
- State: aksi hanya tersedia sesuai status/authorization.

### Reports, upgrades, settings, logs

#### `admin/reports/index.blade.php`

- Route: `admin.reports.index`, `.store`.
- Controller: `Admin\DailyReportController`.
- Data/filter: status dan tanggal.
- Aksi: buat & kirim laporan harian melalui modal/form, buka detail.
- DataTables: tersedia dengan tombol copy/csv/excel/pdf/print.

#### `admin/reports/show.blade.php`

- Route: `admin.reports.show`, `.resend`.
- Isi: ringkasan confirmed/rejected/pending, status, transaksi terkait.
- Aksi: resend laporan jika sesuai status.

#### `admin/upgrades/index.blade.php`

- Route: `admin.upgrades.index`.
- Controller: `Admin\ServiceUpgradeController@index`.
- Data/filter: request upgrade layanan customer, status.
- Aksi: buka detail request.

#### `admin/upgrades/show.blade.php`

- Route: `admin.upgrades.show`, `.approve`, `.reject`.
- Isi: customer, layanan asal/tujuan, periode efektif, status, catatan.
- Aksi: approve atau reject upgrade; reject membutuhkan catatan/alasan.

#### `admin/settings/index.blade.php`

- Route: `admin.settings.index`, `admin.settings.update`.
- Controller: `Admin\SettingsController`.
- Isi: pengaturan perusahaan/billing yang dibaca `SettingService`.
- Fitur: update normal dengan CSRF, validation, flash success/error.

#### `admin/cron-logs/index.blade.php`

- Route: `admin.cron-logs.index`.
- Controller: `Admin\CronLogController@index`.
- Data: job, status success/partial/failed, summary JSON, waktu mulai/selesai.
- UI: table paginated, badge status, empty state.

#### `system-logs/index.blade.php`

- Route admin: `admin.system-logs.index`; route supervisor: `supervisor.system-logs.index`.
- Controller: `SystemLogController@index`.
- Data/filter: tanggal dari/sampai, user, action.
- Table: waktu, user/sistem, action, tabel/record, method, URL, snapshot old/new.
- Fitur: DataTables, expandable `<details>` untuk snapshot, pagination.
- Sensitivity: snapshot tetap harus sanitized; jangan menampilkan password/token/secret.

## 6. Supervisor pages

Semua halaman supervisor menggunakan middleware `auth` + `role:supervisor`.

### `supervisor/bills/index.blade.php`

- Route: `supervisor.bills.index`.
- Controller: `Supervisor\BillController@index`.
- Data: daftar tagihan read-only, pencarian/filter query, customer, layanan, nominal, periode, status.
- Aksi: buka detail tagihan.

### `supervisor/bills/show.blade.php`

- Route: `supervisor.bills.show`.
- Controller: `Supervisor\BillController@show`.
- Isi: detail tagihan/customer/service/payment.
- Aksi: lihat payment/proof/receipt sesuai link yang tersedia.
- Tidak ada aksi mutasi billing.

### `supervisor/payments/index.blade.php`

- Route: `supervisor.payments.index`.
- Controller: `Supervisor\PaymentController@index`.
- Data: payment read-only.
- Filter: semua, pending, confirmed, rejected.
- Aksi: detail payment.

### `supervisor/payments/show.blade.php`

- Route: `supervisor.payments.show`, `supervisor.payments.proof`, `supervisor.receipts.show`.
- Isi: customer, tagihan, layanan, jumlah, metode, status, proof.
- Aksi: buka bukti dan e-receipt jika tersedia.
- Tidak ada confirm/reject.

### `supervisor/reports/index.blade.php`

- Route: `supervisor.reports.index`.
- Controller: `Supervisor\DailyReportController@index`.
- Filter: status, tanggal mulai, tanggal selesai.
- Table: laporan, source, status, confirmed, rejected, pending.
- Fitur: DataTables + export buttons, buka detail.

### `supervisor/reports/show.blade.php`

- Route: `supervisor.reports.show`, `.archive`, `.revision`.
- Isi: ringkasan laporan, transaksi, revision note.
- Aksi jika status `dikirim`: setujui & arsipkan atau minta revisi melalui modal.
- State: laporan archived/revised harus read-only sesuai workflow.

## 7. Customer pages

Semua halaman customer menggunakan middleware `auth` + `role:customer` dan ownership policy di controller/policy.

### `customer/bills/index.blade.php`

- Route: `customer.bills.index`.
- Controller: `Customer\BillController@index`.
- Data: tagihan milik customer, periode, nominal, due date, status.
- Aksi: detail dan bayar untuk status yang memenuhi syarat.
- State: empty billing list, overdue indicator.

### `customer/bills/show.blade.php`

- Route: `customer.bills.show`.
- Controller: `Customer\BillController@show`.
- Isi: detail tagihan, layanan, customer, nominal, jatuh tempo, status, payment history.
- Aksi: menuju halaman bayar jika belum bayar/terlambat.

### `customer/bills/pay.blade.php`

- Route GET: `customer.bills.pay`.
- Controller: `Customer\BillController@pay`.
- Isi: ringkasan tagihan yang akan dibayar, pilihan metode pembayaran, nominal, upload proof.
- Submit: `customer.payments.store` (`POST /customer/bills/{bill}/payments`).
- Fitur: CSRF, validasi file proof, ownership check, status billing, transaction, notifikasi.
- State: invalid file type/size, payment pending, bill already paid.

### `customer/payments/index.blade.php`

- Route: `customer.payments.index`.
- Controller: `Customer\PaymentController@index`.
- Data: histori payment milik customer, tagihan, tanggal, jumlah, metode, status.
- Aksi: buka proof dan e-receipt jika tersedia.

### `customer/services/index.blade.php`

- Route GET: `customer.services.index`.
- Submit: `customer.services.upgrades.store` (`POST /customer/services/upgrades`).
- Controller: `Customer\ServiceController`.
- Isi: layanan aktif, pilihan layanan tujuan, harga/kecepatan, upgrade request.
- Fitur: hanya satu pending upgrade, effective period, ownership, transaction, notifier.
- State: active upgrade, pending request, approved/rejected, no alternative service.

## 8. Shared/detail pages

### `bills/index.blade.php`, `bills/show.blade.php`

- View shared/non-role-specific untuk daftar/detail bill jika dipanggil dari flow legacy atau route tambahan.
- Harus dicek ulang terhadap `routes/web.php` sebelum reset karena route utama saat ini menggunakan `admin.*`, `supervisor.*`, dan `customer.*`.
- Jangan dihapus sebelum seluruh referensi route/controller/policy dipastikan tidak memakainya.

### `notifications/index.blade.php`

- Route: `notifications.index`, `notifications.read`, `notifications.read-all`.
- Controller: `NotificationController`.
- Isi: daftar notifikasi milik user, title, message, waktu, status belum dibaca.
- Aksi: buka/tandai satu notifikasi, tandai semua dibaca.
- State: empty notification, pagination, unread highlight.

### `profile/show.blade.php`

- Route: `profile`, `password.update`.
- Controller: `ProfileController`.
- Isi: informasi akun dan form ubah password.
- Fitur: current password, password baru, confirmation, validation, flash message.
- Tidak boleh menampilkan password/token.

## 9. Route non-view / infrastructure

Route berikut tidak menghasilkan Blade page, tetapi harus tetap ada saat UI di-reset:

| Route | Fungsi |
|---|---|
| `GET /` | Redirect guest ke login atau user ke dashboard |
| `GET otika-fonts/{font}` | Proxy font Otika same-origin |
| `POST cron/generate-bills` | Generate billing melalui token cron |
| `POST cron/mark-overdue` | Tandai billing terlambat |
| `POST cron/daily-report` | Generate daily report |
| `POST logout` | Logout + audit |
| `GET */payments/{payment}/proof` | Binary file proof, bukan Blade |
| `GET */receipts/{receipt}` | PDF/e-receipt response |

## 10. Matriks navigasi role

| Menu | Admin | Supervisor | Customer |
|---|:---:|:---:|:---:|
| Dashboard | Ya | Ya | Ya |
| Layanan | Ya | Tidak | Lihat layanan sendiri |
| Metode Pembayaran | Ya | Tidak | Tidak |
| Customer | Ya | Tidak | Tidak |
| Tagihan | Ya | Read-only | Milik sendiri |
| Verifikasi Pembayaran | Ya | Read-only | Riwayat sendiri |
| Pengaturan | Ya | Tidak | Tidak |
| Log Cron | Ya | Tidak | Tidak |
| Audit Log | Ya | Ya | Tidak |
| Laporan Harian | Ya | Review | Tidak |
| Upgrade Layanan | Ya | Tidak | Ajukan sendiri |
| Notifikasi | Ya | Ya | Ya |
| Profil | Ya | Ya | Ya |

## 11. Catatan reset yang aman

Urutan reset UI yang direkomendasikan setelah dokumen ini disetujui:

1. Backup/commit kondisi saat ini.
2. Jangan mengubah controller, model, migration, policy, service, atau route kecuali ada kebutuhan akibat perubahan nama view.
3. Salin struktur layout/partial Otika dari `antre-in` terlebih dahulu.
4. Hapus atau kosongkan override visual Netivo lama setelah semua referensi CSS dicatat.
5. Migrasikan halaman per kelompok: layout/auth → dashboard → admin master → admin transaction → supervisor → customer → utility/PDF.
6. Setelah tiap kelompok, jalankan view cache, test terkait, route list, dan render browser pada 375px, 768px, 1024px, dan desktop.
7. Hapus view legacy hanya setelah `rg` memastikan tidak ada `view()`, `include()`, `extends()`, route, test, atau controller yang masih merujuknya.

## 12. Checklist penerimaan UI baru

- [ ] Header, sidebar, footer, card, modal, table, empty state sama pola dengan `antre-in`.
- [ ] Hanya menu sidebar yang berbeda sesuai domain Netivo.
- [ ] Tidak ada `data-bs-*`, `btn-close`, `me-*`, `ms-*`, `data-kt-*`, atau Metronic wrapper.
- [ ] Tidak ada CSS global baru yang mengubah geometri Otika tanpa alasan.
- [ ] Semua form memiliki label, id, name, CSRF, old value, validation, dan feedback.
- [ ] Semua tabel memiliki responsive wrapper dan state kosong.
- [ ] Semua aksi dilindungi middleware/policy/controller authorization.
- [ ] Semua modal Bootstrap 4 diuji ketika diletakkan di dalam/luar card.
- [ ] Tidak ada asset 404, icon font kosong, duplicate plugin, atau error console.
- [ ] `php artisan test`, `php artisan route:list`, `php artisan view:cache`, dan `git diff --check` lulus.
