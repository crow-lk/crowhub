<?php

use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\EmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function (): void {
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product:slug}', [ProductController::class, 'show']);

    Route::post('inquiries', [InquiryController::class, 'store'])
        ->middleware('throttle:10,1');
});

// Email API Routes
Route::prefix('email')->group(function () {
    // Send a single email - frontend decides all parameters
    Route::post('/send', [EmailController::class, 'send']);

    // Send multiple emails in batch
    Route::post('/send-batch', [EmailController::class, 'sendBatch']);

    // List available templates
    Route::get('/templates', [EmailController::class, 'listTemplates']);

    // Test email configuration
    Route::get('/test', [EmailController::class, 'test']);
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'CrowEmail API',
        'timestamp' => now()->toISOString(),
    ]);
});
