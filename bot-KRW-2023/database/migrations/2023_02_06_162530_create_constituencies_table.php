<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('constituencies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text("name");
            $table->integer("seats_2023")->length(2)->nullable();
            $table->integer("seats_2019")->length(2)->nullable();
            $table->integer("population")->length(6)->default(0);
        });

        DB::unprepared(file_get_contents(storage_path("app/defaultData/constituencies.sql")));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('constituencies');
    }
};
