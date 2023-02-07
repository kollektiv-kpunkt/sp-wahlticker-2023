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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("partyId", 7)->unique();
            $table->string("name", 50);
            $table->string("abbreviation", 10);
            $table->string("color", 7);
            $table->string("seats_2023", 2)->nullable();
            $table->string("seats_2019", 2)->nullable();
            $table->string("seats_2015", 2)->nullable();
            $table->float("voteShare_2023", 5, 2)->nullable();
            $table->float("voteShare_2019", 5, 2)->nullable();
            $table->float("voteShare_2015", 5, 2)->nullable();
        });

        DB::unprepared(file_get_contents(storage_path("app/defaultData/parties.sql")));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parties');
    }
};
