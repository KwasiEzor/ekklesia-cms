<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('slug');
            $table->string('day_of_week')->nullable();
            $table->time('default_start_time')->nullable();
            $table->unsignedInteger('expected_duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
