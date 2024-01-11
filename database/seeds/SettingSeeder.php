<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Insert some stuff
        DB::table('settings')->insert(
            array(
                'email' => 'admin@optimedevices.com',
                'currency_id' => 1,
                'client_id' => 1,
                'warehouse_id' => null,
                'CompanyName' => 'OptimeDevices',
                'CompanyPhone' => '(049) 808-6669',
                'CompanyAdress' => '632 Angeles Heights Subd. Brgy. Bagong Bayan, San Pablo City, Province of Laguna Region IV-A Philippines 4000.',
                'footer' => '©2023 All Rights Reserve OptimeDevices | Powered by ITechxellence IT Solution',
                'developed_by' => 'Itechxellence IT Solution',
                'logo' => 'logo-default.png',
            )

        );
    }
}
