<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Registration completion routes (public but signed)
Route::post('/registration/validate', [RegistrationController::class, 'validateInvitation']);
Route::post('/registration/complete', [RegistrationController::class, 'complete'])
    ->name('registration.complete');

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // User management
    Route::apiResource('users', UserController::class);
    Route::post('/users/invite', [UserController::class, 'invite']);
    Route::post('/users/{user}/resend-invitation', [UserController::class, 'resendInvitation']);
    Route::get('/users/stats/invitations', [UserController::class, 'getInvitationStats']);
    
    // Role management
    Route::apiResource('roles', RoleController::class);
    Route::get('/permissions', [RoleController::class, 'permissions']);
    
    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendInvoice']);
    
    // Clients
    Route::apiResource('clients', ClientController::class);
    
    // Reports
    Route::get('reports/sales', [ReportController::class, 'salesReport']);
    Route::get('reports/clients', [ReportController::class, 'clientReport']);
    Route::get('reports/dashboard-stats', [ReportController::class, 'dashboardStats']);

    //client

    Route::get('/clients/stats', [ClientController::class, 'stats']);
    Route::get('/clients/search', [ClientController::class, 'search']);
    Route::get('/clients/export', [ClientController::class, 'export']);
    Route::post('/clients/import', [ClientController::class, 'import']);
    Route::post('/clients/bulk-delete', [ClientController::class, 'bulkDestroy']);
    Route::get('/clients/{client}/invoices', [ClientController::class, 'invoices']);
    
    Route::apiResource('clients', ClientController::class);

    // Invoice Item routes

    Route::get('/invoices/{invoice}/items', [InvoiceItemController::class, 'index']);
    Route::post('/invoices/{invoice}/items', [InvoiceItemController::class, 'store']);
    Route::get('/invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'show']);
    Route::put('/invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'update']);
    Route::delete('/invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'destroy']);
    Route::post('/invoices/{invoice}/items/bulk-update', [InvoiceItemController::class, 'bulkUpdate']);
    Route::post('/invoices/{invoice}/items/{item}/duplicate', [InvoiceItemController::class, 'duplicate']);
    Route::post('/invoices/{invoice}/items/reorder', [InvoiceItemController::class, 'reorder']);

});