<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestSupportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_support', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('randomDigit')->nullable();
            $table->unsignedInteger('randomNumber')->nullable();
            $table->unsignedFloat('randomFloat')->nullable();
            $table->unsignedInteger('numberBetween')->nullable();
            $table->json('randomElements')->nullable();
            $table->string('randomElement')->nullable();
            $table->json('shuffle')->nullable();
            $table->string('ignoreNull')->nullable();
            $table->string('exactValue')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_support');
    }
}
