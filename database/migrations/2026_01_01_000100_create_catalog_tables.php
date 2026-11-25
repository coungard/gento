<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'sort']);
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('country', 64)->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('generator_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->decimal('power_kva', 8, 2)->nullable();
            $table->string('engine', 128)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['brand_id', 'slug']);
        });

        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('external_id')->nullable()->index();
            $table->string('source', 16)->default('manual');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand', 128)->nullable();
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('old_price', 12, 2)->nullable();
            $table->char('currency', 3)->default('RUB');
            $table->string('unit', 16)->default('шт');
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 512)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['category_id', 'is_active']);
            $table->index(['is_active', 'is_featured']);
            $table->index('price');
        });

        Schema::create('part_generator_model', function (Blueprint $table) {
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generator_model_id')->constrained()->cascadeOnDelete();
            $table->string('note')->nullable();

            $table->primary(['part_id', 'generator_model_id']);
        });

        Schema::create('part_cross_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->string('brand', 128)->nullable();
            $table->string('kind', 16)->default('analog');
            $table->timestamps();

            $table->index('number');
        });

        Schema::create('part_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->decimal('old_price', 12, 2)->nullable();
            $table->string('source', 16)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['part_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_price_histories');
        Schema::dropIfExists('part_cross_numbers');
        Schema::dropIfExists('part_generator_model');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('generator_models');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
