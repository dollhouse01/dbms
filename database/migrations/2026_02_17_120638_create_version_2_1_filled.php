<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->text('sms_message')->nullable()->after('message');
            $table->integer('enabled_sms')->default(0)->after('enabled_email');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->integer('document_id')->default(0)->after('id');
        });

        Schema::table('reminders', function (Blueprint $table) {
            $table->integer('reminder_id')->default(0)->after('id');
        });


        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('enabled_openai')->default(0)->after('enabled_logged_history');
            $table->integer('enabled_n8n')->default(0)->after('enabled_openai');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('sms_message');
            $table->dropColumn('enabled_sms');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('enabled_openai');
            $table->dropColumn('enabled_n8n');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_id');
        });

        Schema::table('reminders', function (Blueprint $table) {
            $table->dropColumn('reminder_id');
        });

    }
};
