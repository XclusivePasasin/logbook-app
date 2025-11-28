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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->date('trip_date');
            $table->time('trip_time');
            $table->integer('passengers');
            $table->string('origin'); // Salida
            $table->string('destination'); // Destino
            $table->enum('payment_method', ['E', 'CH', 'TJ']); // E=Efectivo, CH=Cargo Habitación, TJ=Tarjeta
            $table->string('equipment_number')->nullable(); // EQ - Número de equipo/carro
            $table->boolean('is_round_trip')->default(false); // Ida y Vuelta
            $table->decimal('amount', 10, 2);
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
