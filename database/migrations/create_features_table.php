<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('features', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 64);
            $table->boolean('enabled')->nullable();
            $table->boolean('claimable')->default(false);
            $table->unsignedInteger('max_claims')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('features');
    }
};
