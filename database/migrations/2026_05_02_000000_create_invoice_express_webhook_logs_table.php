<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config(
            'invoiceexpress.persistence.tables.webhook_logs',
            'invoice_express_webhook_logs',
        );

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, static function (Blueprint $table): void {
            $table->id();
            $table->string('event')->index();
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->string('document_type')->nullable()->index();
            $table->json('payload');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = (string) config(
            'invoiceexpress.persistence.tables.webhook_logs',
            'invoice_express_webhook_logs',
        );

        Schema::dropIfExists($tableName);
    }
};
