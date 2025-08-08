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

            // Lien avec l'utilisateur (clé étrangère) obligatoire
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Type et infos entreprise (nullable)
            $table->enum('type_entreprise', ['Auto-Entrepreneur', 'Freelancer', 'Entreprise'])->nullable();
            $table->string('nom_entreprise')->nullable();
            $table->string('activite_entreprise')->nullable();
            $table->string('categorie_activite')->nullable();

            // Données localisation/professionnelles (nullable)
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable();

            // Contact (nullable)
            $table->string('indicatif')->nullable(); // Ex: +212
            $table->string('telephone')->nullable();


            // Champ Profil personnel (nullable)
            $table->enum('sexe', ['Homme', 'Femme'])->nullable();
            $table->string('prenom')->nullable();
            $table->string('nom')->nullable();

            // Infos complémentaires (nullable)
            $table->date('date_naissance')->nullable();
            $table->string('langue_maternelle')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->json('competences')->nullable(); // champs JSON
            // Suppression douce et timestamps
            $table->boolean('profil_rempli')->default(false);

            $table->softDeletes();
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
