<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_reading_progress', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reading_plan_day_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('started_at');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['member_id', 'reading_plan_id']);
            $table->unique(['member_id', 'reading_plan_id', 'reading_plan_day_id'], 'member_plan_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_reading_progress');
    }
};
