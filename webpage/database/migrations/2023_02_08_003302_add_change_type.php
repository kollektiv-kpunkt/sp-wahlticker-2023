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
        Schema::table('politician_results', function (Blueprint $table) {
            $table->text("change_type")->nullable();
            $table->boolean("is_scheduled")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('politician_results', function (Blueprint $table) {
            $table->dropColumn("change_type");
            $table->dropColumn("is_scheduled");
        });
    }
};
