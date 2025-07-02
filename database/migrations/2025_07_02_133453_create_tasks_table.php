<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // if user is deleted, his tasks will be deleted.
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date');
            $table->string('status')->default(TaskStatus::PENDING->value);
            $table->string('priority')->default(TaskPriority::MEDIUM->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
