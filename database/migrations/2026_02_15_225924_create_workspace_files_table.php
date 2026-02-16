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
        Schema::create('workspace_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable(); // For folder structure
            $table->string('name');
            $table->enum('type', ['file', 'folder']);
            $table->string('language')->nullable();
            $table->longText('content')->nullable();
            $table->string('path'); // Recursive path for easy lookups
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('workspace_files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_files');
    }
};
