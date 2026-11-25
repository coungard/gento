<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->supportsFullText()) {
            return;
        }

        Schema::getConnection()->statement(
            'ALTER TABLE parts ADD FULLTEXT parts_fulltext_search (name, sku, description)'
        );
    }

    public function down(): void
    {
        if (! $this->supportsFullText()) {
            return;
        }

        Schema::getConnection()->statement('ALTER TABLE parts DROP INDEX parts_fulltext_search');
    }

    private function supportsFullText(): bool
    {
        return in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
