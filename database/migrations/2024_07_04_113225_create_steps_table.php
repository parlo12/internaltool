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
        Schema::create('steps', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('workflow_id');
            $table->string('type');
            $table->string('content');
            $table->string('name');
            $table->string('delay');
            $table->boolean('custom_sending');
            $table->string('custom_sending_data')->nullable();
            $table->string('end_time')->nullable();
            $table->string('start_time')->nullable();
            $table->string('days_of_week')->nullable();
            $table->string('batch_size')->nullable();
            $table->string('batch_delay')->nullable();
            $table->string('step_quota_balance')->nullable();
            $table->string('offer_expiry')->nullable();
            $table->string('email_subject')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steps');
    }
};
