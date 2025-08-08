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
        Schema::create('intervenant_disponibilites', function (Blueprint $table) {
           $table->id();
            $table->foreignId('intervenant_id')->constrained()->onDelete('cascade');
            $table->string('jour'); // ex: lundi, mardi ...
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervenant_disponibilites');
    }
};
