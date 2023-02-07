<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BotDefaultData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared(file_get_contents(storage_path('app/defaultData/telegraph_bots.sql')));
        DB::unprepared(file_get_contents(storage_path('app/defaultData/telegraph_chats.sql')));
    }
}
