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
        Schema::create('openai_assistants_files', function (Blueprint $table) {
            $table->id();
            $table->biginteger('bot_openai_assistant_id');
            $table->string("description")->nullable();
            
            $table->string("openai_file_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openai_assistants_files');
    }
};
