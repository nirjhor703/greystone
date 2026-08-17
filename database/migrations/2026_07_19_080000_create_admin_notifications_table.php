<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();

            $table->string('category', 30);
            $table->string('type', 60);

            $table->string('title', 180);
            $table->text('message');
            $table->string('link_url')->nullable();

            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();

            $table->json('meta')->nullable();
            $table->date('reminder_date')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['category', 'read_at']);
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->unique(
                [
                    'type',
                    'notifiable_type',
                    'notifiable_id',
                    'reminder_date',
                ],
                'admin_notification_daily_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
