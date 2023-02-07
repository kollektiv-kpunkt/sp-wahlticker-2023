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
        Schema::create('politician_results', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("politicianId", 10)->unique();
            $table->text("name");
            $table->string("partyId", 7)->constrained("parties");
            $table->unsignedBigInteger("constituencyId");
            $table->integer("votes")->length(7)->nullable();
            $table->integer("initialPosition")->length(2);
            $table->integer("finalPosition")->length(2)->nullable();
            $table->boolean("elected")->default(false);

            $table->foreign("partyId")->references("partyId")->on("parties");
            $table->foreign("constituencyId")->references("id")->on("constituencies");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('politician_results');
    }
};
