<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('student_documents', function(Blueprint $table){ $table->engine='InnoDB'; $table->id(); $table->foreignId('student_id')->constrained()->cascadeOnDelete(); $table->string('type',50); $table->string('title')->nullable(); $table->string('file_path'); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('student_documents'); } };
