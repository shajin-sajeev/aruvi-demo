<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('site')->index();
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
