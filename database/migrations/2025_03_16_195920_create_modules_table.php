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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('subcategory_id')->constrained('subcategories')->onDelete('cascade');
            $table->foreignId('formateur_id')->constrained('users')->onDelete('cascade');

            $table->string('module_image')->nullable();
            $table->string('header_image')->nullable();

            $table->text('module_title')->nullable();
            $table->text('module_name')->nullable();
            $table->string('module_name_slug')->nullable();
            $table->longText('description')->nullable();

            $table->string('video')->nullable();
            $table->string('label')->nullable();

            $table->string('duree')->nullable(); // ou integer si tu préfères en minutes
            $table->string('resources')->nullable();
            $table->boolean('certificat')->default(false);
            $table->text('prerequi')->nullable();

            $table->boolean('bestseller')->default(false);
            $table->boolean('vedette')->default(false);
            $table->boolean('surevalue')->default(false);

            $table->tinyInteger('status')->default(0)->comment('0=Inactive, 1=Active');

            $table->timestamps();
            $table->softDeletes(); // si tu veux
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
