<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sermons', function (Blueprint $table): void {
            $table->dropColumn('tags');
        });
    }

    public function down(): void
    {
        Schema::table('sermons', function (Blueprint $table): void {
            $table->jsonb('tags')->nullable();
        });
    }
};
