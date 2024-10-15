<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run() : void
    {
        Tool::factory(10)->create([
            'user_id' => 2,
        ]);
    }
}
