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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('election_id')->constrained()->cascadeOnDelete();
            $table->string('position'); // President, Vice President, etc.
            $table->text('manifesto')->nullable();
            $table->text('vision_statement')->nullable();
            $table->string('campaign_video')->nullable();
            $table->string('manifesto_path')->nullable(); // PDF file path
            $table->unsignedBigInteger('votes_count')->default(0); // Cached vote count
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
