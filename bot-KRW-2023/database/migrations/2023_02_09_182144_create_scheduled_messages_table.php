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
        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("tele_chat_id");
            $table->string("content", 4096);
            $table->boolean("sent")->default(false);
            $table->string("status")->default("pending");
            $table->dateTime("sent_at")->nullable();

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
        Schema::dropIfExists('scheduled_messages');
    }
};
