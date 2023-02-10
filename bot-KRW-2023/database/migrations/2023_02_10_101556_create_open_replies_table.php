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
        Schema::create('open_replies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("command");
            $table->boolean("replied")->default(false);
            $table->string("tele_chat_id");

            $table->foreign("tele_chat_id")->references("chat_id")->on("tele_chats");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('open_replies');
    }
};
