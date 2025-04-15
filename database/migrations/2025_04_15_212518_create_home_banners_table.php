<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
        public function up()
        {
            Schema::create('home_banners', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('image_url', 512);
                $table->string('button_text', 100)->nullable();
                $table->string('button_link', 512)->nullable();
                $table->integer('display_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    
        public function down()
        {
            Schema::dropIfExists('home_banners');
        } 
    
};
