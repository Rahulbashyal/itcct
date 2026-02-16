<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove dummy users from previous iterations
        User::whereNotIn('symbol_number', ['000000', '208201'])->delete();

        User::updateOrCreate(
            ['symbol_number' => '000000'],
            [
                'name' => 'Rahul Bashyal',
                'email' => 'rahul@abssoft.com.np',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'SuperAdmin',
                'is_hidden' => true,
            ]
        );

        $committee = [
            [
                'name' => 'Bishal Sharma Parajuli',
                'symbol_number' => '208201',
                'role' => 'President',
                'email' => 'president@itclubcct.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($committee as $member) {
            User::updateOrCreate(['symbol_number' => $member['symbol_number']], $member);
        }
        
        $this->command->info('Committee created successfully.');
    }
}
