<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('cell_group_id')->nullable()->constrained('cell_groups')->nullOnDelete();
            $table->string('type')->default('prayer');
            $table->string('visibility')->default('public');
            $table->string('title');
            $table->text('content');
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->text('testimony')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'visibility']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_requests');
    }
};
