<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Middleware;
use App\Models\Settings;

class AdminMaintenanceController extends Controller
{
    public function index(): void
    {
        Middleware::requireRole('admin');

        $this->view('admin/maintenance/index', [
            'title'     => 'Maintenance — Administration Le Commerce',
            'pageTitle' => 'Mode maintenance',
            'currentIp' => $_SERVER['REMOTE_ADDR'] ?? '',
        ], 'admin');
    }

    public function update(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        Settings::updateMany([
            'maintenance_enabled'       => $this->input('maintenance_enabled') ? '1' : '0',
            'maintenance_message'       => trim((string) $this->input('maintenance_message', '')),
            'maintenance_whitelist_ips' => trim((string) $this->input('maintenance_whitelist_ips', '')),
        ]);

        $this->setFlash('success', 'Paramètres de maintenance mis à jour.');
        $this->redirect('/admin/maintenance');
    }
}
