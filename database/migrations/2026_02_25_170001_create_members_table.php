<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('baptism_date')->nullable();
            $table->foreignId('cell_group_id')->nullable()->constrained('cell_groups')->nullOnDelete();
            $table->string('status')->default('active');
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
        });

        DB::statement('CREATE UNIQUE INDEX members_tenant_email_unique ON members (tenant_id, email) WHERE email IS NOT NULL');
        DB::statement('CREATE INDEX members_custom_fields_gin ON members USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
