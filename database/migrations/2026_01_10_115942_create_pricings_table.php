<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price_per_unit', 10, 2);
            $table->decimal('pack_price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->string('badge')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pricings');
    }
};