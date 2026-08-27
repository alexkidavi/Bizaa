<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * pipeline_stage_id was created with cascadeOnDelete() — deleting a stage
 * would silently delete every deal in it. Deals require a stage (the
 * column isn't nullable), so the safe behavior is to block the delete at
 * the DB level; CrmPipelineStageService::delete() also checks for
 * existing deals first so the failure is a clean validation message, not
 * a raw constraint error.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropForeign(['pipeline_stage_id']);
        });
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->foreign('pipeline_stage_id')->references('id')->on('crm_pipeline_stages')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropForeign(['pipeline_stage_id']);
        });
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->foreign('pipeline_stage_id')->references('id')->on('crm_pipeline_stages')->cascadeOnDelete();
        });
    }
};
