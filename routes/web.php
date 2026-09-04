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

use App\Http\Controllers\AttachmentController;

// Root redirect
Router::get('/', function() {
    header('Location: ' . Router::url('/dashboard'));
    exit;
});

// Authentication Routes
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::post('/logout', [AuthController::class, 'logout']);
Router::post('/auth/switch', [AuthController::class, 'quickSwitch']);

// Dashboard Routes
Router::get('/dashboard', [DashboardController::class, 'index']);
Router::get('/dashboard/stats-json', [DashboardController::class, 'statsJson']);

// Project Routes
Router::get('/projects', [ProjectController::class, 'index']);
Router::post('/projects', [ProjectController::class, 'store']);
Router::get('/projects/{id}', [ProjectController::class, 'show']);
Router::post('/projects/{id}', [ProjectController::class, 'update']);
Router::post('/projects/{id}/delete', [ProjectController::class, 'delete']);

// Sub Project Routes
Router::post('/sub-projects', [SubProjectController::class, 'store']);
Router::get('/sub-projects/{id}', [SubProjectController::class, 'show']);
Router::post('/sub-projects/{id}/update', [SubProjectController::class, 'update']);
Router::post('/sub-projects/{id}/delete', [SubProjectController::class, 'delete']);
Router::post('/sub-projects/{id}/increment', [SubProjectController::class, 'incrementProgress']);
Router::post('/sub-projects/{id}/status', [SubProjectController::class, 'updateStatusAndProgress']);
Router::post('/sub-projects/{id}/manual-progress', [SubProjectController::class, 'updateManualProgress']);
Router::post('/sub-projects/{id}/report-problem', [SubProjectController::class, 'reportProblem']);
Router::post('/sub-projects/{id}/resolve-problem', [SubProjectController::class, 'resolveProblem']);

// Activities Routes
Router::post('/activities', [ActivityController::class, 'store']);
Router::post('/activities/{id}/update', [ActivityController::class, 'update']);
Router::post('/activities/{id}/status', [ActivityController::class, 'updateStatus']);
Router::post('/activities/{id}/delete', [ActivityController::class, 'delete']);

// Budgets Routes
Router::get('/budgets', [BudgetController::class, 'index']);
Router::post('/budgets/disburse', [BudgetController::class, 'disburse']);
Router::post('/budgets/disbursements/{id}/delete', [BudgetController::class, 'deleteDisbursement']);

// Attachments Routes
Router::post('/attachments/upload', [AttachmentController::class, 'upload']);
Router::post('/attachments/{id}/delete', [AttachmentController::class, 'delete']);

// Reports Routes
Router::get('/reports', [ReportController::class, 'index']);
Router::get('/reports/print', [ReportController::class, 'printReport']);
Router::get('/reports/export-csv', [ReportController::class, 'exportCsv']);

// Audit Log Routes
Router::get('/audit-logs', [AuditLogController::class, 'index']);

// User Management Routes
Router::get('/users', [UserController::class, 'index']);
Router::post('/users', [UserController::class, 'store']);
Router::post('/users/{id}/update', [UserController::class, 'update']);
Router::post('/users/{id}/delete', [UserController::class, 'delete']);
