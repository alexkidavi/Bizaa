<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('crm_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('name');
            $table->enum('channel', ['sms', 'whatsapp', 'email']);
            $table->string('subject')->nullable(); // email only
            $table->text('message'); // template body — supports {{name}}
            // Snapshot of the segment criteria used to build the recipient
            // list at send time (tag_id, lifecycle_stage, company_id,
            // source_id) — kept for the record even if contacts/tags
            // change afterward.
            $table->json('filters')->nullable();
            $table->string('status')->default('draft'); // draft, sending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_campaigns');
    }
};
