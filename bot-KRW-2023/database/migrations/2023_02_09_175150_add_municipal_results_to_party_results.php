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
        Schema::table('party_results', function (Blueprint $table) {
            $table->boolean('municipal')->default(false);
            $table->unsignedBigInteger('municipality_id')->nullable();
            $table->foreign('municipality_id')->references('id')->on('municipalities');
            $table->json("chats_interested_municipal")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('party_results', function (Blueprint $table) {
            $table->dropColumn('municipal');
            $table->dropColumn('municipality_id');
            $table->dropColumn('chats_interested_municipal');
        });
    }
};
