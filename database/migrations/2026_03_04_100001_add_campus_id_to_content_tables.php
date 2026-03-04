<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'members',
        'cell_groups',
        'events',
        'sermons',
        'announcements',
        'giving_records',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->foreignId('campus_id')->nullable()->after('tenant_id')->constrained('campuses')->nullOnDelete();
                $blueprint->index(['tenant_id', 'campus_id'], "{$table}_tenant_campus_index");
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropForeign(["{$table}_campus_id_foreign"]);
                $blueprint->dropIndex("{$table}_tenant_campus_index");
                $blueprint->dropColumn('campus_id');
            });
        }
    }
};
