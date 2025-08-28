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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('custom_category')->nullable();
            $table->enum('status', [
                'en_attente',
                'validee',
                'refusee',
                'archivee'
            ])->default('en_attente');
            $table->text('description')->nullable();
            $table->decimal('price_ht', 10, 2)->check('price_ht >= 0');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->boolean('cat_killbill')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
