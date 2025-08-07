<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('interest_name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('interest_people', function (Blueprint $table) {
            $table->foreignId('people_id')->constrained()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('interest_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->primary(['people_id', 'interest_id']);
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interest_people', function (Blueprint $table) {
            $table->dropForeign(['interest_id']);
            $table->dropForeign(['people_id']);
        });

        Schema::dropIfExists('interest_people');

        Schema::dropIfExists('interests');
    }
};
