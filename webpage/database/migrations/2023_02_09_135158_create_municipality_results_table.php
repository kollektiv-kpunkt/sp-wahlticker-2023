<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipality_results', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->text("mun_name");
            $table->text("mun_id");
            $table->unsignedBigInteger("party_id");
            $table->float("voteShare", 5, 2);
            $table->float("voteShareChange", 5, 2)->nullable();

            $table->foreign("party_id")->references("id")->on("parties");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('municipality_results');
    }
};
