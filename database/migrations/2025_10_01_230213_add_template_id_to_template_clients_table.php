<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('template_clients', function (Blueprint $table) {
        $table->unsignedBigInteger('template_id')->nullable()->after('id'); // أو after العمود المناسب
    });
}

public function down(): void
{
    Schema::table('template_clients', function (Blueprint $table) {
        $table->dropColumn('template_id');
    });
}

};
