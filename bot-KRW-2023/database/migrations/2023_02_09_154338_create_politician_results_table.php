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
            $table->string("politician_id", 10)->unique();
            $table->text("name");
            $table->string("party_id", 7)->constrained("parties");
            $table->unsignedBigInteger("constituency_id")->nullable();
            $table->integer("votes")->length(7)->nullable();
            $table->integer("initialPosition")->length(2)->nullable();
            $table->integer("finalPosition")->length(2)->nullable();
            $table->boolean("elected")->default(false);
            $table->string("council")->nullable();
            $table->json('chats_interested')->nullable()->default(null);
            $table->text("change_type")->nullable();
            $table->boolean("is_scheduled")->default(false);

            $table->foreign("party_id")->references("party_id")->on("parties");
            $table->foreign("constituency_id")->references("id")->on("constituencies");
        });

        DB::unprepared(file_get_contents(storage_path("app/defaultData/politician_results.sql")));
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
