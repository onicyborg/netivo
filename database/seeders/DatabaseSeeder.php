<?php

namespace Database\Seeders;

use App\Enums\CustomerStatus;
use App\Enums\PaymentMethodType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = env('SEEDER_DEFAULT_PASSWORD');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('SEEDER_DEFAULT_PASSWORD harus diisi di .env lokal sebelum menjalankan seeder.');
        }

        DB::transaction(function () use ($password): void {
            User::updateOrCreate(
                ['email' => 'admin@netivo.test'],
                ['name' => 'Admin Netivo', 'password' => $password, 'role' => UserRole::ADMIN, 'is_active' => true],
            );

            User::updateOrCreate(
                ['email' => 'supervisor@netivo.test'],
                ['name' => 'Supervisor Netivo', 'password' => $password, 'role' => UserRole::SUPERVISOR, 'is_active' => true],
            );

            $services = collect([
                ['name' => 'Netivo Basic', 'speed_mbps' => 10, 'price' => 150000, 'description' => 'Paket internet dasar untuk kebutuhan rumah.'],
                ['name' => 'Netivo Family', 'speed_mbps' => 30, 'price' => 250000, 'description' => 'Paket internet keluarga untuk banyak perangkat.'],
                ['name' => 'Netivo Pro', 'speed_mbps' => 100, 'price' => 450000, 'description' => 'Paket internet berkecepatan tinggi untuk kebutuhan profesional.'],
            ])->mapWithKeys(fn (array $data): array => [$data['name'] => Service::updateOrCreate(['name' => $data['name']], $data)]);

            PaymentMethod::updateOrCreate(
                ['type' => PaymentMethodType::TRANSFER, 'name' => 'Bank BCA'],
                ['account_number' => '0000000000', 'account_name' => 'PT Netivo Placeholder', 'is_active' => true],
            );
            PaymentMethod::updateOrCreate(
                ['type' => PaymentMethodType::EWALLET, 'name' => 'GoPay'],
                ['account_number' => '0800000000', 'account_name' => 'PT Netivo Placeholder', 'is_active' => true],
            );

            foreach ([
                'bill_due_day' => '10',
                'company_name' => 'PT Netivo Placeholder',
                'company_address' => 'Alamat perusahaan placeholder',
                'company_phone' => '0210000000',
            ] as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }

            foreach ([
                ['name' => 'Andi Demo', 'email' => 'andi@netivo.test', 'phone' => '081200000001', 'service' => 'Netivo Basic'],
                ['name' => 'Budi Demo', 'email' => 'budi@netivo.test', 'phone' => '081200000002', 'service' => 'Netivo Family'],
                ['name' => 'Citra Demo', 'email' => 'citra@netivo.test', 'phone' => '081200000003', 'service' => 'Netivo Pro'],
            ] as $index => $data) {
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'password' => $password, 'role' => UserRole::CUSTOMER, 'is_active' => true],
                );

                Customer::updateOrCreate(
                    ['user_id' => $user->id],
                    ['service_id' => $services[$data['service']]->id, 'customer_number' => sprintf('CUST-%06d', $index + 1), 'phone' => $data['phone'], 'address' => 'Alamat customer demo '.$data['name'], 'registered_at' => today(), 'status' => CustomerStatus::AKTIF],
                );
            }
        });
    }
}
