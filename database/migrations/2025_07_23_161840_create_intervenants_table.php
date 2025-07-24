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
        Schema::create('intervenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type_entreprise', ['Auto-Entrepreneur', 'Freelancer', 'Entreprise']);
            $table->string('nom_entreprise')->nullable(); // Obligatoire côté validation si type = Entreprise
            $table->string('activite_entreprise')->nullable();
            $table->string('categorie_activite')->nullable();
            $table->string('ville');
            $table->string('adresse');
            $table->string('telephone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervenants');
    }
};
