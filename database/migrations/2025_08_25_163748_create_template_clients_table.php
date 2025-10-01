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
    $table->unsignedBigInteger('client_id')->nullable(); // للعلاقة بالعميل الحقيقي
    $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_clients');
    }
};
