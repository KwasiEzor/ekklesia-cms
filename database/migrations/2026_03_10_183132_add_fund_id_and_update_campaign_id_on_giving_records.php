<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('giving_records', function (Blueprint $table): void {
            $table->foreignId('fund_id')->nullable()->after('campus_id')->constrained('funds')->nullOnDelete();

            // Drop old campaign_id varchar column and replace with proper FK
            $table->dropIndex('giving_records_tenant_id_campaign_id_index');
            $table->dropColumn('campaign_id');
        });

        Schema::table('giving_records', function (Blueprint $table): void {
            $table->foreignId('campaign_id')->nullable()->after('fund_id')->constrained('campaigns')->nullOnDelete();
            $table->index(['tenant_id', 'fund_id']);
            $table->index(['tenant_id', 'campaign_id']);
        });

        // Also update payment_transactions campaign_id
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropColumn('campaign_id');
        });

        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->foreignId('campaign_id')->nullable()->after('phone_number')->constrained('campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('campaign_id');
        });

        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->string('campaign_id')->nullable()->after('phone_number');
        });

        Schema::table('giving_records', function (Blueprint $table): void {
            $table->dropIndex('giving_records_tenant_id_fund_id_index');
            $table->dropIndex('giving_records_tenant_id_campaign_id_index');
            $table->dropConstrainedForeignId('campaign_id');
            $table->dropConstrainedForeignId('fund_id');
        });

        Schema::table('giving_records', function (Blueprint $table): void {
            $table->string('campaign_id')->nullable()->after('reference');
            $table->index(['tenant_id', 'campaign_id']);
        });
    }
};
