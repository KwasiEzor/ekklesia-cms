<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('households', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'created_at']);
            $table->unique(['tenant_id', 'slug']);
        });

        // Add household_id and family_role to members
        Schema::table('members', function (Blueprint $table): void {
            $table->foreignId('household_id')->nullable()->constrained('households')->nullOnDelete();
            $table->string('family_role')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('household_id');
            $table->dropColumn('family_role');
        });

        Schema::dropIfExists('households');
    }
};
