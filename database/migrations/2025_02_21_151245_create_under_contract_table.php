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
        Schema::create('under_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('contact_name');
            $table->string('workflow_id');
            $table->string('organisation_id');
            $table->string('user_id');
            $table->string('zipcode')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('offer')->nullable();
            $table->string('email')->nullable();
            $table->string('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('lead_score')->nullable();
            $table->string('agent')->nullable();
            $table->string('novation')->nullable();
            $table->string('creative_price')->nullable();
            $table->string('monthly')->nullable();
            $table->string('downpayment')->nullable();
            $table->json('messages');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('under_contract');
    }
};
