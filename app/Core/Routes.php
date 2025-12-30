<?php

declare(strict_types=1);

namespace App\Core;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\TenantBusinessHoursController;
use App\Http\Controllers\TenantEmployeeHoursController;
use App\Http\Controllers\TenantHolidaysController;
use App\Http\Controllers\TenantTimeBlocksController;
use App\Http\Controllers\TenantFinanceController;
use App\Http\Controllers\TenantFinanceTitlesController;
use App\Http\Controllers\TenantAuditController;
use App\Http\Controllers\TenantReportsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SuperAdminAuditController;
use App\Http\Controllers\SuperAdminPlansController;
use App\Http\Controllers\SuperAdminSettingsController;
use App\Http\Controllers\SuperAdminSubscriptionsController;
use App\Http\Controllers\SuperAdminTenantsController;
use App\Http\Controllers\SuperAdminAsaasController;
use App\Http\Controllers\WebhooksAsaasController;
use App\Http\Controllers\TenantDashboardController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SuperAdminWebhooksController;
use App\Http\Controllers\TenantNotificationSettingsController;
use App\Http\Controllers\TenantSubscriptionController;
use App\Http\Controllers\TenantCompanySettingsController;
use App\Http\Controllers\TenantCalendarController;
use App\Http\Controllers\TenantPagesController;

final class Routes
{
    public static function register(Router $router): void
    {
        $router->get('/uploads/branding/{file}', static function (Request $request, array $params): Response {
            $file = (string)($params['file'] ?? '');

            if ($file === '' || str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
                return Response::html('404 - Arquivo não encontrado', 404);
            }

            $projectRoot = dirname(__DIR__, 2);
            $paths = [
                $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'branding' . DIRECTORY_SEPARATOR . $file,
                $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'branding' . DIRECTORY_SEPARATOR . $file,
            ];

            $found = null;
            foreach ($paths as $p) {
                if (is_file($p)) {
                    $found = $p;
                    break;
                }
            }

            if ($found === null) {
                return Response::html('404 - Arquivo não encontrado', 404);
            }

            $ext = strtolower(pathinfo($found, PATHINFO_EXTENSION));
            $contentType = match ($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                default => 'application/octet-stream',
            };

            $body = (string)@file_get_contents($found);
            if ($body === '') {
                return Response::html('404 - Arquivo não encontrado', 404);
            }

            return new Response($body, 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        });

        $router->get('/', static fn () => Response::redirect('/login'));

        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->post('/logout', [AuthController::class, 'logout']);

        $router->get('/forgot-password', [PasswordController::class, 'showForgot']);
        $router->post('/forgot-password', [PasswordController::class, 'sendReset']);
        $router->get('/reset-password', [PasswordController::class, 'showReset']);
        $router->post('/reset-password', [PasswordController::class, 'reset']);
        $router->get('/change-password', [PasswordController::class, 'showChange']);
        $router->post('/change-password', [PasswordController::class, 'change']);

        $router->get('/super/dashboard', [SuperAdminController::class, 'dashboard']);
        $router->get('/super/audit', [SuperAdminAuditController::class, 'index']);

        $router->get('/super/webhooks', [SuperAdminWebhooksController::class, 'index']);
        $router->post('/super/webhooks', [SuperAdminWebhooksController::class, 'store']);
        $router->post('/super/webhooks/resend', [SuperAdminWebhooksController::class, 'resend']);
        $router->post('/super/webhooks/process', [SuperAdminWebhooksController::class, 'processQueue']);

        $router->get('/super/tenants', [SuperAdminTenantsController::class, 'index']);
        $router->post('/super/tenants', [SuperAdminTenantsController::class, 'store']);
        $router->get('/super/tenants/{id}/edit', [SuperAdminTenantsController::class, 'edit']);
        $router->post('/super/tenants/{id}', [SuperAdminTenantsController::class, 'update']);

        $router->get('/super/plans', [SuperAdminPlansController::class, 'index']);
        $router->post('/super/plans', [SuperAdminPlansController::class, 'store']);

        $router->get('/super/subscriptions', [SuperAdminSubscriptionsController::class, 'index']);
        $router->post('/super/subscriptions', [SuperAdminSubscriptionsController::class, 'store']);

        $router->get('/super/settings', [SuperAdminSettingsController::class, 'index']);
        $router->post('/super/settings', [SuperAdminSettingsController::class, 'store']);
        $router->post('/super/settings/test-email', [SuperAdminSettingsController::class, 'testEmail']);

        $router->get('/super/asaas', [SuperAdminAsaasController::class, 'index']);
        $router->post('/super/asaas', [SuperAdminAsaasController::class, 'store']);

        $router->post('/webhooks/asaas', [WebhooksAsaasController::class, 'handle']);

        $router->get('/dashboard', [TenantDashboardController::class, 'dashboard']);

        $router->get('/subscription', [TenantSubscriptionController::class, 'index']);
        $router->get('/calendars', [TenantCalendarController::class, 'index']);
        $router->get('/settings/company', [TenantCompanySettingsController::class, 'index']);
        $router->get('/affiliate', [TenantPagesController::class, 'affiliate']);
        $router->get('/domain', [TenantPagesController::class, 'domain']);
        $router->get('/locations', [TenantPagesController::class, 'locations']);

        $router->get('/settings/business-hours', [TenantBusinessHoursController::class, 'index']);
        $router->post('/settings/business-hours', [TenantBusinessHoursController::class, 'store']);

        $router->get('/settings/employee-hours', [TenantEmployeeHoursController::class, 'index']);
        $router->post('/settings/employee-hours', [TenantEmployeeHoursController::class, 'store']);

        $router->get('/settings/holidays', [TenantHolidaysController::class, 'index']);
        $router->post('/settings/holidays', [TenantHolidaysController::class, 'store']);

        $router->get('/settings/time-blocks', [TenantTimeBlocksController::class, 'index']);
        $router->post('/settings/time-blocks', [TenantTimeBlocksController::class, 'store']);

        $router->get('/settings/notifications', [TenantNotificationSettingsController::class, 'index']);
        $router->post('/settings/notifications', [TenantNotificationSettingsController::class, 'store']);

        $router->get('/finance', [TenantFinanceController::class, 'index']);
        $router->post('/finance', [TenantFinanceController::class, 'store']);

        $router->get('/finance/titles', [TenantFinanceTitlesController::class, 'index']);
        $router->post('/finance/titles', [TenantFinanceTitlesController::class, 'store']);
        $router->post('/finance/titles/pay', [TenantFinanceTitlesController::class, 'pay']);
        $router->post('/finance/titles/cancel', [TenantFinanceTitlesController::class, 'cancel']);

        $router->get('/audit', [TenantAuditController::class, 'index']);

        $router->get('/reports', [TenantReportsController::class, 'index']);
        $router->get('/reports/appointments.csv', [TenantReportsController::class, 'appointmentsCsv']);
        $router->get('/reports/finance.csv', [TenantReportsController::class, 'financeCsv']);

        $router->get('/services', [ServicesController::class, 'index']);
        $router->post('/services', [ServicesController::class, 'store']);

        $router->get('/employees', [EmployeesController::class, 'index']);
        $router->post('/employees', [EmployeesController::class, 'store']);

        $router->get('/clients', [ClientsController::class, 'index']);
        $router->post('/clients', [ClientsController::class, 'store']);

        $router->get('/agenda', [AgendaController::class, 'day']);
        $router->post('/agenda', [AgendaController::class, 'store']);

        $router->get('/book', [BookingController::class, 'show']);
        $router->post('/book', [BookingController::class, 'store']);
    }
}
