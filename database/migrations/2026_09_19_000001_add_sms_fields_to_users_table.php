<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin','teacher','student'])->default('student')->after('password');
            $table->boolean('status')->default(true)->after('role');
            $table->string('phone', 30)->nullable()->after('status');
            $table->string('profile_photo')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','status','phone','profile_photo']);
        });
    }
};
