<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('semesters', function(Blueprint $table){ $table->engine='InnoDB'; $table->id(); $table->foreignId('course_id')->constrained()->cascadeOnDelete(); $table->string('name',50); $table->unsignedTinyInteger('number'); $table->timestamps(); $table->unique(['course_id','number']); }); } public function down(): void { Schema::dropIfExists('semesters'); } };
