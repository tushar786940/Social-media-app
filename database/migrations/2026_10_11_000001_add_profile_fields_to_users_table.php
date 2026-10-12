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
            // Username for public profile URLs
            $table->string('username')->unique()->after('name');
            
            // Profile information
            $table->text('bio')->nullable()->after('username');
            $table->string('location')->nullable()->after('bio');
            $table->string('website')->nullable()->after('location');
            $table->date('birthdate')->nullable()->after('website');
            
            // Profile photos
            $table->string('profile_photo_path')->nullable()->after('birthdate');
            $table->string('cover_photo_path')->nullable()->after('profile_photo_path');
            
            // Privacy settings
            $table->boolean('is_private')->default(false)->after('cover_photo_path');
            
            // Add indexes for better performance
            $table->index('username');
            $table->index('is_private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'bio',
                'location',
                'website',
                'birthdate',
                'profile_photo_path',
                'cover_photo_path',
                'is_private',
            ]);
        });
    }
};
