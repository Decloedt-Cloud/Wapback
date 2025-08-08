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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('sexe', ['Homme', 'Femme'])->nullable();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
            $table->string('nationalite', 5)->nullable();
            $table->string('adresse')->nullable();
            $table->string('indicatif', 10)->nullable();
            $table->string('telephone')->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('langue_maternelle')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->boolean('profil_rempli')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
