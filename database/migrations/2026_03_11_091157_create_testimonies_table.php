<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonies', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->string('category'); // healing, provision, deliverance, conversion, family_restoration, other
            $table->string('status')->default('submitted'); // submitted, approved, rejected, featured
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable(); // user name who approved
            $table->text('rejection_reason')->nullable();
            $table->jsonb('reactions')->nullable(); // {"amen": 5, "gloire_a_dieu": 3, "alleluia": 2}
            $table->jsonb('custom_fields')->nullable();
            $table->jsonb('previous_version')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'is_featured']);
            $table->index(['tenant_id', 'created_at']);
        });

        DB::statement('CREATE INDEX testimonies_custom_fields_gin ON testimonies USING GIN (custom_fields)');
        DB::statement('CREATE INDEX testimonies_reactions_gin ON testimonies USING GIN (reactions)');
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonies');
    }
};
