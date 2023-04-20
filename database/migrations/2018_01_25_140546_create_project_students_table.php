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
        Schema::create('project_students', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('project_id');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade')->onDelete('cascade');
            $table->boolean('is_accepted')->default(false);
            $table->integer('choice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_students');
    }
};
