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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('steps')->nullable();
            $table->string('steps_flow')->nullable();
            $table->string('contact_group');
            $table->string('group_id');
            $table->string('name');
            $table->boolean('active');
            $table->string('voice');
            $table->string('agent_number');
            $table->string('texting_number')->nullable();
            $table->string('calling_number')->nullable();
            $table->string('folder_id')->nullable();
            $table->string('godspeedoffers_api')->nullable();
            $table->string('user_id');
            $table->string('organisation_id');
            $table->string('number_pool_id')->nullable();
            $table->string('generated_message')->nullable();
            $table->string('job_id')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
