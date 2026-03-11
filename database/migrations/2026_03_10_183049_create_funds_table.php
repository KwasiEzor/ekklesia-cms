<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funds', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('type'); // tithe, offering, building, missions, benevolence, other
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_active']);
            $table->unique(['tenant_id', 'slug']);
        });

        DB::statement('CREATE INDEX funds_custom_fields_gin ON funds USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
