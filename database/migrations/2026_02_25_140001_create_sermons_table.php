<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('title');
            $table->string('slug');
            $table->string('speaker');
            $table->date('date');
            $table->unsignedInteger('duration')->nullable()->comment('Duration in seconds');
            $table->string('audio_url')->nullable();
            $table->string('video_url')->nullable();
            $table->longText('transcript')->nullable();
            $table->foreignId('series_id')->nullable()->constrained('sermon_series')->nullOnDelete();
            $table->jsonb('tags')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'date']);
            $table->unique(['tenant_id', 'slug']);
        });

        // GIN index on custom_fields for JSONB queries
        DB::statement('CREATE INDEX sermons_custom_fields_gin ON sermons USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sermons');
    }
};
