<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/', WebhookController::class)->name('handle');
