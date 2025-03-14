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
        Schema::create('number_pools', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('pool_name');
            $table->string('pool_messages');
            $table->string('pool_time');
            $table->string('pool_time_units');
            $table->string('organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_pools');
    }
};
