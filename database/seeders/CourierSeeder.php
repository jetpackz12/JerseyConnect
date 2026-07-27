<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Courier::create([
            'name' => 'JT Express',
            'site' => 'https://www.jtexpress.ph/track-and-trace?waybillNo=&flag=1',
            'status' => 'active'
        ]);

        Courier::create([
            'name' => 'Flash Express',
            'site' => 'https://www.flashexpress.ph/fle/tracking',
            'status' => 'active'
        ]);
    }
}
