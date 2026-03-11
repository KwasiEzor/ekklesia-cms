<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_plan_days', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreignId('reading_plan_id')->constrained()->cascadeOnDelete();
            $table->integer('day_number');
            $table->string('title')->nullable();
            $table->string('passage_reference');
            $table->text('passage_text')->nullable();
            $table->text('reflection')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->unique(['reading_plan_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_plan_days');
    }
};
