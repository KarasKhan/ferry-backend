<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Ex: MRK
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('ports'); }
};