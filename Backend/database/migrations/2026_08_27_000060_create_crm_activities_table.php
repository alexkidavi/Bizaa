<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            // Every activity hangs off a contact — one unified per-contact
            // timeline that also surfaces its deal-related activity,
            // rather than two separate, unsynced timelines.
            $table->foreignId('contact_id')->constrained('crm_contacts')->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained('crm_deals')->cascadeOnDelete();
            $table->enum('type', ['call', 'meeting', 'email', 'whatsapp', 'sms', 'note', 'task']);
            $table->string('subject');
            $table->text('description')->nullable();
            // A "task"/follow-up is just an activity with a due_date and
            // no completed_at yet — no separate Task table needed.
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'due_date']);
            $table->index(['business_id', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
    }
};
