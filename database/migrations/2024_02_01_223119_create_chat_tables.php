<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create conversations table
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->timestamps();
        });

        // Drop existing chat_histories table if exists
        Schema::dropIfExists('chat_histories');

        // Create new chat_histories table
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->text('user_message');
            $table->text('ai_response');
            $table->integer('sequence');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_histories');
        Schema::dropIfExists('conversations');
    }
};
