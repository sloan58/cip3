<?php

use App\User;
use App\Models\Ucm;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'CIP3 Admin',
            'email' => 'admin@cip3.com',
            'password' => Hash::make('password123')
        ]);

        Ucm::create([
            'name' => 'CIP3 Dev',
            'ip_address' => '10.175.200.10',
            'username' => 'cip3-admin',
            'password' => 'password123',
            'timezone' => 'America/New_York',
            'version' => '12.5',
            'verify_peer' => false,
            'sync_at' => '00:00',
            'sync_schedule_enabled' => true,
            'sync_in_progress' => false
        ]);
    }
}
