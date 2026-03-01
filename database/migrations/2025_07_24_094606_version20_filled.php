<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone_number');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->text('assign_to')->nullable()->after('name');
            $table->text('stage_id')->nullable()->after('name');
            $table->integer('archive')->default(0)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('assign_to');
            $table->dropColumn('stage_id');
            $table->dropColumn('archive');
        });
    }
};
