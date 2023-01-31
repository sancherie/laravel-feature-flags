<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feature_claims', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignUuid('feature_id')->constrained('features');
            $table->uuidMorphs('featurable');
            $table->boolean('enabled')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamps();

            $table->unique([
                'feature_id',
                'featurable_type',
                'featurable_id',
            ], 'unique_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('feature_claims');
    }
};
