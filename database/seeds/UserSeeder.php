<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Insert some stuff
        DB::table('users')->insert(
            array(
                'id' => 1,
                'firstname' => 'Optimed',
                'lastname' => 'Devices',
                'username' => 'Optimedevices',
                'email' => 'admin@example.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
                'avatar' => 'no_avatar.png',
                'phone' => '0123456789',
                'role_id' => 1,
                'statut' => 1,
                'is_all_warehouses' => 1,
            )
        );
    }
}
