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
        Schema::table('counted_municipalities', function (Blueprint $table) {
            $table->unsignedBigInteger('constituency_id')->nullable();
            $table->foreign('constituency_id')->references('id')->on('constituencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('counted_municipalities', function (Blueprint $table) {
            $table->dropForeign('counted_municipalities_constituency_id_foreign');
            $table->dropColumn('constituency_id');
        });
    }
};
