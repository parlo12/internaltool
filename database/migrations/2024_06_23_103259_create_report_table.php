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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('contact_uid');
            $table->string('group_name')->nullable();
            $table->string('call_sid')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('call_status')->nullable();
            $table->string('phone')->nullable();
            $table->string('campaign_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
