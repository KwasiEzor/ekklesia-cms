<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devotionals', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('series_id')->nullable()->constrained('devotional_series')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('verse_reference'); // e.g. "Jean 3:16", "Psaumes 23:1-6"
            $table->text('verse_text')->nullable();
            $table->text('reflection');
            $table->text('prayer_point')->nullable();
            $table->text('application')->nullable(); // practical application
            $table->date('scheduled_date');
            $table->string('status')->default('draft'); // draft, scheduled, published
            $table->timestamp('published_at')->nullable();
            $table->string('author')->nullable();
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'scheduled_date']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'created_at']);
            $table->unique(['tenant_id', 'slug']);
        });

        DB::statement('CREATE INDEX devotionals_custom_fields_gin ON devotionals USING GIN (custom_fields)');
    }

    public function down(): void
    {
        Schema::dropIfExists('devotionals');
    }
};
