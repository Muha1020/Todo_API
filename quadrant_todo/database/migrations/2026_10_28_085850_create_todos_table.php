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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('quadrant', [
                'important_urgent',
                'important_not_urgent',
                'not_important_urgent',
                'not_important_not_urgent',
            ]);
            $table->enum('status', ['pending', 'completed']);
            $table->date('due_date');
            $table->unsignedInteger('carry_count')->default(0);
            $table->unsignedInteger('postpone_count')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('user_id', 'idx_user');
            $table->index(['user_id', 'due_date'], 'idx_user_date');
            $table->index(['user_id', 'quadrant'], 'idx_user_quadrant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
