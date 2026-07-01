<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (DB::table('contacts')->exists()) {
            throw new RuntimeException('The contacts table is not empty. Export or migrate existing contact records before dropping it.');
        }

        Schema::drop('contacts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('prenom')->nullable();
            $table->string('nom');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('heure_appel')->nullable();
            $table->enum('type_utilisateur', ['formateur', 'stagiaire']);
            $table->json('objet')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }
};
