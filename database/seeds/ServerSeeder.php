<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::statement('SET IDENTITY_INSERT servers ON');
       // Insert some stuff
        DB::table('servers')->insert([
            'host' => 'smtp.office365.com',
            'port' => '587',
            'username' => 'admin@optimedevices.com',
            'password' => '@H520GamingCenter27',
            'encryption' => 'tls',
        ]);

        // DB::statement('SET IDENTITY_INSERT servers OFF');
    }
}
