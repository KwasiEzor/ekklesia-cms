<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->date('wedding_anniversary')->nullable()->after('date_of_birth');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table): void {
            $table->dropColumn(['date_of_birth', 'wedding_anniversary']);
        });
    }
};
