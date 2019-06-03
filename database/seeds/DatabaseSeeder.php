<?php

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
        // $this->call(UsersTableSeeder::class);
        \App\User::create([
            'name' => 'Marty Sloan',
            'email' => 'marty@karmatek.io',
            'password' => Hash::make('password123')
        ]);

        \App\Models\Ucm::create([
            'name' => 'CIP3 Dev',
            'ip_address' => '10.175.200.10',
            'username' => 'cip3-admin',
            'password' => 'password123',
            'timezone' => 'America/New_York',
            'version' => '12.5',
            'verify_peer' => false,
            'sync_at' => '00:00:00',
            'sync_schedule_enabled' => true,
            'sync_in_progress' => false
        ]);
    }
}
