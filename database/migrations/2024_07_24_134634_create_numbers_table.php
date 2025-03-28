<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('numbers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('phone_number');
            $table->string('provider');
            $table->string('purpose');
            $table->string('organisation_id');
            $table->string('sending_server_id')->nullable();
            $table->string('number_pool_id')->nullable();
            $table->string('can_refill_on')->nullable();
            $table->string('remaining_messages')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numbers');
    }
};
