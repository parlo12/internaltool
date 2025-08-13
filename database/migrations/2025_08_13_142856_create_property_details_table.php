<?php

// database/migrations/xxxx_xx_xx_create_property_details_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('property_details', function (Blueprint $table) {
            $table->id();
            $table->string('upa');
            $table->string('sca');
            $table->decimal('downpayment', 15, 2);
            $table->decimal('purchase_price', 15, 2);
            $table->string('plc');
            $table->timestamps();
            $table->string('organisation_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_details');
    }
};
