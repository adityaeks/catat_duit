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
        Schema::create('couples', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('couple_id')->nullable()->after('remember_token')->constrained('couples')->nullOnDelete();
            $table->string('pairing_code', 16)->nullable()->unique()->after('couple_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['couple_id']);
            $table->dropColumn(['couple_id', 'pairing_code']);
        });

        Schema::dropIfExists('couples');
    }
};
