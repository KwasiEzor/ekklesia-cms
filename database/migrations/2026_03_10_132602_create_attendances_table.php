<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained('service_types')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('cell_group_id')->nullable()->constrained('cell_groups')->nullOnDelete();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->date('date');
            $table->time('checked_in_at')->nullable();
            $table->time('checked_out_at')->nullable();
            $table->string('check_in_method')->default('manual');
            $table->boolean('is_first_time')->default(false);
            $table->text('notes')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'member_id', 'date']);
            $table->index(['tenant_id', 'service_type_id', 'date']);
            $table->index(['tenant_id', 'is_first_time']);
            $table->unique(['tenant_id', 'member_id', 'date', 'service_type_id', 'event_id', 'cell_group_id'], 'attendances_unique_record');
        });

        DB::statement('CREATE INDEX attendances_custom_fields_gin ON attendances USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
