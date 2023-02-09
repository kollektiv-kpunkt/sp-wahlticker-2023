<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ScheduledMessage;
use Faker\Factory as Faker;

class ScheduledMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        for ($i = 0; $i < 10; $i++) {
            ScheduledMessage::create([
                "content" => $faker->sentence,
                "tele_chat_id" => "543376720",
            ]);
        }
    }
}
