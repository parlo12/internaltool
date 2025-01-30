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
        Schema::create('assistants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('prompt');
            $table->string('file1')->nullable();
            $table->string('file2')->nullable();
            $table->string('file1_id')->nullable();
            $table->string('file2_id')->nullable();
            $table->string('openAI_id')->nullable();
            $table->string('min_wait_time')->nullable();
            $table->string('max_wait_time')->nullable();
            $table->string('maximum_messages')->nullable();
            $table->string('sleep_time')->nullable();
            $table->string('wait_time_units')->nullable();
            $table->string('sleep_time_units')->nullable();
            $table->string('openAI')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assistants');
    }
};
