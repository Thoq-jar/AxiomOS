<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('apps', function(Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('version');
            $table->string('category');
            $table->string('icon')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('installed')->default(false);
            $table->string('docker_image')->nullable();
            $table->json('ports')->nullable();
            $table->json('environment_vars')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('apps');
    }
};
