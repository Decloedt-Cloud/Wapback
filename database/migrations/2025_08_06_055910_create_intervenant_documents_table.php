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
        Schema::create('intervenant_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervenant_id')->constrained()->onDelete('cascade');
            $table->string('type')->comment('photo_profil ou document');
            $table->string('filename');
            $table->string('filepath'); // chemin stockage fichier
            $table->string('mime_type');
            $table->bigInteger('size'); // taille en octets
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervenant_documents');
    }
};
