# PRD — Sistem Pembayaran Billing Internet (Berbasis Web)

Dokumen ini adalah sumber kebenaran fungsional. Aturan teknis ada di `AGENTS.md`, aturan UI ada di `design.md`. Jika ada konflik antara PRD ini dan `AGENTS.md` untuk hal teknis (stack, konvensi kode), `AGENTS.md` menang.

---

## 1. Ringkasan

Aplikasi web untuk mengelola tagihan internet bulanan dengan tiga peran: **Customer**, **Admin**, **Supervisor**.

- Tagihan dibuat **otomatis tiap bulan** lewat endpoint publik bertoken yang dipanggil oleh https://cron-job.org.
- Customer membayar secara **manual** (transfer/e-wallet), mengunggah bukti, lalu **admin memverifikasi**.
- Sistem menerbitkan **e-receipt**, mengirim notifikasi **in-app**, dan membuat **laporan transaksi harian** yang ditinjau supervisor.

## 2. Tujuan & Non-Tujuan

**Tujuan**
1. Menghilangkan pembuatan tagihan manual (generate otomatis, idempotent).
2. Alur verifikasi pembayaran yang jelas dan teraudit.
3. Customer dapat melihat tagihan, membayar, dan mengunduh bukti sendiri.
4. Supervisor dapat mengawasi transaksi harian.

**Non-Tujuan (di luar scope versi ini)**
- Virtual account, payment gateway, webhook pembayaran otomatis.
- Notifikasi email/WhatsApp (dirancang agar bisa ditambah kemudian).
- Denda keterlambatan, pro-rata, suspend/putus layanan otomatis.
- Pendaftaran mandiri customer (akun dibuat admin).
- Lebih dari satu layanan per customer.
- Postpaid (lihat 5.1, cukup ubah kode bila nanti dibutuhkan).

## 3. Peran & Hak Akses

| Kemampuan | Customer | Admin | Supervisor |
|---|:-:|:-:|:-:|
| Login, ubah password sendiri | ✔ | ✔ | ✔ |
| Dashboard sendiri | ✔ | ✔ | ✔ |
| Lihat tagihan & riwayat **miliknya** | ✔ | – | – |
| Upload bukti bayar | ✔ | – | – |
| Unduh e-receipt **miliknya** | ✔ | ✔ (semua) | ✔ (semua, read-only) |
| Ajukan upgrade layanan | ✔ | – | – |
| CRUD customer, layanan, metode pembayaran | – | ✔ | – |
| Pengaturan sistem (jatuh tempo, info perusahaan) | – | ✔ | – |
| Verifikasi pembayaran (konfirmasi/tolak) | – | ✔ | – |
| Approve/tolak upgrade | – | ✔ | – |
| Generate tagihan manual | – | ✔ | – |
| Buat/kirim laporan harian | – | ✔ | – |
| Review, setujui, minta revisi laporan | – | – | ✔ |
| Lihat daftar tagihan & pembayaran (read-only) | – | ✔ | ✔ |
| Lihat system logs | – | ✔ | ✔ (read-only) |

Aturan: hak akses divalidasi di **server** (middleware role + Policy). Customer tidak boleh mengakses data customer lain (cegah IDOR).

## 4. Keputusan Produk (final)

| # | Keputusan |
|---|---|
| D1 | Akun customer dibuat oleh admin. |
| D2 | Pembayaran manual: metode **transfer** dan **e-wallet**, diverifikasi admin. Tanpa virtual account. |
| D3 | Tiap role punya dashboard sendiri. Data admin dan supervisor hampir sama. |
| D4 | Notifikasi hanya **in-app**. |
| D5 | Tagihan **prabayar**: tagihan periode berjalan terbit di awal bulan. |
| D6 | Tanpa denda. Tanpa pro-rata (customer baru membayar penuh periode berjalan). |
| D7 | 1 customer = 1 layanan, diisi admin saat mendaftarkan customer. |
| D8 | Upgrade layanan lewat request customer + approval admin. **Berlaku mulai periode tagihan berikutnya** (opsi A). |
| D9 | Jatuh tempo default **tanggal 10**, dibuat **dinamis** lewat pengaturan (`bill_due_day`, 1–28). Nilai disalin ke `bills.due_date` saat tagihan dibuat. |
| D10 | Tagihan belum bayar yang lewat jatuh tempo berstatus **`terlambat`**. |
| D11 | Laporan harian dibuat manual oleh admin, **atau otomatis oleh cron** jika hari itu belum ada laporan. |
| D12 | Bukti pembayaran **wajib** diunggah (karena semua verifikasi manual). |

## 5. Aturan Bisnis

### 5.1 Periode & tagihan
- Format periode: `YYYY-MM` (contoh `2026-10`).
- Satu customer hanya boleh punya **satu** tagihan per periode (unique `customer_id + period`).
- Tagihan dibuat untuk customer berstatus `aktif` saja.
- `amount` tagihan adalah **snapshot** harga layanan saat tagihan dibuat. Perubahan harga layanan tidak mengubah tagihan yang sudah ada.
- `service_id` pada tagihan juga disalin (snapshot).
- `due_date` = tanggal `bill_due_day` pada bulan periode tersebut.
- Nomor tagihan: `INV-YYYYMM-NNNN` (urut per periode). Dibuat di dalam transaction, dengan unique constraint dan retry saat bentrok.
- Prabayar: cron berjalan tanggal 1. Jika nanti ingin postpaid, yang diubah hanya logika penentuan periode dan `due_date` di `BillGenerator`.

### 5.2 Customer baru di tengah bulan
Saat admin membuat customer (status aktif), sistem **langsung membuat tagihan periode berjalan** memakai `BillGenerator` yang sama (idempotent). Tanpa pro-rata, nominal penuh.

### 5.3 Status tagihan

`belum_bayar` · `menunggu_verifikasi` · `lunas` · `terlambat`

| Dari | Ke | Pemicu |
|---|---|---|
| (baru) | `belum_bayar` | Tagihan dibuat |
| `belum_bayar` | `terlambat` | Cron harian: `due_date < hari ini` |
| `belum_bayar`/`terlambat` | `menunggu_verifikasi` | Customer upload bukti (membuat `payments.pending`) |
| `menunggu_verifikasi` | `lunas` | Admin konfirmasi |
| `menunggu_verifikasi` | `belum_bayar` atau `terlambat` | Admin tolak. Dipilih berdasar `due_date` vs hari ini |

Aturan tambahan:
- Hanya boleh ada **satu payment `pending`** per tagihan.
- Tagihan `lunas` tidak dapat dibayar lagi maupun diubah.
- Tagihan `terlambat` tetap dapat dibayar, nominal tidak berubah.

### 5.4 Status pembayaran
`pending` → `confirmed` atau `rejected`. Penolakan **wajib** menyertakan alasan. Payment yang ditolak tetap tersimpan sebagai riwayat, customer mengunggah ulang sebagai payment baru.

### 5.5 Upgrade layanan (opsi A)
1. Customer memilih layanan tujuan (layanan aktif, berbeda dari layanan sekarang). Maksimal **satu request pending** per customer.
2. Admin approve atau tolak (tolak wajib alasan).
3. Saat approve: `status = approved`, `effective_period` = periode setelah bulan approve. `customers.service_id` **belum** berubah.
4. Saat cron generate tagihan periode `P` berjalan: sebelum membuat tagihan, semua request `approved` dengan `effective_period <= P` diterapkan (`customers.service_id` diganti, status request menjadi `applied`), lalu tagihan dibuat dengan harga layanan baru.
5. Tagihan periode berjalan tidak pernah berubah karena upgrade.
6. Asumsi: perpindahan ke layanan yang lebih murah (downgrade) juga diperbolehkan lewat alur yang sama. Ubah bila tidak diinginkan.

### 5.6 Laporan harian
- Satu laporan per tanggal (`report_date` unik).
- Isi: jumlah dan total nominal pembayaran `confirmed` pada tanggal tersebut, jumlah `rejected`, jumlah `pending` (informasi), serta daftar transaksi (dihitung dari tabel `payments` berdasarkan `verified_at`).
- Sumber: `manual` (admin klik "Buat & Kirim") atau `cron` (otomatis jika belum ada laporan hari itu).
- Status: `dikirim` → supervisor **setujui** → `diarsipkan`. Atau `dikirim` → supervisor **minta revisi** (catatan wajib) → `revisi` → admin memperbaiki (hitung ulang) dan **kirim ulang** → `dikirim`.
- Laporan `diarsipkan` bersifat read-only.
- Cron dijalankan malam hari sehingga admin sempat membuat laporan manual lebih dulu. Jika sudah ada, cron tidak melakukan apa pun.

### 5.7 Customer nonaktif
Admin dapat menonaktifkan customer. Customer nonaktif tidak dibuatkan tagihan baru dan tidak bisa login. Tagihan lama tetap ada.

## 6. Fitur & Acceptance Criteria

### F1. Autentikasi & Profil
- Login email + password, redirect ke dashboard sesuai role.
- Logout, ubah password sendiri (validasi password lama).
- Throttle percobaan login.
- **AC:** user tanpa role yang sesuai mendapat 403 di route role lain. Customer nonaktif ditolak login dengan pesan jelas.

### F2. Pengaturan Sistem (admin)
- `bill_due_day` (1–28, default 10), `company_name`, `company_address`, `company_phone` (dipakai di e-receipt).
- **AC:** mengubah `bill_due_day` hanya memengaruhi tagihan yang dibuat sesudahnya.

### F3. Master Layanan (admin)
- CRUD via modal: nama, kecepatan (Mbps), harga, deskripsi, aktif/nonaktif.
- **AC:** layanan yang dipakai customer atau tagihan tidak dapat dihapus (hanya dinonaktifkan).

### F4. Master Metode Pembayaran (admin)
- CRUD via modal: tipe (`transfer`/`ewallet`), nama (Bank/E-wallet), nomor rekening/akun, atas nama, aktif/nonaktif.
- **AC:** customer hanya melihat metode aktif.

### F5. Master Customer (admin)
- CRUD via modal: nama, email, password awal, no. HP, alamat, layanan, status. `customer_number` dibuat otomatis (`CUST-NNNNNN`), unik, tidak bisa diubah.
- Pembuatan user + customer + tagihan awal berada dalam **satu transaction**.
- Admin dapat reset password customer.
- **AC:** membuat customer di tengah bulan otomatis menghasilkan tagihan periode berjalan. Email unik.

### F6. Generator Tagihan (`BillGenerator`) — inti
- Class service yang dipakai oleh: endpoint cron, tombol "Generate Manual" admin, dan pembuatan customer baru.
- Langkah: terapkan upgrade `approved` yang jatuh tempo efektif, ambil customer aktif (chunk), `firstOrCreate` per `(customer, period)`, kirim notifikasi "Tagihan baru", kembalikan ringkasan `{created, skipped, failed}`.
- **AC:** dijalankan dua kali pada periode yang sama tidak membuat data ganda. Kegagalan satu customer tidak menggagalkan yang lain, tercatat di `cron_logs`.

### F7. Penanda Terlambat (`OverdueMarker`)
- Mengubah `belum_bayar` dengan `due_date < hari ini` menjadi `terlambat`. Idempotent.
- **AC:** tagihan `menunggu_verifikasi` dan `lunas` tidak tersentuh.

### F8. Daftar Tagihan (admin/supervisor)
- DataTables: filter periode, status, pencarian nomor tagihan/customer. Detail tagihan memuat riwayat payment.
- Admin: tombol generate manual untuk periode tertentu.

### F9. Sisi Customer
- **Dashboard**: tagihan periode berjalan (status, jumlah, jatuh tempo), layanan aktif, info upgrade terjadwal/pending, 5 pembayaran terakhir, notifikasi terbaru.
- **Pembayaran Tagihan**: daftar tagihan yang belum lunas → detail (nomor tagihan, periode, layanan, jumlah, jatuh tempo).
  Alur diagram "Informasi sudah sesuai? Tidak" cukup dengan kembali ke daftar. Jika tagihan tidak sesuai, customer menghubungi admin. Tampilkan teks bantuan.
- **Bayar**: pilih metode aktif, lihat instruksi (nomor rekening/akun), unggah bukti (jpg/png/pdf, maks 2 MB), tanggal bayar, nama pengirim opsional, catatan opsional.
- **Riwayat**: semua tagihan + status, semua payment (termasuk alasan penolakan).
- **E-receipt**: lihat dan unduh PDF untuk pembayaran `confirmed`.
- **Upgrade layanan**: form pilih layanan tujuan, status request.
- **AC:** customer tidak dapat membuka tagihan/bukti/receipt milik customer lain (403/404). Upload ganda saat payment `pending` ditolak dengan pesan.

### F10. Verifikasi Pembayaran (admin)
- Daftar payment `pending` (badge jumlah di sidebar/dashboard).
- Halaman detail: data payment, bukti (pratinjau), data customer & tagihan berdampingan (ini "mencocokkan dengan data pelanggan dan tagihan").
- **Konfirmasi** (satu transaction): payment → `confirmed` (`verified_by`, `verified_at`), tagihan → `lunas`, buat receipt, notifikasi ke customer.
- **Tolak**: alasan wajib, payment → `rejected`, tagihan kembali ke `belum_bayar`/`terlambat`, notifikasi ke customer.
- **AC:** konfirmasi dua kali (double click/race) tidak membuat receipt ganda (lock row + cek status).

### F11. E-Receipt
- Dibuat otomatis saat konfirmasi. Nomor `RCP-YYYYMMDD-NNNN` (unik).
- PDF berisi: info perusahaan, nomor receipt, tanggal, data customer, layanan, periode, jumlah, metode, status LUNAS.
- Akses via route ber-otorisasi (bukan URL publik).

### F12. Notifikasi In-App
- Tabel `notifications`, ikon lonceng di navbar dengan jumlah belum dibaca, halaman daftar notifikasi, tandai dibaca (satu/semua). Klik membuka `url` terkait.
- Trigger:

| Kejadian | Penerima |
|---|---|
| Tagihan baru terbit | Customer |
| Bukti bayar diunggah | Semua admin |
| Pembayaran dikonfirmasi | Customer |
| Pembayaran ditolak | Customer |
| Request upgrade diajukan | Semua admin |
| Upgrade disetujui/ditolak/diterapkan | Customer |
| Laporan harian dikirim/dikirim ulang | Semua supervisor |
| Laporan diminta revisi | Semua admin |
| Laporan disetujui/diarsipkan | Semua admin |

- Dibuat lewat satu class/helper `Notifier` supaya kelak mudah ditambah kanal email.
- **AC:** user hanya melihat dan menandai notifikasi miliknya.

### F13. Laporan Harian
- Admin: daftar laporan, tombol **Buat & Kirim** (tanggal default hari ini), edit/kirim ulang saat `revisi`, lihat catatan revisi.
- Supervisor: daftar laporan, detail (ringkasan + daftar transaksi), **Setujui & Arsipkan**, **Minta Revisi** (catatan wajib).
- Arsip: filter tanggal/status, export sesuai Buttons DataTables Otika.
- Cron `DailyReportGenerator`: jika belum ada laporan pada tanggal hari itu, buat dengan `source = cron`, status `dikirim`.
- **AC:** tidak mungkin ada dua laporan pada tanggal yang sama. Laporan `diarsipkan` tidak dapat diubah.

### F14. Upgrade Layanan
- Sesuai 5.5. Admin punya daftar request (filter status) dengan aksi approve/tolak lewat modal.

### F15. Dashboard
- **Customer:** lihat F9.
- **Admin & Supervisor** (KPI `card-statistic-*`): total tagihan periode ini, jumlah lunas / belum bayar / terlambat / menunggu verifikasi, pendapatan hari ini dan bulan ini, 10 transaksi terbaru. Tambahan admin: payment pending, request upgrade pending. Tambahan supervisor: laporan menunggu review.
- Boleh memakai partial Blade bersama.

### F16. Audit Log (`system_logs`)
- Sesuai AGENTS.md: created, updated, deleted, assigned, unassigned, login, logout, plus aksi domain (confirm, reject, approve, revise, generate). Bersihkan password/token/secret dari snapshot.

## 7. Endpoint Cron (cron-job.org)

| Endpoint | Metode | Jadwal disarankan | Fungsi |
|---|---|---|---|
| `/cron/generate-bills` | POST | Tgl 1 pukul 00:05, plus harian 00:05 sebagai pengaman | `BillGenerator` untuk periode berjalan |
| `/cron/mark-overdue` | POST | Harian 00:10 | `OverdueMarker` |
| `/cron/daily-report` | POST | Harian 23:55 | `DailyReportGenerator` (hanya jika belum ada) |

Kebutuhan:
- Route di `routes/web.php`, **di luar** grup `auth`, dikecualikan dari CSRF (`validateCsrfTokens(except: ['cron/*'])` di `bootstrap/app.php`).
- Autentikasi: header `Authorization: Bearer <CRON_SECRET>`. Middleware alias `cron.token` (didaftarkan di `bootstrap/app.php`), perbandingan pakai `hash_equals`, token dari `.env` (`CRON_SECRET`), **tidak** dicatat di log. Tanpa token yang benar → 401.
- `throttle` (mis. 10/menit per IP).
- Response JSON: `{ "message": "...", "data": { "created": n, "skipped": n, "failed": n } }`.
- Setiap eksekusi dicatat ke `cron_logs` (job, status, ringkasan, durasi, waktu).
- Timezone aplikasi diset satu (mis. `Asia/Jakarta`) dan jadwal cron-job.org memakai timezone yang sama.
- Setiap job juga tersedia sebagai perintah Artisan (`bills:generate`, `bills:mark-overdue`, `reports:daily`) untuk pengujian lokal, memanggil class service yang sama.

## 8. Model Data

Semua tabel: PK `id` **UUID v7** (di Laravel 12 lewat trait `HasUuids`), semua FK bertipe UUID (`foreignUuid`). Timestamps standar. Gunakan `$fillable` eksplisit.

**users**: name, email (unik), password, role (`admin|supervisor|customer`), is_active, remember_token

**settings**: key (unik), value, (seeder: `bill_due_day=10`, info perusahaan)

**services**: name, speed_mbps, price (decimal 12,2 atau integer rupiah, tentukan satu dan konsisten), description, is_active

**customers**: user_id (unik), service_id, customer_number (unik), phone, address, registered_at, status (`aktif|nonaktif`)

**payment_methods**: type (`transfer|ewallet`), name, account_number, account_name, is_active

**bills**: bill_number (unik), customer_id, service_id, period (`YYYY-MM`), amount, due_date, status (`belum_bayar|menunggu_verifikasi|lunas|terlambat`), paid_at nullable. Unique `(customer_id, period)`. Index `(status, due_date)`.

**payments**: bill_id, payment_method_id, amount, paid_date, sender_name nullable, note nullable, proof_path, status (`pending|confirmed|rejected`), rejection_reason nullable, verified_by nullable (users), verified_at nullable. Index `(status)`, `(verified_at)`.

**receipts**: payment_id (unik), receipt_number (unik), issued_at

**service_upgrade_requests**: customer_id, from_service_id, to_service_id, status (`pending|approved|applied|rejected`), effective_period nullable, note nullable, reviewed_by nullable, reviewed_at nullable

**daily_reports**: report_date (unik), source (`manual|cron`), status (`dikirim|revisi|diarsipkan`), total_confirmed_count, total_confirmed_amount, rejected_count, pending_count, created_by nullable, reviewed_by nullable, revision_note nullable, sent_at, reviewed_at nullable, archived_at nullable

**notifications**: user_id, type, title, message, url nullable, read_at nullable. Index `(user_id, read_at)`.

**cron_logs**: job, status (`success|partial|failed`), summary (json), started_at, finished_at

**system_logs**: mengikuti AGENTS.md (user, tabel, record, method, URL, IP, old/new snapshot).

Penyimpanan bukti bayar: disimpan di **disk privat** (bukan `public`), diakses lewat route ber-otorisasi (`payments/{payment}/proof`), nama file di-random. E-receipt PDF di-render saat diminta dari data (tidak perlu menyimpan file).

## 9. Peta Route (ringkas)

Nama route dan struktur mengikuti AGENTS.md. Master data memakai `Route::resource(...)->except(['show','create','edit'])`.

**Publik**: `login`, `logout`, `POST /cron/generate-bills|mark-overdue|daily-report` (token).

**Umum (auth)**: `dashboard` (redirect per role), `profile`, `password.update`, `notifications.index|read|read-all`.

**Admin** (`/admin`): `settings`, `services`, `payment-methods`, `customers` (+ `customers.reset-password`), `bills` (+ `bills.generate`), `payments.index|show`, `payments.confirm|reject|proof`, `receipts.download`, `upgrade-requests.index` (+ `approve|reject`), `reports.index|store|resend`, `system-logs`.

**Supervisor** (`/supervisor`): `bills` (read-only), `payments` (read-only), `reports.index|show|approve|revise`, `system-logs`.

**Customer** (`/customer`): `bills.index|show`, `bills.pay` (form), `payments.store`, `payments.index`, `payments.proof`, `receipts.download`, `upgrade.index|store`.

## 10. Persyaratan Non-Fungsional

- **Keamanan:** CSRF, XSS (escape output), mass assignment, IDOR (Policy + scope query per customer), upload aman (validasi mime + ekstensi + ukuran, nama acak, disk privat), throttle login & cron, tidak mencatat secret. `CRON_SECRET` wajib ada di `.env.example` tanpa nilai nyata.
- **Integritas data:** operasi multi-tabel (buat customer, konfirmasi pembayaran, apply upgrade) dalam transaction. Row lock pada konfirmasi. Unique constraint sebagai pengaman terakhir.
- **Performa:** eager loading (cegah N+1), index sesuai bagian 8, chunk pada generate tagihan.
- **UI:** Otika + Bootstrap 4 sesuai `design.md` dan `AGENTS.md`. Loading, empty, success, error, dan permission state. Responsif di 375/768/1024/desktop.
- **Dependensi baru** yang dibolehkan karena kebutuhan nyata: library PDF untuk e-receipt (mis. `barryvdh/laravel-dompdf`). Tidak menambah library lain tanpa alasan.
- **Timezone & format:** `Asia/Jakarta` (atau sesuai keputusan), mata uang Rupiah (`Rp 1.500.000`), tanggal `dd MMM yyyy`, bahasa UI Indonesia.

## 11. Strategi Pengujian

Feature test minimum:
1. Cron: tanpa token 401, token salah 401, token benar 200. Idempotent (jalankan dua kali). Customer nonaktif dilewati. Upgrade `approved` diterapkan sebelum tagihan dibuat.
2. `OverdueMarker` hanya mengubah `belum_bayar` yang lewat jatuh tempo.
3. Snapshot harga: ubah harga layanan, tagihan lama tetap.
4. Pembuatan customer di tengah bulan menghasilkan tagihan periode berjalan.
5. Alur pembayaran: upload → `menunggu_verifikasi`, double upload ditolak, konfirmasi → `lunas` + receipt tunggal, tolak → status kembali sesuai `due_date`.
6. IDOR: customer A tidak dapat mengakses tagihan/bukti/receipt customer B.
7. Otorisasi role pada tiap grup route.
8. Laporan harian: satu per tanggal, cron tidak menimpa laporan manual, alur revisi dan arsip, arsip read-only.
9. Upgrade: satu pending per customer, `effective_period` benar, tagihan periode berjalan tidak berubah.

Quality gate: `php artisan test`, `php artisan route:list`, `git diff --check`, cek 404 asset, console browser bersih.

## 12. Fase Pengembangan

| Fase | Isi | Hasil utama |
|---|---|---|
| 0 | Fondasi proyek | Laravel 12, config asset Otika, timezone, `.env.example`, trait UUID |
| 1 | Skema data | Migration, model, factory, seeder |
| 2 | Auth, role, layout | Login, middleware role, layout Otika per role, sidebar |
| 3 | Master data & pengaturan | Layanan, metode bayar, customer, settings |
| 4 | Inti tagihan & cron | `BillGenerator`, `OverdueMarker`, endpoint cron, `cron_logs`, generate manual |
| 5 | Sisi customer | Tagihan, pembayaran, riwayat |
| 6 | Verifikasi & e-receipt | Konfirmasi/tolak, receipt PDF |
| 7 | Notifikasi in-app | `Notifier`, lonceng, halaman notifikasi |
| 8 | Laporan harian | Admin, supervisor, cron report |
| 9 | Upgrade layanan | Request, approval, penerapan lewat cron |
| 10 | Dashboard | Customer, admin, supervisor |
| 11 | Audit log & hardening | `system_logs`, pengujian akhir, QA responsif |

## 13. Asumsi & Hal Terbuka

- Downgrade diizinkan (bagian 5.5). Ubah bila tidak.
- `bill_due_day` dibatasi 1–28 untuk menghindari bulan pendek.
- Customer tidak menginput nominal, nominal selalu sama dengan tagihan.
- Refund/pembatalan pembayaran yang sudah `confirmed` tidak termasuk scope (bila perlu, tangani manual via database atau fitur lanjutan).
- Pemulihan password mandiri tidak ada, admin yang mereset.
