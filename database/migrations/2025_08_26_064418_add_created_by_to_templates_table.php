<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('templates', function (Blueprint $table) {
        $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('templates', function (Blueprint $table) {
        $table->dropConstrainedForeignId('created_by');
    });
}
};
