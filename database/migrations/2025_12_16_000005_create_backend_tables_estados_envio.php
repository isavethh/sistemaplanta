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
        if (!Schema::hasTable('estados_envio')) {
            Schema::create('estados_envio', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 50)->unique();
                $table->text('descripcion')->nullable();
                $table->string('color', 20)->nullable();
                $table->integer('orden')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_envio');
    }
};

