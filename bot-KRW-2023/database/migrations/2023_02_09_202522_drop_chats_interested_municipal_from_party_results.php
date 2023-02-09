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
            $table->dropColumn('chats_interested_municipal');
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
            $table->json("chats_interested_municipal")->nullable();
        });
    }
};
