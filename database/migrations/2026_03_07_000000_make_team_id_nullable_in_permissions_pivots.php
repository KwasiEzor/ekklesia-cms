<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $teamKey = $columnNames['team_foreign_key'];
        $modelMorphKey = $columnNames['model_morph_key'];

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamKey, $pivotRole, $modelMorphKey) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->string($teamKey)->nullable()->change();
            
            // We can't have NULL in a primary key in PostgreSQL.
            // We use a unique index instead, and leave the table without a primary key (not ideal but works for Spatie).
            // Or we could add an auto-incrementing ID but Spatie doesn't use it.
            $table->unique([$teamKey, $pivotRole, $modelMorphKey, 'model_type'], 'model_has_roles_role_model_type_unique');
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamKey, $pivotPermission, $modelMorphKey) {
            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->string($teamKey)->nullable()->change();
            
            $table->unique([$teamKey, $pivotPermission, $modelMorphKey, 'model_type'], 'model_has_permissions_permission_model_type_unique');
        });
    }

    public function down(): void
    {
        // Reverting this is complex and might lose data if NULLs were introduced.
    }
};
