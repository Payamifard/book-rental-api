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
        Schema::create('rent_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');            //law name, exmple: late_fee
            $table->string('description')->nullable();
            $table->decimal('value', 10, 2);    // عدد اصلی، مثلا 5000 (ریال) یا درصد
            $table->enum('value_type', ['fixed', 'percent', 'day_rate'])->default('fixed');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_rules');
    }
};
