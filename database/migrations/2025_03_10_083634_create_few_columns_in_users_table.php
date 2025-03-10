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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_picture')->after('email')->nullable();
            $table->date('date_of_birth')->after('profile_picture')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->after('date_of_birth');
            $table->text('bio')->after('gender')->nullable();
            $table->string('location')->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_picture', 'date_of_birth', 'gender', 'bio', 'location']);
        });
    }
};
