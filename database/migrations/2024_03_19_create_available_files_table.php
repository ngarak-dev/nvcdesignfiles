<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('available_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained()->onDelete('cascade');
            $table->string('temp_path');
            $table->timestamp('download_ready_at');
            $table->timestamp('expires_at');
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // $table->boolean('is_downloaded')->default(false);
            // $table->timestamp('downloaded_at')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('available_files');
    }
};
