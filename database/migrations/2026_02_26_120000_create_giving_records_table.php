<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giving_records', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('XOF');
            $table->date('date');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->string('campaign_id')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'method']);
            $table->index(['tenant_id', 'currency']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'member_id']);
            $table->index(['tenant_id', 'campaign_id']);
        });

        DB::statement('CREATE INDEX giving_records_custom_fields_gin ON giving_records USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('giving_records');
    }
};
