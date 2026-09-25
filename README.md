<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Konvensi

- Semua primary key aplikasi menggunakan UUID v7 dari trait `HasUuids` pada model.
- Migration tabel memakai `$table->uuid('id')->primary()` untuk primary key dan `$table->foreignUuid('...')` untuk foreign key UUID.
- Asset Otika dimuat langsung dengan helper `asset()` menggunakan `ASSET_URL`; proyek ini tidak menggunakan Vite.
- Format tampilan bersama tersedia melalui helper `format_rupiah()` dan `format_tanggal_id()`.

Seeder menyediakan akun demo development berikut. Password semua akun adalah `Qwerty123*`; akun dan password ini hanya untuk development dan tidak boleh digunakan pada deployment production:

- Admin: `admin@example.com`
- Supervisor: `supervisor@example.com`
- Customer: `customer1@example.com` sampai `customer10@example.com`

## Setup development

1. Jalankan `composer install`.
2. Salin `.env.example` menjadi `.env`, lalu jalankan `php artisan key:generate`.
3. Konfigurasikan PostgreSQL untuk development. Untuk deployment MySQL, ubah `DB_CONNECTION=mysql` dan isi host, port, database, username, serta password MySQL. Migration dan seeder tetap kompatibel dengan keduanya.
4. Jalankan `php artisan migrate`.
5. Jalankan `php artisan db:seed` untuk membuat akun admin, supervisor, 10 customer, layanan, metode pembayaran, dan settings demo.
6. Jalankan `php artisan storage:link` hanya untuk media publik aplikasi. Bukti pembayaran tetap disimpan di disk privat dan tidak boleh dipublikasikan.

Asset Otika tidak memakai Vite. `ASSET_URL` default di `.env.example` adalah `https://otika.namikulo.com/assets`; ubah hanya bila deployment memakai mirror asset yang kompatibel. File aplikasi dari storage publik menggunakan URL `/storage/...`, sedangkan asset Otika menggunakan `asset()`.

Untuk membuat secret cron secara acak, jalankan:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

Masukkan hasilnya ke `CRON_SECRET` pada environment server. Jika `CRON_SECRET` kosong, seluruh endpoint cron menolak request (fail closed). Jangan menaruh nilai secret asli di repository atau log.

## Cron-job.org

Buat tiga job dengan timezone `Asia/Jakarta`. Gunakan domain deployment sebagai pengganti `https://app.example.test` dan nilai secret disimpan di konfigurasi secret cron-job.org, bukan di URL.

| Job | URL | Metode | Header | Jadwal |
|---|---|---|---|---|
| Generate tagihan | `https://app.example.test/cron/generate-bills` | POST | `Authorization: Bearer <CRON_SECRET>` | Setiap hari 00:05 |
| Tandai terlambat | `https://app.example.test/cron/mark-overdue` | POST | `Authorization: Bearer <CRON_SECRET>` | Setiap hari 00:10 |
| Laporan harian | `https://app.example.test/cron/daily-report` | POST | `Authorization: Bearer <CRON_SECRET>` | Setiap hari 23:55 |

## Perintah Artisan

```bash
php artisan migrate
php artisan db:seed
php artisan test
php artisan bills:generate --period=2026-09
php artisan bills:mark-overdue
php artisan reports:daily
php artisan route:list
php artisan view:cache
```

## Audit log dan hardening

`AuditLogger` dan `AuditObserver` mencatat CRUD model domain serta login, logout, reset password, generate manual, konfirmasi/penolakan pembayaran, review laporan, dan workflow upgrade ke tabel `system_logs`. Snapshot otomatis menyamarkan password, token, secret, authorization, API key, dan data biner. Admin dan supervisor dapat membaca log melalui menu Audit Log; customer tidak memiliki akses.

Semua endpoint bisnis memakai middleware autentikasi dan role yang sesuai, dengan Policy untuk ownership dan aksi sensitif. Bukti pembayaran berada di disk privat, validasi upload memeriksa MIME/ekstensi/ukuran, login dan cron memiliki throttle, dan query listing memakai eager loading pada relasi yang ditampilkan.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
