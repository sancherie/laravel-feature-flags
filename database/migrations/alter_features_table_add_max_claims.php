<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_has_feature', function (Blueprint $table) {
            $table->timestamp('claimed_at')->after('enabled')->nullable();
        });

        Schema::table('features', function (Blueprint $table) {
            $table->unsignedInteger('max_claims')->after('enabled')->nullable();
        });
    }

    public function down()
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('max_claims');
        });

        Schema::table('model_has_feature', function (Blueprint $table) {
            $table->dropColumn('claimed_at');
        });
    }
};
