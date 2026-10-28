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
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->char('token_hash',64)->unique();
            $table->dateTime('expires_at');
            $table->dateTime('created_at')->useCurrent();

            //connecting foreign key
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
            
            //the index
            $table->index('token_hash', 'idx_token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_tokens');
    }
};
