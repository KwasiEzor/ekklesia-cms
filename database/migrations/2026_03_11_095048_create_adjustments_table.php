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
        Schema::create('adjustments', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('tenant_id');
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();

            $blueprint->morphs('adjustable'); // GivingRecord or PaymentTransaction

            $blueprint->string('type'); // void, correction
            $blueprint->decimal('amount_before', 15, 2)->nullable();
            $blueprint->decimal('amount_after', 15, 2)->nullable();
            $blueprint->string('reason');
            $blueprint->jsonb('metadata')->nullable();

            $blueprint->timestamps();

            $blueprint->index('tenant_id');
            $blueprint->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
