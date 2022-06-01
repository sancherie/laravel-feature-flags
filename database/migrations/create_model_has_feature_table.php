<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('model_has_feature', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('feature_id')->constrained('features');
            $table->uuidMorphs('featurable');

            $table->unique([
                'feature_id',
                'featurable_type',
                'featurable_id',
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_feature');
    }
};
