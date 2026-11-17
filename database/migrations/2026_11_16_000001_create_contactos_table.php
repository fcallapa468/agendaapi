<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Crea la tabla "contactos": cada contacto pertenece a un usuario
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('telefono', 20);
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    // Deshace la migración (borra la tabla)
    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};
