<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('sections', function(Blueprint $table){ $table->engine='InnoDB'; $table->id(); $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); $table->string('name',50); $table->string('code',20); $table->timestamps(); $table->unique(['course_id','code']); }); } public function down(): void { Schema::dropIfExists('sections'); } };
