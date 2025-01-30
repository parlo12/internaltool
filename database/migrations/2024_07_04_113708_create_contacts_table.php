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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uuid');
            $table->integer('current_step')->nullable();
            $table->string('contact_communication_ids')->nullable();
            $table->string('phone');
            $table->string('contact_name');
            $table->boolean('can_send');
            $table->dateTime('can_send_after')->nullable();
            $table->string('response');
            $table->string('status');
            $table->string('workflow_id');
            $table->boolean('valid_lead')->nullable();
            $table->boolean('deal_closed')->nullable();
            $table->boolean('offer_made')->nullable();
            $table->boolean('contract_executed')->nullable();
            $table->boolean('contract_cancelled')->nullable();
            $table->string('cost');
            $table->boolean('subscribed');
            $table->string('organisation_id');
            $table->string('user_id');
            $table->string('zipcode')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
