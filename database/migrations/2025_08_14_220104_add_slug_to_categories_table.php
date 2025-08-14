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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->after('name')->unique()->nullable();
        });
        
        // Generate slugs for existing categories
        \App\Models\Category::all()->each(function($category) {
            $category->update([
                'slug' => \Illuminate\Support\Str::slug($category->name)
            ]);
        });
        
        // Make the slug column not nullable after populating
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
