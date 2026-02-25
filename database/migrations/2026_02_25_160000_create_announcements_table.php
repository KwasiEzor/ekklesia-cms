<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('title');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('pinned')->default(false);
            $table->string('target_group')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'published_at']);
            $table->index(['tenant_id', 'created_at']);
            $table->unique(['tenant_id', 'slug']);
        });

        DB::statement('CREATE INDEX announcements_custom_fields_gin ON announcements USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
