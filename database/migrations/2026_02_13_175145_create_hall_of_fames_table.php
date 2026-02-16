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
        Schema::create('hall_of_fames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role'); // e.g., President (2025-2026)
            $table->string('batch');
            $table->text('achievements')->nullable();
            $table->text('contribution_summary')->nullable();
            $table->string('image')->nullable();
            $table->json('external_links')->nullable(); // Social/Portfolio links
            $table->integer('order_weight')->default(0); // For custom vertical timeline order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hall_of_fames');
    }
};
