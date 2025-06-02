<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        $modules = json_decode(file_get_contents(database_path('seeders/data/modules.json')), true);

        foreach ($modules as $module) {
            DB::table('modules')->insert([
                'id' => $module['id'],
                'title' => $module['title'],
                'order_position' => $module['order_position']
            ]);
        }
    }
}
