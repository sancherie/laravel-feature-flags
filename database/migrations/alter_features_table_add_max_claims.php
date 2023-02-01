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
            $table->timestamp('created_at')->nullable()->after('claimed_at');
            $table->timestamp('updated_at')->nullable()->after('created_at');
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
            $table->boolean('claimable')->after('enabled')->default(false);
            $table->unsignedInteger('max_claims')->after('claimable')->nullable();
        });

        Schema::rename('model_has_feature', 'feature_claims');
    }

    public function down()
    {
        Schema::rename('feature_claims', 'model_has_feature');

        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('max_claims');
        });

        Schema::table('model_has_feature', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
            $table->dropColumn('claimed_at');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
};
