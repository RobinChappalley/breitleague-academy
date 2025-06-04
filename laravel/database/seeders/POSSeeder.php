<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class POSSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/pos.json');
        $posList = json_decode(file_get_contents($path), true);

        foreach ($posList as $pos) {
            DB::table('pos')->insert([
                'id' => $pos['id'],
                'address' => $pos['address'],
                'zipcode' => $pos['zipcode'],
                'country' => $pos['country'],
                'breitling_pin' => $pos['breitling_pin'],
                'country_flag' => $pos['country_flag'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
