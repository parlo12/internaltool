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
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('organisation_name');
            $table->string('calling_service');
            $table->string('texting_service');
            $table->string('signalwire_texting_space_url')->nullable();
            $table->string('signalwire_texting_api_token')->nullable();
            $table->string('signalwire_texting_project_id')->nullable();
            $table->string('twilio_texting_auth_token')->nullable();
            $table->string('twilio_texting_account_sid')->nullable();
            $table->string('twilio_calling_account_sid')->nullable();
            $table->string('twilio_calling_auth_token')->nullable();
            $table->string('signalwire_calling_space_url')->nullable();
            $table->string('signalwire_calling_api_token')->nullable();
            $table->string('signalwire_calling_project_id')->nullable();
            $table->string('user_id');
            $table->string('openAI')->nullable();
            $table->string('device_id')->nullable();
            $table->string('api_url')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('sending_email')->nullable();
            $table->string('email_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
