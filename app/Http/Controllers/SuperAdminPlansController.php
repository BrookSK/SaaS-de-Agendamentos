<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Plan;

final class SuperAdminPlansController extends Controller
{
    /** @param array<string,string> $params */
    public function index(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        return $this->view('super/plans/index', [
            'plans' => Plan::all(),
        ]);
    }

    /** @param array<string,string> $params */
    public function store(Request $request, array $params): Response
    {
        if ($resp = Auth::requireRole('super_admin')) {
            return $resp;
        }

        $name = trim((string)$request->input('name', ''));
        $description = trim((string)$request->input('description', ''));
        $priceCents = (int)$request->input('price_cents', 0);
        $billingCycle = (string)$request->input('billing_cycle', 'monthly');
        $active = (int)$request->input('active', 1);

        if (!in_array($billingCycle, ['monthly', 'semiannual', 'annual'], true)) {
            $billingCycle = 'monthly';
        }

        $active = $active === 1 ? 1 : 0;

        if ($name === '' || $priceCents < 0) {
            return Response::redirect('/super/plans');
        }

        Plan::create(
            $name,
            $description !== '' ? $description : null,
            $priceCents,
            $billingCycle,
            $active
        );

        return Response::redirect('/super/plans');
    }
}
