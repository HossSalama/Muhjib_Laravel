<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    // Schema::table('activities', function (Blueprint $table) {
    //     $table->timestamps(); // بيضيف created_at و updated_at
    // });
}

public function down()
{
    Schema::table('activities', function (Blueprint $table) {
        $table->dropTimestamps();
    });
}

};
