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
        Schema::create('tele_chats', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('chat_id')->unique();
            $table->string('chat_type');
            $table->string('chat_title')->nullable();
            $table->string('chat_username')->nullable();
            $table->string('chat_first_name')->nullable();
            $table->string('chat_last_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tele_chats');
    }
};
