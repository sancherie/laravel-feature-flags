<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('model_has_feature', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable();
            $table->timestamp('claimed_at')->after('enabled')->nullable();
        });

        foreach (DB::table('model_has_feature')->whereNull('uuid')->cursor() as $item) {
            DB::table('model_has_feature')
                ->where('id', $item->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }

        Schema::table('model_has_feature', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
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
            $table->dropIndex(['uuid']);
            $table->dropColumn('uuid');
            $table->dropColumn('claimed_at');
        });
    }
};
