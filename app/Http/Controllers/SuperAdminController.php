<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class SuperAdminController extends Controller
{
    /** @param array<string,string> $params */
    public function dashboard(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/dashboard', [
            'user' => Auth::user(),
        ]);
    }
}
