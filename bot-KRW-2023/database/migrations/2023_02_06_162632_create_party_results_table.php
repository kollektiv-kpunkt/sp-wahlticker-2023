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
        Schema::create('party_results', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("party_id", 7)->constrained("parties");
            $table->unsignedBigInteger("constituency_id");
            $table->integer("votes")->length(7)->nullable();
            $table->float("voteShare", 5, 2)->nullable();
            $table->integer("seats")->length(2)->nullable();
            $table->integer("seatsChange")->length(2)->nullable();

            $table->foreign("party_id")->references("party_id")->on("parties");
            $table->foreign("constituency_id")->references("id")->on("constituencies");
        });

        DB::unprepared(file_get_contents(storage_path("app/defaultData/party_results.sql")));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('party_results');
    }
};
