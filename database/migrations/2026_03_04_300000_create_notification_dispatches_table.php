<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_dispatches', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('channel');
            $table->string('type');
            $table->string('status')->default('pending');
            $table->string('recipient');
            $table->text('subject')->nullable();
            $table->text('body')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'channel']);
            $table->index(['tenant_id', 'status']);
        });

        DB::statement('CREATE INDEX notification_dispatches_metadata_gin ON notification_dispatches USING GIN (metadata)');
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_dispatches');
    }
};
