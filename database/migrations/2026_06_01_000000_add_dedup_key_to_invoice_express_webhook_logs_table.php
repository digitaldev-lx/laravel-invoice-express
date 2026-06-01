<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = $this->tableName();

        if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'dedup_key')) {
            return;
        }

        Schema::table($tableName, static function (Blueprint $table): void {
            // SHA-256 hex digest of the raw webhook body. The unique index is
            // what makes idempotency race-safe: a concurrent duplicate insert
            // violates the constraint and Eloquent's firstOrCreate re-reads it.
            $table->string('dedup_key', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'dedup_key')) {
            Schema::table($tableName, static function (Blueprint $table): void {
                // Drop the unique index before the column so SQLite does not
                // choke on an index that references a now-missing column.
                $table->dropUnique(['dedup_key']);
                $table->dropColumn('dedup_key');
            });
        }
    }

    private function tableName(): string
    {
        return (string) config(
            'invoiceexpress.persistence.tables.webhook_logs',
            'invoice_express_webhook_logs',
        );
    }
};
