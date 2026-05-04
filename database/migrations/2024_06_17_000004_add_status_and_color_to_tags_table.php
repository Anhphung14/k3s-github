<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tags', function (Blueprint $table) {
            if (!Schema::hasColumn('tags', 'status')) {
                $table->string('status', 16)->nullable()->after('description');
            }
            // Không thêm lại cột color nếu đã có
            // if (!Schema::hasColumn('tags', 'color')) {
            //     $table->string('color', 32)->nullable()->after('status');
            // }
        });
    }

    public function down()
    {
        Schema::table('tags', function (Blueprint $table) {
            if (Schema::hasColumn('tags', 'status')) {
                $table->dropColumn('status');
            }
            // if (Schema::hasColumn('tags', 'color')) {
            //     $table->dropColumn('color');
            // }
        });
    }
};
