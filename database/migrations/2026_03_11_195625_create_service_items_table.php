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
        Schema::create('service_items', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('tenant_id');
            $blueprint->foreignId('service_plan_id')->constrained()->onDelete('cascade');

            $blueprint->string('title');
            $blueprint->text('description')->nullable();
            $blueprint->integer('duration_minutes')->default(5);
            $blueprint->string('type')->default('liturgy'); // worship, message, prayer, announcement, special, transition, etc.

            $blueprint->jsonb('roles')->nullable(); // Who is leading this segment
            $blueprint->integer('sort_order')->default(0);

            $blueprint->jsonb('custom_fields')->nullable();
            $blueprint->timestamps();

            $blueprint->index('tenant_id');
            $blueprint->index(['tenant_id', 'service_plan_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
