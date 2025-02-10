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
        Schema::create('sending_servers', function (Blueprint $table) {
            $table->id();
            $table->string('purpose');
            $table->string('server_name');
            $table->string('service_provider');
            $table->string('signalwire_space_url')->nullable();
            $table->string('signalwire_api_token')->nullable();
            $table->string('signalwire_project_id')->nullable();
            $table->string('twilio_auth_token')->nullable();
            $table->string('twilio_account_sid')->nullable();
            $table->string('user_id');
            $table->string('websockets_api_url')->nullable();
            $table->string('websockets_auth_token')->nullable();
            $table->string('websockets_device_id')->nullable();
            $table->string('organisation_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sending_servers');
    }
};