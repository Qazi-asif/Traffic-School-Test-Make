<?php

use App\Http\Controllers\DicdsOrderAmendmentController;
use App\Http\Controllers\DicdsReceiptController;
use App\Http\Controllers\DicdsUserManagementController;
use App\Http\Controllers\FloridaApprovalController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // Order Amendment Routes
    Route::put('/dicds-orders/{id}/amend', [DicdsOrderAmendmentController::class, 'amend']);
    Route::get('/dicds-orders/{id}/amendment-history', [DicdsOrderAmendmentController::class, 'history']);

    // Receipt Routes
    Route::post('/dicds-orders/{id}/generate-receipt', [DicdsReceiptController::class, 'generate']);
    Route::get('/dicds-orders/{id}/receipt', [DicdsReceiptController::class, 'show']);
    Route::post('/dicds-orders/{id}/mark-printed', [DicdsReceiptController::class, 'markPrinted']);

    // Florida Approval Routes
    Route::put('/dicds-orders/{id}/update-approval', [FloridaApprovalController::class, 'updateApproval']);
    Route::get('/dicds-orders/pending-approval', [FloridaApprovalController::class, 'pendingApproval']);

    // User Management Routes
    Route::get('/dicds/user-management/users', [DicdsUserManagementController::class, 'getUsers']);
    Route::get('/dicds/user-management/roles', [DicdsUserManagementController::class, 'getRoles']);
    Route::put('/dicds/user-management/users/{id}/status', [DicdsUserManagementController::class, 'updateStatus']);
    Route::put('/dicds/user-management/users/{id}/role', [DicdsUserManagementController::class, 'updateRole']);
    Route::post('/dicds/user-management/users/{id}/reset-password', [DicdsUserManagementController::class, 'resetPassword']);
});
