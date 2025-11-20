<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '12345678',
                'is_admin' => false,
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => '12345678',
                'is_admin' => true,
            ],
        ];

        foreach ($usersData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                ]
            );

            // ایجاد توکن
            $token = $user->createToken('api-token')->plainTextToken;

            // چاپ توکن در ترمینال
            echo "User: {$user->email} | Token: {$token}\n";
        }
    }
}
