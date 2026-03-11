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
        Schema::create('service_plans', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('tenant_id');
            $blueprint->foreignId('campus_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->foreignId('service_type_id')->nullable()->constrained()->onDelete('set null');
            $blueprint->foreignId('event_id')->nullable()->constrained()->onDelete('set null');

            $blueprint->string('title');
            $blueprint->string('slug');
            $blueprint->date('service_date');
            $blueprint->time('start_time');
            $blueprint->text('theme')->nullable();
            $blueprint->text('notes')->nullable();

            $blueprint->jsonb('roles')->nullable(); // Assigned staff/volunteers
            $blueprint->string('status')->default('draft'); // draft, published, completed

            $blueprint->jsonb('custom_fields')->nullable();
            $blueprint->jsonb('previous_version')->nullable();
            $blueprint->timestamps();

            $blueprint->index('tenant_id');
            $blueprint->index(['tenant_id', 'service_date']);
            $blueprint->unique(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
