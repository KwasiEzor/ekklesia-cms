<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_commitments', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('prayer_request_id')->constrained('prayer_requests')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->timestamp('prayed_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->unique(['prayer_request_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_commitments');
    }
};
