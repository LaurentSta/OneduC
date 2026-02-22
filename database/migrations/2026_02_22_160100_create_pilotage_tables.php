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
        Schema::create('pilot_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['module_id', 'due_date']);
        });

        Schema::create('pilot_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('pilot_projects')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 32)->default('todo');
            $table->string('priority', 16)->default('normal');
            $table->date('due_date')->nullable();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('task_type', 32);
            $table->string('internal_url')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'position']);
            $table->index(['task_type', 'priority']);
            $table->index(['module_id', 'responsible_id']);
            $table->index('due_date');
        });

        Schema::create('pilot_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('pilot_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('pilot_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('pilot_projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('pilot_tasks')->cascadeOnDelete();
            $table->boolean('notify_in_app')->default(true);
            $table->boolean('notify_mail')->default(false);
            $table->string('frequency', 16)->default('immediate');
            $table->timestamps();

            $table->index(['user_id', 'project_id']);
            $table->index(['user_id', 'task_id']);
            $table->index('frequency');
        });

        Schema::create('pilot_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(false);
            $table->string('frequency', 16)->default('immediate');
            $table->json('event_types')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('route_name')->nullable();
            $table->string('method', 10);
            $table->string('url');
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['route_name', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_journal_entries');
        Schema::dropIfExists('pilot_notification_preferences');
        Schema::dropIfExists('pilot_subscriptions');
        Schema::dropIfExists('pilot_task_comments');
        Schema::dropIfExists('pilot_tasks');
        Schema::dropIfExists('pilot_projects');
    }
};

