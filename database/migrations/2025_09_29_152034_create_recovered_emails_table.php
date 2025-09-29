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
        Schema::create('recovered_emails', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('workflow_id')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('offer')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->integer('lead_score')->nullable();
            $table->string('agent')->nullable();
            $table->string('novation')->nullable();
            $table->decimal('creative_price', 15, 2)->nullable();
            $table->decimal('monthly', 15, 2)->nullable();
            $table->decimal('downpayment', 15, 2)->nullable();
            $table->text('generated_message')->nullable();
            $table->decimal('list_price', 15, 2)->nullable();
            $table->boolean('no_second_call')->nullable();
            $table->decimal('earnest_money_deposit', 15, 2)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovered_emails');
    }
};
