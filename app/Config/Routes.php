<?php

namespace Config;

/**
 * Routes Configuration untuk Aplikasi IKM v2.0.0
 */

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =============================================================================
// PUBLIC ROUTES (Tidak memerlukan autentikasi)
// =============================================================================

// Home page - Landing Page Publik
$routes->get('/', 'PublicController\LandingController::index');

// Language switcher
$routes->get('language/(:alpha)', 'PublicController\LandingController::setLanguage/$1');

// Public Dashboard Transparansi (F-20, UC-20) - Read-only, SEO optimized
$routes->get('dashboard', 'PublicController\PublicDashboardController::index');
$routes->get('public/dashboard/data', 'PublicController\PublicDashboardController::getData');

// Old dashboard route (keep for backward compatibility or remove)
// $routes->get('dashboard', 'PublicController\LandingController::dashboard');

// Public survey pages
$routes->group('survei', ['namespace' => 'App\Controllers\PublicController'], function ($routes) {
    $routes->get('/', 'SurveiController::index');
    $routes->get('index/(:num)/(:num)', 'SurveiController::index/$1/$2');
    $routes->post('submit', 'SurveiController::submit');
    $routes->get('thank-you/(:num)', 'SurveiController::thankYou/$1');
});

// Health check & Metrics
$routes->get('health', 'Health::index');
$routes->get('metrics', 'Metrics::index');

// Privacy & Legal
$routes->get('privacy-notice', 'Legal::privacy');
$routes->get('terms-of-service', 'Legal::terms');

// =============================================================================
// AUTHENTICATION ROUTES
// =============================================================================

$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    // Login/Logout
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::authenticate');
    $routes->get('logout', 'AuthController::logout');
    
    // OAuth2
    $routes->get('oauth/(:segment)', 'OAuthController::redirect/$1');
    $routes->get('oauth/callback/(:segment)', 'OAuthController::callback/$1');
    
    // MFA
    $routes->get('mfa/setup', 'MFAController::setup');
    $routes->post('mfa/setup', 'MFAController::enable');
    $routes->get('mfa/verify', 'MFAController::verify');
    $routes->post('mfa/verify', 'MFAController::validate');
    
    // Password Reset
    $routes->get('forgot-password', 'PasswordController::forgot');
    $routes->post('forgot-password', 'PasswordController::sendReset');
    $routes->get('reset-password/(:alphanum)', 'PasswordController::reset/$1');
    $routes->post('reset-password', 'PasswordController::update');
});

// =============================================================================
// ADMIN ROUTES (Memerlukan autentikasi & authorization)
// =============================================================================

$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'auth'], function ($routes) {
    
    // Dashboard Internal IKM (F-07, F-17)
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');
    
    // Kuesioner Management (Modul Manajemen Kuesioner - F-04, UC-05)
    $routes->get('kuesioner', 'KuesionerController::index');
    $routes->get('kuesioner/data', 'KuesionerController::data');
    $routes->get('kuesioner/(:num)', 'KuesionerController::show/$1');
    $routes->get('kuesioner/(:num)/edit', 'KuesionerController::edit/$1');
    $routes->post('kuesioner/update/(:num)', 'KuesionerController::update/$1');
    $routes->post('kuesioner/(:num)/toggle-status', 'KuesionerController::toggleStatus/$1');
    $routes->delete('kuesioner/(:num)', 'KuesionerController::destroy/$1');
    $routes->get('kuesioner/preview', 'KuesionerController::preview');
    $routes->post('kuesioner/reorder', 'KuesionerController::reorder');

    // User Management
    $routes->resource('users', ['controller' => 'UserController']);
    $routes->post('users/(:num)/toggle-status', 'UserController::toggleStatus/$1');
    $routes->post('users/(:num)/reset-password', 'UserController::resetPassword/$1');
    
    // Role & Permission Management
    $routes->resource('roles', ['controller' => 'RoleController']);
    $routes->resource('permissions', ['controller' => 'PermissionController']);
    $routes->post('roles/(:num)/permissions', 'RoleController::assignPermissions/$1');
    
    // Survey Management
    $routes->resource('surveys', ['controller' => 'SurveyController']);
    $routes->post('surveys/(:num)/publish', 'SurveyController::publish/$1');
    $routes->post('surveys/(:num)/unpublish', 'SurveyController::unpublish/$1');
    $routes->post('surveys/(:num)/duplicate', 'SurveyController::duplicate/$1');
    $routes->get('surveys/(:num)/preview', 'SurveyController::preview/$1');
    
    // Survey Elements
    $routes->resource('survey-elements', ['controller' => 'SurveyElementController']);
    $routes->resource('survey-questions', ['controller' => 'SurveyQuestionController']);
    $routes->resource('survey-options', ['controller' => 'SurveyOptionController']);
    
    // Respondents & Responses
    $routes->resource('respondents', ['controller' => 'RespondentController']);
    $routes->resource('responses', ['controller' => 'ResponseController']);
    $routes->get('responses/survey/(:num)', 'ResponseController::bySurvey/$1');
    $routes->get('responses/export/(:num)', 'ResponseController::export/$1');
    
    // Analytics & Reports
    $routes->get('analytics/dashboard', 'AnalyticsController::dashboard');
    $routes->get('analytics/survey/(:num)', 'AnalyticsController::survey/$1');
    $routes->get('analytics/ikm-score', 'AnalyticsController::ikmScore');
    $routes->get('analytics/export', 'AnalyticsController::export');
    
    // Unit Kerja
    $routes->resource('unit-kerja', ['controller' => 'UnitKerjaController']);
    
    // Settings
    $routes->get('settings', 'SettingsController::index');
    $routes->post('settings', 'SettingsController::update');
    
    // PDP & Consent Management
    $routes->get('consents', 'ConsentController::index');
    $routes->get('consents/(:num)', 'ConsentController::detail/$1');
    $routes->post('consents/export', 'ConsentController::export');
    $routes->delete('consents/expired', 'ConsentController::cleanupExpired');
    
    // Audit Logs
    $routes->get('audit-logs', 'AuditLogController::index');
    $routes->get('audit-logs/(:num)', 'AuditLogController::detail/$1');
    $routes->get('audit-logs/user/(:num)', 'AuditLogController::byUser/$1');
    $routes->get('audit-logs/export', 'AuditLogController::export');
    
    // Backup & Restore
    $routes->get('backup', 'BackupController::index');
    $routes->post('backup/create', 'BackupController::create');
    $routes->post('backup/restore', 'BackupController::restore');
    $routes->delete('backup/(:num)', 'BackupController::delete/$1');
    
    // Laporan Module (F-08, F-09)
    $routes->get('laporan', 'LaporanController::index');
    $routes->get('laporan/preview-pdf', 'LaporanController::previewPdf');
    $routes->post('laporan/generate-pdf', 'LaporanController::generatePdf');
    $routes->post('laporan/generate-excel', 'LaporanController::generateExcel');
    $routes->get('laporan/download-pdf/(:num)', 'LaporanController::downloadPdf/$1');
    $routes->get('laporan/download-excel/(:num)', 'LaporanController::downloadExcel/$1');
    $routes->get('laporan/direct-pdf', 'LaporanController::directPdf');
    $routes->get('laporan/direct-excel', 'LaporanController::directExcel');
    $routes->get('laporan/job-status/(:num)', 'LaporanController::jobStatus/$1');
    $routes->get('laporan/history', 'LaporanController::history');
});

// =============================================================================
// API ROUTES (RESTful API untuk Microservices)
// =============================================================================

$routes->group('api/v1', ['namespace' => 'App\Controllers\Api', 'filter' => 'api-auth'], function ($routes) {
    
    // Public API endpoints
    $routes->get('surveys', 'SurveyAPI::index');
    $routes->get('surveys/(:segment)', 'SurveyAPI::show/$1');
    $routes->post('surveys/(:segment)/respond', 'SurveyAPI::submit/$1');
    
    // Protected API endpoints
    $routes->resource('users', ['controller' => 'UserAPI', 'only' => ['index', 'show', 'update']]);
    $routes->resource('responses', ['controller' => 'ResponseAPI', 'only' => ['index', 'show', 'create']]);
    $routes->get('analytics/summary', 'AnalyticsAPI::summary');
    $routes->get('analytics/ikm', 'AnalyticsAPI::ikm');
    
    // Webhooks
    $routes->post('webhooks/(:segment)', 'WebhookController::handle/$1');
});

// Dashboard API (Internal Admin)
$routes->group('api/dashboard', ['namespace' => 'App\Controllers\Api', 'filter' => 'auth'], function ($routes) {
    $routes->get('data', 'DashboardApiController::getData');
});

// =============================================================================
// ERROR PAGES
// =============================================================================

$routes->set404Override('Errors::notFound');
$routes->set500Override('Errors::serverError');

// =============================================================================
// QUEUE MONITORING ROUTES (Admin Only)
// =============================================================================

$routes->group('admin/queue', ['namespace' => 'App\\Controllers\\Admin', 'filter' => 'auth'], function ($routes) {
    $routes->get('/', 'QueueController::index');
    $routes->get('stats', 'QueueController::stats');
    $routes->post('pause/(:segment)', 'QueueController::pause/$1');
    $routes->post('resume/(:segment)', 'QueueController::resume/$1');
    $routes->post('retry/(:segment)', 'QueueController::retry/$1');
    $routes->post('retry/all', 'QueueController::retryAll');
    $routes->get('failed-jobs', 'QueueController::failedJobs');
    $routes->get('job-detail/(:segment)', 'QueueController::jobDetail/$1');
    $routes->delete('clear/(:segment)', 'QueueController::clear/$1');
});
