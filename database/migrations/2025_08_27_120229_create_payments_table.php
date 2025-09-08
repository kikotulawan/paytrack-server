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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->bigInteger('or');
            $table->string('payor_name');
            $table->dateTime('deposit_date')->nullable();
            $table->dateTime('payment_date');
            $table->string('mode_of_payment');
            $table->string('reference')->nullable();
            $table->longText('description')->nullable();
            $table->string('nature_of_collection');
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};