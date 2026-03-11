<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('fund_id')->nullable()->constrained('funds')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('goal_amount', 14, 2)->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // draft, active, completed, cancelled
            $table->string('image')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'start_date']);
            $table->index(['tenant_id', 'end_date']);
            $table->unique(['tenant_id', 'slug']);
        });

        DB::statement('CREATE INDEX campaigns_custom_fields_gin ON campaigns USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
