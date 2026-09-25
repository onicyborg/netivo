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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kredensial berikut khusus untuk data demo development, bukan production.
        $password = 'Qwerty123*';

        DB::transaction(function () use ($password): void {
            User::updateOrCreate(
                ['email' => 'admin@example.com'],
                ['name' => 'Admin Netivo', 'password' => $password, 'role' => UserRole::ADMIN, 'is_active' => true],
            );

            User::updateOrCreate(
                ['email' => 'supervisor@example.com'],
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

            foreach (range(1, 10) as $index) {
                $serviceNames = ['Netivo Basic', 'Netivo Family', 'Netivo Pro'];
                $serviceName = $serviceNames[($index - 1) % count($serviceNames)];
                $data = [
                    'name' => 'Customer Demo '.$index,
                    'email' => 'customer'.$index.'@example.com',
                    'phone' => '081200000'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'service' => $serviceName,
                ];
                $user = User::updateOrCreate(
                    ['email' => $data['email']],
                    ['name' => $data['name'], 'password' => $password, 'role' => UserRole::CUSTOMER, 'is_active' => true],
                );

                Customer::updateOrCreate(
                    ['user_id' => $user->id],
                    ['service_id' => $services[$data['service']]->id, 'customer_number' => sprintf('CUST-%06d', $index), 'phone' => $data['phone'], 'address' => 'Alamat customer demo '.$data['name'], 'registered_at' => today(), 'status' => CustomerStatus::AKTIF],
                );
            }
        });
    }
}
