<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createAnnouncment();
        }
    }

    public function createAnnouncment() {
        $faker = \Faker\Factory::create("de_CH");
        $subtitles = [
            "Kantonsrat | Neuer Wahlkreis ausgezählt",
            "Regierungsrat | Neuer Wahlkreis ausgezählt"
        ];
        $rand = rand(0, 2);
        $announcment = new Announcement([
            'title' => $faker->sentence,
            'content' => '{"time":1675778944662,"blocks":[{"id":"j_VV-DSzOX","type":"paragraph","data":{"text":"\n                Adipisci velit sunt praesentium quibusdam. Non sapiente ex ut quo quia est fugiat. Debitis nostrum praesentium fuga est non quia odit. Voluptatum veritatis animi aperiam voluptatum.\n            "}}],"version":"2.26.5"}',
            'user_id' => 1,
        ]);
        if ($rand == 2) {
            $announcment->subtitle = $faker->sentence;
            $announcment->type = "news";
        } else {
            $announcment->subtitle = $subtitles[$rand];
            $announcment->type = "newResult";
        }
        $announcment->save();
    }
}
