<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_limits', function (Blueprint $table): void {
            $table->id();
            $table->string('plan_slug')->unique();
            $table->string('name');
            $table->unsignedInteger('max_members')->default(50);
            $table->unsignedInteger('max_storage_mb')->default(500);
            $table->unsignedInteger('max_campuses')->default(1);
            $table->boolean('has_payments')->default(false);
            $table->boolean('has_sms')->default(false);
            $table->boolean('has_whatsapp')->default(false);
            $table->boolean('has_ai')->default(false);
            $table->string('ai_tier')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('stripe_price_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_limits');
    }
};
