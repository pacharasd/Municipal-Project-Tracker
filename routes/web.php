<?php

use App\Core\Router;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubProjectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ProfileController;

// Root redirect
Router::get('/', function() {
    if (\App\Core\Auth::check()) {
        header('Location: ' . Router::url('/dashboard'));
    } else {
        header('Location: ' . Router::url('/login'));
    }
    exit;
});

// Authentication Routes (Public Guest Routes)
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::post('/logout', [AuthController::class, 'logout']);

// User Profile Routes (Authenticated)
Router::post('/profile/update', [ProfileController::class, 'updateProfile'], ['auth']);
Router::post('/profile/password', [ProfileController::class, 'updatePassword'], ['auth']);

// Dashboard Routes (Authenticated)
Router::get('/dashboard', [DashboardController::class, 'index'], ['auth']);
Router::get('/dashboard/stats-json', [DashboardController::class, 'statsJson'], ['auth']);

// Project Routes (Authenticated)
Router::get('/projects', [ProjectController::class, 'index'], ['auth']);
Router::post('/projects', [ProjectController::class, 'store'], ['auth']);
Router::get('/projects/{id}', [ProjectController::class, 'show'], ['auth']);
Router::post('/projects/{id}', [ProjectController::class, 'update'], ['auth']);
Router::post('/projects/{id}/evaluate', [ProjectController::class, 'evaluate'], ['auth']);
Router::post('/projects/{id}/delete', [ProjectController::class, 'delete'], ['auth']);

// Sub Project Routes (Authenticated)
Router::post('/sub-projects', [SubProjectController::class, 'store'], ['auth']);
Router::get('/sub-projects/{id}', [SubProjectController::class, 'show'], ['auth']);
Router::post('/sub-projects/{id}/update', [SubProjectController::class, 'update'], ['auth']);
Router::post('/sub-projects/{id}/delete', [SubProjectController::class, 'delete'], ['auth']);
Router::post('/sub-projects/{id}/increment', [SubProjectController::class, 'incrementProgress'], ['auth']);
Router::get('/sub-projects/{id}/increment', function(string $id) {
    header('Location: ' . Router::url("/sub-projects/{$id}"));
    exit;
}, ['auth']);
Router::post('/sub-projects/{id}/status', [SubProjectController::class, 'updateStatusAndProgress'], ['auth']);
Router::post('/sub-projects/{id}/manual-progress', [SubProjectController::class, 'updateManualProgress'], ['auth']);
Router::post('/sub-projects/{id}/report-problem', [SubProjectController::class, 'reportProblem'], ['auth']);
Router::post('/sub-projects/{id}/resolve-problem', [SubProjectController::class, 'resolveProblem'], ['auth']);

// Activities Routes (Authenticated)
Router::post('/activities', [ActivityController::class, 'store'], ['auth']);
Router::post('/activities/{id}/update', [ActivityController::class, 'update'], ['auth']);
Router::post('/activities/{id}/status', [ActivityController::class, 'updateStatus'], ['auth']);
Router::post('/activities/{id}/delete', [ActivityController::class, 'delete'], ['auth']);

// Budgets Routes (Authenticated)
Router::get('/budgets', [BudgetController::class, 'index'], ['auth']);
Router::post('/budgets/disburse', [BudgetController::class, 'disburse'], ['auth']);
Router::post('/budgets/disbursements/{id}/delete', [BudgetController::class, 'deleteDisbursement'], ['auth']);

// Attachments Routes (Authenticated)
Router::post('/attachments/upload', [AttachmentController::class, 'upload'], ['auth']);
Router::post('/attachments/{id}/delete', [AttachmentController::class, 'delete'], ['auth']);

// Reports Routes (Authenticated)
Router::get('/reports', [ReportController::class, 'index'], ['auth']);
Router::get('/reports/print', [ReportController::class, 'printReport'], ['auth']);
Router::get('/reports/export-csv', [ReportController::class, 'exportCsv'], ['auth']);

// Audit Log Routes (Administrator Only)
Router::get('/audit-logs', [AuditLogController::class, 'index'], ['auth', 'admin']);

// User Management Routes (Administrator Only)
Router::get('/users', [UserController::class, 'index'], ['auth', 'admin']);
Router::post('/users', [UserController::class, 'store'], ['auth', 'admin']);
Router::post('/users/{id}/update', [UserController::class, 'update'], ['auth', 'admin']);
Router::post('/users/{id}/delete', [UserController::class, 'delete'], ['auth', 'admin']);

// Project Categories Management Routes (Administrator Only)
Router::get('/categories', [CategoryController::class, 'index'], ['auth', 'admin']);
Router::post('/categories', [CategoryController::class, 'store'], ['auth', 'admin']);
Router::post('/categories/seed-defaults', [CategoryController::class, 'seedDefaults'], ['auth', 'admin']);
Router::post('/categories/{id}/update', [CategoryController::class, 'update'], ['auth', 'admin']);
Router::post('/categories/{id}/delete', [CategoryController::class, 'delete'], ['auth', 'admin']);

// Fiscal Years Route (Redirect to Dashboard)
Router::get('/fiscal-years', function() {
    header('Location: ' . \App\Core\Router::url('/dashboard'));
    exit;
}, ['auth']);


